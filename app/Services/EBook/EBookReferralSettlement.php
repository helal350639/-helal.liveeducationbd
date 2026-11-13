<?php

namespace App\Services\EBook;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * E-Book-only referral quote and idempotent bonus settlement.
 * This service deliberately uses the E-Book package, order, entitlement and
 * referral-credit tables; it never reads or writes another module's records.
 */
final class EBookReferralSettlement
{
    public const SESSION_KEY = 'lebd_ebook_referral_attribution_v1';
    public const LEDGER = 'lebd_ebook_referral_credits';

    /** A referral owner must have a currently active, paid purchase of this exact package. */
    public function owner(int $packageId, string $code): ?object
    {
        $code = trim($code);
        if ($packageId < 1 || $code === '' || strlen($code) > 20
            || !preg_match('/^LEBD[0-9]{1,16}$/', $code)) {
            return null;
        }

        $owner = DB::table('students')
            ->where('referral_id', $code)
            ->where('status', 'Active')
            ->select('id', 'name', 'referral_id')
            ->first();

        if (!$owner || !Schema::hasTable('lebd_ebook_entitlements')
            || !Schema::hasTable('lebd_ebook_payment_orders')) {
            return null;
        }

        $now = Carbon::now('Asia/Dhaka')->toDateTimeString();
        $eligible = DB::table('lebd_ebook_entitlements as e')
            ->join('lebd_ebook_payment_orders as o', function ($join) {
                $join->on('o.student_id', '=', 'e.user_id')
                    ->on('o.package_id', '=', 'e.package_id')
                    ->on('o.category_id', '=', 'e.category_id');
            })
            ->where('e.user_id', $owner->id)
            ->where('e.package_id', $packageId)
            ->where('e.status', 'Active')
            ->where('e.expires_at', '>', $now)
            ->whereIn('o.status', ['Complete', 'Completed', 'Paid', 'Success', 'Successful'])
            ->exists();

        return $eligible ? $owner : null;
    }

    /**
     * Calculate all prices in integer paisa. Package price already includes
     * the package's own discount; extra fee is added and referral discount is
     * applied only to a verified, eligible referral owner.
     */
    public function quote(object $package, string $code, ?int $buyerId = null): array
    {
        $now = Carbon::now('Asia/Dhaka');
        $offerActive = empty($package->offer_ends_at)
            || Carbon::parse($package->offer_ends_at, 'Asia/Dhaka')->endOfDay()->greaterThanOrEqualTo($now);
        $basePrice = $offerActive
            ? (float) ($package->price ?? $package->main_price ?? 0)
            : (float) ($package->main_price ?? $package->price ?? 0);

        $base = max(0, (int) round($basePrice * 100));
        $fee = Schema::hasColumn('lebd_ebook_packages', 'extra_fee')
            ? max(0, (int) round((float) ($package->extra_fee ?? 0) * 100)) : 0;

        $referrer = $this->owner((int) $package->id, $code);
        if ($referrer && $buyerId !== null && (int) $referrer->id === $buyerId) {
            $referrer = null;
        }

        $discount = 0;
        $bonus = 0;
        if ($referrer) {
            if (!Schema::hasTable(self::LEDGER)) {
                throw new RuntimeException('E-Book referral-credit table is missing. Existing E-Book referral SQL must be checked before referral checkout.');
            }
            if (Schema::hasColumn('lebd_ebook_packages', 'referral_discount')) {
                $discount = max(0, (int) round((float) ($package->referral_discount ?? 0) * 100));
            }
            if (Schema::hasColumn('lebd_ebook_packages', 'referrer_bonus')) {
                $bonus = max(0, (int) round((float) ($package->referrer_bonus ?? 0) * 100));
            }
            $discount = min($discount, max(0, $base + $fee - 100));
        }

        return [
            'owner' => $referrer,
            'base' => $base,
            'fee' => $fee,
            'discount' => $discount,
            'bonus' => $referrer ? $bonus : 0,
            'total' => $base + $fee - $discount,
        ];
    }

    /** Use only a referral attribution explicitly attached to this package in the session. */
    public function sessionQuote(object $package, int $buyerId, $session): array
    {
        $saved = $session->get(self::SESSION_KEY);
        $code = '';
        if (is_array($saved)
            && (int) ($saved['package_id'] ?? 0) === (int) $package->id
            && !empty($saved['ref'])) {
            $at = (int) ($saved['at'] ?? 0);
            if ($at === 0 || (time() - $at) <= 1800) {
                $code = (string) $saved['ref'];
            }
        }
        return $this->quote($package, $code, $buyerId);
    }

    /** Save referral attribution against the newly created E-Book payment order. */
    public function addAttribution(int $orderId, int $buyerId, int $packageId, array $quote): void
    {
        $owner = $quote['owner'] ?? null;
        if (!$owner) {
            return;
        }
        if (!Schema::hasTable(self::LEDGER)) {
            throw new RuntimeException('E-Book referral-credit table is missing.');
        }

        DB::table(self::LEDGER)->insert([
            'order_id' => $orderId,
            'buyer_id' => $buyerId,
            'package_id' => $packageId,
            'referrer_id' => (int) $owner->id,
            'referral_code' => (string) ($owner->referral_id ?? ''),
            'referral_discount' => number_format(((int) ($quote['discount'] ?? 0)) / 100, 2, '.', ''),
            'referrer_bonus' => number_format(((int) ($quote['bonus'] ?? 0)) / 100, 2, '.', ''),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Credit the owner once, and only after the matching E-Book order is Complete. */
    public function credit(int $orderId): void
    {
        if (!Schema::hasTable(self::LEDGER)) {
            return;
        }

        $credit = DB::table(self::LEDGER)->where('order_id', $orderId)->lockForUpdate()->first();
        if (!$credit || ($credit->status ?? null) !== 'pending') {
            return;
        }

        $order = DB::table('lebd_ebook_payment_orders')->where('id', $orderId)->first();
        if (!$order || $order->status !== 'Complete'
            || (int) $order->student_id !== (int) $credit->buyer_id
            || (int) $order->package_id !== (int) $credit->package_id) {
            throw new RuntimeException('Unverified E-Book referral order.');
        }

        $bonus = (int) round((float) $credit->referrer_bonus * 100);
        if ($bonus > 0) {
            $owner = DB::table('students')->where('id', $credit->referrer_id)->lockForUpdate()->first();
            if (!$owner) {
                throw new RuntimeException('E-Book referral owner no longer exists.');
            }
            $balance = (int) round((float) $owner->referral_balance * 100);
            DB::table('students')->where('id', $owner->id)->update([
                'referral_balance' => number_format(($balance + $bonus) / 100, 2, '.', ''),
            ]);
        }

        DB::table(self::LEDGER)->where('id', $credit->id)->update([
            'status' => 'credited',
            'credited_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
