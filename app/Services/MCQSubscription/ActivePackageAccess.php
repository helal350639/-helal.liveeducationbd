<?php
namespace App\Services\MCQSubscription;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only union of existing successful paid subscriptions and active admin grants.
 * Never creates an order, subscription, or payment. */
final class ActivePackageAccess
{
    public static function rows(int $studentId)
    {
        $now = now('Asia/Dhaka')->toDateTimeString();
        $result = collect();
        if ($studentId < 1 || !Schema::hasTable('mcq_subscription_packages')) return $result;
        if (Schema::hasTable('lebd_mcq_subscriptions')) {
            $paid = DB::table('lebd_mcq_subscriptions as s')
                ->join('mcq_subscription_packages as p', 'p.id', '=', 's.package_id')
                ->where('s.student_id', $studentId)
                ->where('s.starts_at', '<=', $now)->where('s.ends_at', '>', $now)
                ->select('s.package_id', 's.ends_at', 'p.name', 'p.access_type')
                ->get();
            $result = $result->concat($paid);
        }
        if (Schema::hasTable('lebd_mcq_manual_grants')) {
            $manual = DB::table('lebd_mcq_manual_grants as g')
                ->join('mcq_subscription_packages as p', 'p.id', '=', 'g.package_id')
                ->where('g.student_id', $studentId)->where('g.status', 'Active')
                ->where('g.starts_at', '<=', $now)->where('g.ends_at', '>', $now)
                ->select('g.package_id', 'g.ends_at', 'p.name', 'p.access_type')
                ->get();
            $result = $result->concat($manual);
        }
        // One card per package; retain the longer current entitlement if both exist.
        return $result->sortByDesc('ends_at')->unique('package_id')->sortBy('ends_at')->values();
    }
}
