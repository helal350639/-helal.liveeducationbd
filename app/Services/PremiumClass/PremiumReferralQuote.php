<?php
namespace App\Services\PremiumClass;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/** Premium-video referral only. Never alters MCQ/E-Book/Batch wallet workflows. */
final class PremiumReferralQuote
{
    public static function quote($package, ?int $buyerId, ?string $code): array
    {
        $base = (int) round((float) ($package->final_price ?? $package->price) * 100);
        $fee = (int) round((float) ($package->extra_fee ?? 0) * 100);
        $refDiscount = max(0, (int) round((float) ($package->referral_discount ?? 0) * 100));
        $bonus = max(0, (int) round((float) ($package->referrer_bonus ?? 0) * 100));
        $ownerId = null;
        $ownerName = null;
        $code = trim((string) $code);
        if ($buyerId && $code !== '' && strlen($code) <= 255 && Schema::hasTable('lebd_premium_class_orders')) {
            $owner = DB::table('students')->where('referral_id', $code)->first(['id', 'name']);
            if ($owner && (int) $owner->id !== $buyerId) {
                // Only buyers with an active PURCHASE of this exact package may share a link.
                $active = DB::table('lebd_premium_class_orders')
                    ->where('student_id', $owner->id)->where('package_id', $package->id)
                    ->where('status', 'paid')->where('expires_at', '>', now())->exists();
                if ($active) {
                    $ownerId = (int) $owner->id;
                    $ownerName = trim((string) ($owner->name ?? '')) ?: 'Premium Student';
                }
            }
        }
        $discount = $ownerId ? min($refDiscount, max(0, $base + $fee - 100)) : 0;
        $payable = $base + $fee - $discount;
        return [
            'referrer_id' => $ownerId, 'referrer_code' => $ownerId ? $code : null,
            'referrer_name' => $ownerId ? $ownerName : null,
            'base_cents' => $base, 'fee_cents' => $fee,
            'discount_cents' => $discount, 'bonus_cents' => $ownerId ? $bonus : 0,
            'payable_cents' => $payable,
        ];
    }

    public static function capture($request, int $packageId): void
    {
        // MCQ-style referral: an ordinary checkout URL must NEVER inherit a
        // referral code from an earlier visit in this browser session.
        // Only an explicit ?ref=CODE on the package review page enables it.
        $code = $request->has('ref') ? trim((string) $request->query('ref', '')) : '';
        if (strlen($code) > 255) $code = '';
        if ($code !== '') $request->session()->put('premium_ref_code_'.$packageId, $code);
        else $request->session()->forget('premium_ref_code_'.$packageId);
        // A newly opened checkout must get its own server-verified preview quote.
        $request->session()->forget('premium_quote_cents_'.$packageId);
    }

    public static function checked($request, $package, int $buyerId): array
    {
        $quote = self::quote($package, $buyerId, $request->session()->get('premium_ref_code_'.$package->id));
        $expected = $request->session()->get('premium_quote_cents_'.$package->id);
        if (!is_numeric($expected) || (int) $expected !== $quote['payable_cents']) {
            throw ValidationException::withMessages(['payment'=>'Payment Preview-এর মূল্য পরিবর্তিত হয়েছে। পুনরায় Preview খুলুন।']);
        }
        if ($quote['payable_cents'] < 100 || $quote['payable_cents'] > 999999999) {
            throw ValidationException::withMessages(['payment'=>'Invalid payable amount.']);
        }
        return $quote;
    }

    public static function record(int $orderId, int $buyerId, int $packageId, array $quote): void
    {
        if (!$quote['referrer_id']) return;
        if (!Schema::hasTable('lebd_premium_referral_credits')) {
            throw new \RuntimeException('Premium referral credits table is missing.');
        }
        DB::table('lebd_premium_referral_credits')->insertOrIgnore([
            'order_id'=>$orderId, 'buyer_id'=>$buyerId, 'package_id'=>$packageId,
            'referrer_id'=>$quote['referrer_id'], 'referral_code'=>$quote['referrer_code'],
            'referral_discount'=>number_format($quote['discount_cents']/100,2,'.',''),
            'referrer_bonus'=>number_format($quote['bonus_cents']/100,2,'.',''),
            'status'=>'pending', 'created_at'=>now(), 'updated_at'=>now(),
        ]);
    }

    /** Invoke only under an order lock after a confirmed paid status. Idempotent by row lock + status. */
    public static function credit(int $orderId): void
    {
        if (!Schema::hasTable('lebd_premium_referral_credits')) return;
        $credit = DB::table('lebd_premium_referral_credits')->where('order_id',$orderId)->lockForUpdate()->first();
        if (!$credit || $credit->status !== 'pending') return;
        if ((float) $credit->referrer_bonus > 0) {
            DB::table('students')->where('id', $credit->referrer_id)->increment('referral_balance', $credit->referrer_bonus);
        }
        DB::table('lebd_premium_referral_credits')->where('id',$credit->id)->update([
            'status'=>'credited', 'credited_at'=>now(), 'updated_at'=>now(),
        ]);
    }
}
