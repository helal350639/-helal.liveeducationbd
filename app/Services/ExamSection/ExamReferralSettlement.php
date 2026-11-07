<?php
namespace App\Services\ExamSection;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Package-specific, verified referral quote and idempotent settlement. */
final class ExamReferralSettlement
{
    public const SESSION = 'lebd_exam_section_referral_v1';
    public const LEDGER = 'lebd_exam_section_referral_credits';

    public function quote(object $package, int $buyerId, ?string $code): array
    {
        $base = (int)round((float)$package->final_price * 100);
        $referrer = null;
        if ($code !== null && $code !== '') {
            abort_unless(preg_match('/^LEBD[0-9]{1,16}$/', $code), 422, 'Invalid referral code.');
            $referrer = DB::table('students')->where('referral_id', $code)->where('status', 'Active')->first();
            abort_unless($referrer && (int)$referrer->id !== $buyerId, 422, 'Referral link invalid or self-referral.');
            $now = now('Asia/Dhaka')->format('Y-m-d H:i:s');
            $hasAccess = DB::table('lebd_exam_section_student_package_access')
                ->where('student_id',$referrer->id)->where('package_id',$package->id)
                ->where('status','active')->where('starts_at','<=',$now)->where('ends_at','>',$now)->exists();
            abort_unless($hasAccess, 422, 'Referrer must have an active Exam Section package.');
            abort_unless(Schema::hasTable(self::LEDGER), 503, 'Import MANUAL_SQL.sql before referral checkout.');
        }
        $discount = $referrer ? max(0,(int)round((float)($package->referral_discount ?? 0)*100)) : 0;
        $bonus = $referrer ? max(0,(int)round((float)($package->referrer_bonus ?? 0)*100)) : 0;
        $payable = $base - $discount;
        abort_unless($payable >= 100, 422, 'Referral payable must be at least BDT 1.');
        return ['base'=>$base,'discount'=>$discount,'bonus'=>$bonus,'payable'=>$payable,
            'referrer_id'=>$referrer ? (int)$referrer->id : null,'referral_code'=>$referrer ? $code : null];
    }

    public function sessionQuote(object $package, int $buyerId, $session): array
    {
        $saved=$session->get(self::SESSION);
        $code=is_array($saved) && (int)($saved['package_id']??0)===(int)$package->id
            && (int)($saved['buyer_id']??0)===$buyerId && (time()-(int)($saved['at']??0))<=1800
                ? (string)($saved['code']??'') : '';
        return $this->quote($package,$buyerId,$code);
    }

    public function attach(string $reference, int $buyerId, int $packageId, array $quote): void
    {
        if (!$quote['referrer_id']) return;
        DB::table(self::LEDGER)->insert([
            'transaction_id'=>$reference,'student_id'=>$buyerId,'package_id'=>$packageId,
            'referrer_id'=>$quote['referrer_id'],'referral_code'=>$quote['referral_code'],
            'referral_discount'=>number_format($quote['discount']/100,2,'.',''),
            'referrer_bonus'=>number_format($quote['bonus']/100,2,'.',''),
            'credited_at'=>null,'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    /** Call ONLY within the transaction that marks the wallet/SSL order paid. */
    public function credit(string $reference): void
    {
        if (!Schema::hasTable(self::LEDGER)) return;
        $row=DB::table(self::LEDGER)->where('transaction_id',$reference)->lockForUpdate()->first();
        if (!$row || $row->credited_at !== null) return;
        $order=DB::table('lebd_exam_section_package_orders')->where('transaction_id',$reference)->first();
        abort_unless($order && $order->status==='paid'
            && (int)$order->student_id===(int)$row->student_id
            && (int)$order->package_id===(int)$row->package_id
            && (int)$row->student_id!==(int)$row->referrer_id, 409, 'Unverified referral order.');
        $bonus=(int)round((float)$row->referrer_bonus*100);
        if ($bonus>0) {
            $owner=DB::table('students')->where('id',$row->referrer_id)->lockForUpdate()->first();
            if (!$owner) throw new \RuntimeException('Referral owner not found.');
            $new=(int)round((float)$owner->referral_balance*100)+$bonus;
            DB::table('students')->where('id',$owner->id)->update([
                'referral_balance'=>number_format($new/100,2,'.',''),'updated_at'=>now(),
            ]);
        }
        DB::table(self::LEDGER)->where('id',$row->id)->update(['credited_at'=>now(),'updated_at'=>now()]);
    }
}
