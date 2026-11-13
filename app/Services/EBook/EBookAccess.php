<?php

namespace App\Services\EBook;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Single source of truth for E-Book access checks.
 * Paid entitlements and administrator manual grants are both respected.
 */
class EBookAccess
{
    private function now(): string
    {
        return now('Asia/Dhaka')->toDateTimeString();
    }

    private function completedOrderExpiry(object $order): ?Carbon
    {
        $base = $order->paid_at ?? $order->created_at ?? null;
        if (!$base) {
            return null;
        }
        try {
            $end = Carbon::parse($base, 'Asia/Dhaka');
            $value = (int) ($order->duration_value ?? 0);
            if ($value < 1) return null;
            switch ((string) ($order->duration_unit ?? '')) {
                case 'days': $end->addDays($value); break;
                case 'months': $end->addMonthsNoOverflow($value); break;
                case 'years': $end->addYearsNoOverflow($value); break;
                default: return null;
            }
            return $end;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function hasActivePaidOrder(int $studentId, int $packageId, ?int $categoryId = null): bool
    {
        if (!Schema::hasTable('lebd_ebook_payment_orders')) return false;
        $query = DB::table('lebd_ebook_payment_orders')
            ->where('student_id', $studentId)
            ->where('package_id', $packageId)
            ->whereIn('status', ['Complete', 'Completed', 'Paid', 'Success', 'Successful']);
        if ($categoryId !== null) $query->where('category_id', $categoryId);
        foreach ($query->get() as $order) {
            $expiry = $this->completedOrderExpiry($order);
            if ($expiry && $expiry->gt(Carbon::now('Asia/Dhaka'))) return true;
        }
        return false;
    }

    public function isPackageRevoked(int $studentId, int $packageId): bool
    {
        return $studentId > 0 && $packageId > 0
            && Schema::hasTable('lebd_ebook_access_revocations')
            && DB::table('lebd_ebook_access_revocations')
                ->where('student_id', $studentId)
                ->where('package_id', $packageId)
                ->exists();
    }

    public function packageAccess(int $studentId, int $packageId, ?int $categoryId = null): bool
    {
        if ($studentId < 1 || $packageId < 1) {
            return false;
        }

        // Explicit admin revocation overrides entitlement, paid-order and manual-grant fallbacks.
        if ($this->isPackageRevoked($studentId, $packageId)) {
            return false;
        }

        if (Schema::hasTable('lebd_ebook_entitlements')) {
            $query = DB::table('lebd_ebook_entitlements')
                ->where('user_id', $studentId)
                ->where('package_id', $packageId)
                ->where('status', 'Active')
                ->where('expires_at', '>', $this->now());
            if ($categoryId !== null) {
                $query->where('category_id', $categoryId);
            }
            if ($query->exists()) {
                return true;
            }
        }

        // Completed payment orders are the durable purchase record. They also
        // restore access if an older manual-grant operation overwrote the
        // package-specific entitlement row for the same category.
        if ($this->hasActivePaidOrder($studentId, $packageId, $categoryId)) {
            return true;
        }

        if (Schema::hasTable('lebd_ebook_manual_grants')) {
            $query = DB::table('lebd_ebook_manual_grants')
                ->where('student_id', $studentId)
                ->where('package_id', $packageId)
                ->where('starts_at', '<=', $this->now())
                ->where('ends_at', '>', $this->now());
            if ($query->exists()) {
                if ($categoryId === null) {
                    return true;
                }
                return DB::table('lebd_ebook_package_categories')
                    ->where('package_id', $packageId)
                    ->where('category_id', $categoryId)
                    ->exists();
            }
        }

        return false;
    }

    /** Return the latest active category access, including manual assignments. */
    public function categoryAccess(int $studentId, int $categoryId): ?object
    {
        if ($studentId < 1 || $categoryId < 1) {
            return null;
        }

        $records = collect();
        if (Schema::hasTable('lebd_ebook_entitlements')) {
            $records = $records->merge(DB::table('lebd_ebook_entitlements')
                ->where('user_id', $studentId)
                ->where('category_id', $categoryId)
                ->where('status', 'Active')
                ->where('expires_at', '>', $this->now())
                ->get(['package_id', 'expires_at'])
                ->map(fn ($row) => (object) [
                    'package_id' => (int) ($row->package_id ?? 0),
                    'expires_at' => $row->expires_at,
                    'source' => 'entitlement',
                ]));
        }

        if (Schema::hasTable('lebd_ebook_payment_orders')) {
            $orders = DB::table('lebd_ebook_payment_orders')
                ->where('student_id', $studentId)
                ->where('category_id', $categoryId)
                ->whereIn('status', ['Complete', 'Completed', 'Paid', 'Success', 'Successful'])
                ->get();
            foreach ($orders as $order) {
                $expiry = $this->completedOrderExpiry($order);
                if ($expiry && $expiry->gt(Carbon::now('Asia/Dhaka'))) {
                    $records->push((object) [
                        'package_id' => (int) $order->package_id,
                        'expires_at' => $expiry->toDateTimeString(),
                        'source' => 'paid_order',
                    ]);
                }
            }
        }

        if (Schema::hasTable('lebd_ebook_manual_grants')) {
            $manual = DB::table('lebd_ebook_manual_grants as g')
                ->join('lebd_ebook_package_categories as pc', 'pc.package_id', '=', 'g.package_id')
                ->where('g.student_id', $studentId)
                ->where('pc.category_id', $categoryId)
                ->where('g.starts_at', '<=', $this->now())
                ->where('g.ends_at', '>', $this->now())
                ->get(['g.package_id', 'g.ends_at']);
            $records = $records->merge($manual->map(fn ($row) => (object) [
                'package_id' => (int) $row->package_id,
                'expires_at' => $row->ends_at,
                'source' => 'manual',
            ]));
        }

        if (Schema::hasTable('lebd_ebook_access_revocations')) {
            $revokedIds = DB::table('lebd_ebook_access_revocations')
                ->where('student_id', $studentId)
                ->pluck('package_id')->map(fn ($id) => (int) $id)->all();
            if ($revokedIds) {
                $records = $records->reject(fn ($row) => in_array((int) ($row->package_id ?? 0), $revokedIds, true));
            }
        }

        return $records->sortByDesc('expires_at')->first();
    }

    public function isPaidPackage(int $studentId, int $packageId, int $categoryId): bool
    {
        if (!$this->packageAccess($studentId, $packageId, $categoryId)
            || !Schema::hasTable('lebd_ebook_payment_orders')) {
            return false;
        }

        return DB::table('lebd_ebook_payment_orders')
            ->where('student_id', $studentId)
            ->where('package_id', $packageId)
            ->where('category_id', $categoryId)
            ->whereIn('status', ['Complete', 'Completed', 'Paid', 'Success', 'Successful'])
            ->exists();
    }
}
