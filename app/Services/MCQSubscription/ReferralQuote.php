<?php
namespace App\Services\MCQSubscription;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Server-authoritative referral quote; no balance changes during a preview. */
final class ReferralQuote
{
    public static function forBuyer(object $package, ?object $buyer, ?string $code): array
    {
        $base = max(0, (int) round(((float) $package->price) * 100));
        $out = ['referrer_id'=>null,'referrer_name'=>null,'referral_code'=>null,
            'discount_cents'=>0,'bonus_cents'=>0,'payable_cents'=>$base];
        if (!$buyer || !$code || !Schema::hasTable('lebd_mcq_referral_credits')) return $out;
        $code = trim($code);
        if ($code === '' || strlen($code) > 20 || !Schema::hasColumn('mcq_subscription_packages','referral_discount')
            || !Schema::hasColumn('mcq_subscription_packages','referrer_bonus')) return $out;
        $referrer = DB::table('students')->where('referral_id',$code)->where('status','Active')
            ->select('id','name','referral_id')->first();
        if (!$referrer || (int)$referrer->id === (int)$buyer->id) return $out;
        // Only a current owner of THIS exact package may refer a new buyer.
        $active = ActivePackageAccess::rows((int)$referrer->id)->contains(function ($row) use ($package) {
            return (int)$row->package_id === (int)$package->id;
        });
        if (!$active) return $out;
        $discount = max(0, (int) round(((float)($package->referral_discount ?? 0))*100));
        $discount = min($base, $discount);
        $bonus = max(0, (int) round(((float)($package->referrer_bonus ?? 0))*100));
        return ['referrer_id'=>(int)$referrer->id,'referrer_name'=>(string)$referrer->name,
            'referral_code'=>(string)$referrer->referral_id,'discount_cents'=>$discount,
            'bonus_cents'=>$bonus,'payable_cents'=>$base-$discount];
    }

    public static function record(int $orderId, int $buyerId, int $packageId, array $quote): void
    {
        if (!$quote['referrer_id']) return;
        DB::table('lebd_mcq_referral_credits')->insert([
            'order_id'=>$orderId,'buyer_id'=>$buyerId,'package_id'=>$packageId,
            'referrer_id'=>$quote['referrer_id'],'referral_code'=>$quote['referral_code'],
            'referral_discount'=>$quote['discount_cents']/100,'referrer_bonus'=>$quote['bonus_cents']/100,
            'status'=>'pending','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    /** Must run under the payment-order transaction and only after order is Complete. */
    public static function credit(int $orderId): void
    {
        if (!Schema::hasTable('lebd_mcq_referral_credits')) return;
        $credit = DB::table('lebd_mcq_referral_credits')->where('order_id',$orderId)->lockForUpdate()->first();
        if (!$credit || $credit->status !== 'pending') return;
        $amount = (int) round(((float)$credit->referrer_bonus)*100);
        if ($amount > 0) {
            $owner = DB::table('students')->where('id',$credit->referrer_id)->lockForUpdate()->first();
            if (!$owner) throw new \RuntimeException('Referrer no longer exists.');
            $balance = (int) round(((float)$owner->referral_balance)*100);
            DB::table('students')->where('id',$credit->referrer_id)->update([
                'referral_balance'=>number_format(($balance+$amount)/100,2,'.','')
            ]);
        }
        DB::table('lebd_mcq_referral_credits')->where('id',$credit->id)->update([
            'status'=>'credited','credited_at'=>now(),'updated_at'=>now()
        ]);
    }
}
