<?php

namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Student-facing E-Book library.
 * Paid entitlements and administrator manual grants are combined into one
 * package list, while their source remains distinguishable in the UI.
 */
class EBookPurchasedAccessController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);

        foreach ([
            'lebd_ebook_packages',
            'lebd_ebook_package_categories',
            'lebd_ebook_categories',
        ] as $table) {
            abort_unless(Schema::hasTable($table), 503, 'E-Book subscription data is unavailable.');
        }

        $now = now('Asia/Dhaka')->toDateTimeString();
        $studentId = (int) $student->id;
        $accessRows = collect();

        // Paid / Referral Balance access. Only currently valid, mapped and
        // published categories are counted for a package card.
        if (Schema::hasTable('lebd_ebook_entitlements')) {
            $paidRows = DB::table('lebd_ebook_entitlements as e')
                ->join('lebd_ebook_package_categories as pc', function ($join) {
                    $join->on('pc.package_id', '=', 'e.package_id')
                        ->on('pc.category_id', '=', 'e.category_id');
                })
                ->join('lebd_ebook_categories as c', 'c.id', '=', 'e.category_id')
                ->where('e.user_id', $studentId)
                ->whereNotNull('e.package_id')
                ->where('e.status', 'Active')
                ->where('e.starts_at', '<=', $now)
                ->where('e.expires_at', '>', $now)
                ->where('c.status', 'Active')
                ->whereIn('c.kind', ['department', 'non-department'])
                ->select(
                    'e.package_id',
                    'e.category_id',
                    'e.expires_at',
                    DB::raw("'paid' as access_source")
                )
                ->get();
            $accessRows = $accessRows->merge($paidRows);
        }

        // The payment order is the durable proof of a successful purchase.
        // Reconstruct its package/category access when an older manual grant
        // accidentally replaced the corresponding entitlement row.
        if (Schema::hasTable('lebd_ebook_payment_orders')) {
            $orders = DB::table('lebd_ebook_payment_orders as o')
                ->join('lebd_ebook_package_categories as pc', function ($join) {
                    $join->on('pc.package_id', '=', 'o.package_id')
                        ->on('pc.category_id', '=', 'o.category_id');
                })
                ->join('lebd_ebook_categories as c', 'c.id', '=', 'o.category_id')
                ->where('o.student_id', $studentId)
                ->whereIn('o.status', ['Complete', 'Completed', 'Paid', 'Success', 'Successful'])
                ->where('c.status', 'Active')
                ->whereIn('c.kind', ['department', 'non-department'])
                ->get([
                    'o.package_id', 'o.category_id', 'o.paid_at', 'o.created_at',
                    'o.duration_value', 'o.duration_unit',
                ]);

            foreach ($orders as $order) {
                $base = $order->paid_at ?: $order->created_at;
                if (!$base || (int) $order->duration_value < 1) continue;
                try {
                    $expiry = \Carbon\Carbon::parse($base, 'Asia/Dhaka');
                    switch ((string) $order->duration_unit) {
                        case 'days': $expiry->addDays((int) $order->duration_value); break;
                        case 'months': $expiry->addMonthsNoOverflow((int) $order->duration_value); break;
                        case 'years': $expiry->addYearsNoOverflow((int) $order->duration_value); break;
                        default: continue 2;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
                if ($expiry->gt(\Carbon\Carbon::now('Asia/Dhaka'))) {
                    $accessRows->push((object) [
                        'package_id' => (int) $order->package_id,
                        'category_id' => (int) $order->category_id,
                        'expires_at' => $expiry->toDateTimeString(),
                        'access_source' => 'paid_order',
                    ]);
                }
            }
        }

        // Admin-assigned access lives in the manual_grants table and must be
        // included here as well; it is not necessarily a payment order or an
        // entitlement row. This was the missing source behind the count gap.
        if (Schema::hasTable('lebd_ebook_manual_grants')) {
            $manualRows = DB::table('lebd_ebook_manual_grants as g')
                ->join('lebd_ebook_package_categories as pc', 'pc.package_id', '=', 'g.package_id')
                ->join('lebd_ebook_categories as c', 'c.id', '=', 'pc.category_id')
                ->where('g.student_id', $studentId)
                ->where('g.starts_at', '<=', $now)
                ->where('g.ends_at', '>', $now)
                ->where('c.status', 'Active')
                ->whereIn('c.kind', ['department', 'non-department'])
                ->select(
                    'g.package_id',
                    'pc.category_id',
                    DB::raw('g.ends_at as expires_at'),
                    DB::raw("'manual' as access_source")
                )
                ->get();
            $accessRows = $accessRows->merge($manualRows);
        }

        // Admin revocation overrides all historical sources (paid order,
        // entitlement and manual grant) for this exact package/student pair.
        if (Schema::hasTable('lebd_ebook_access_revocations')) {
            $revokedPackageIds = DB::table('lebd_ebook_access_revocations')
                ->where('student_id', $studentId)
                ->pluck('package_id')->map(fn ($id) => (int) $id)->all();
            if ($revokedPackageIds) {
                $accessRows = $accessRows->reject(
                    fn ($row) => in_array((int) $row->package_id, $revokedPackageIds, true)
                )->values();
            }
        }

        // One card per package, even if it has several categories or both
        // paid and manual access. Keep the furthest valid expiry and count
        // unique active categories only.
        $summaryByPackage = $accessRows
            ->groupBy(fn ($row) => (int) $row->package_id)
            ->map(function ($rows) {
                return (object) [
                    'ends_at' => $rows->max('expires_at'),
                    'category_count' => $rows->pluck('category_id')->map(fn ($id) => (int) $id)->unique()->count(),
                    'has_manual_access' => $rows->contains(fn ($row) => $row->access_source === 'manual'),
                ];
            });

        $packageIds = $summaryByPackage->keys()->map(fn ($id) => (int) $id)->all();
        $packages = collect();
        if ($packageIds) {
            $packageRows = DB::table('lebd_ebook_packages')
                ->whereIn('id', $packageIds)
                ->get(['id', 'name', 'sort_order']);

            $packages = $packageRows->map(function ($package) use ($summaryByPackage) {
                $summary = $summaryByPackage->get((int) $package->id);
                $package->package_id = (int) $package->id;
                $package->ends_at = $summary->ends_at;
                $package->category_count = (int) $summary->category_count;
                $package->has_manual_access = (bool) $summary->has_manual_access;
                return $package;
            })->sort(function ($a, $b) {
                $order = ((int) ($a->sort_order ?? 0)) <=> ((int) ($b->sort_order ?? 0));
                return $order !== 0 ? $order : ((int) $b->package_id <=> (int) $a->package_id);
            })->values();
        }

        // Keep purchase history separate from Admin grants. A manual assignment
        // must appear in the library, but must never be labelled as purchased.
        $paidOrderStatuses = ['Complete', 'Completed', 'Paid', 'Success', 'Successful'];
        $purchasedPackageIds = [];
        if (Schema::hasTable('lebd_ebook_payment_orders') && $packageIds) {
            $purchasedPackageIds = DB::table('lebd_ebook_payment_orders')
                ->where('student_id', $studentId)
                ->whereIn('package_id', $packageIds)
                ->whereIn('status', $paidOrderStatuses)
                ->pluck('package_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        foreach ($packages as $package) {
            $package->has_paid_purchase = in_array((int) $package->package_id, $purchasedPackageIds, true);
        }

        $selectedPackage = null;
        $categories = collect();

        if ($request->filled('package')) {
            $data = $request->validate([
                'package' => ['required', 'integer', 'min:1'],
            ]);

            $selectedPackage = $packages->first(
                fn ($package) => (int) $package->package_id === (int) $data['package']
            );

            abort_unless($selectedPackage, 403, 'এই E-Book প্যাকেজটি বর্তমানে আপনার Active Access-এ নেই।');

            // For the selected package, combine its currently active paid and
            // manual category access. A category is shown only if it is mapped
            // to this exact package and is currently Active.
            $selectedRows = $accessRows
                ->filter(fn ($row) => (int) $row->package_id === (int) $selectedPackage->package_id)
                ->groupBy(fn ($row) => (int) $row->category_id);

            $categoryIds = $selectedRows->keys()->map(fn ($id) => (int) $id)->all();
            if ($categoryIds) {
                $categoryExpiry = $selectedRows->map(fn ($rows) => $rows->max('expires_at'));
                $categories = DB::table('lebd_ebook_categories')
                    ->whereIn('id', $categoryIds)
                    ->where('status', 'Active')
                    ->whereIn('kind', ['department', 'non-department'])
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'kind', 'name', 'icon', 'front_cover', 'sort_order'])
                    ->map(function ($category) use ($categoryExpiry) {
                        $category->access_expires_at = $categoryExpiry->get((int) $category->id);
                        return $category;
                    });
            }
        }

        // Backward-compatible fallback for legacy category entitlements that
        // pre-date package_id mapping. Preserve the existing reader links.
        $legacyCategories = collect();
        if ($packages->isEmpty() && !$selectedPackage && Schema::hasTable('lebd_ebook_entitlements')) {
            $legacyCategories = DB::table('lebd_ebook_entitlements as e')
                ->join('lebd_ebook_categories as c', 'c.id', '=', 'e.category_id')
                ->where('e.user_id', $studentId)
                ->whereNull('e.package_id')
                ->where('e.status', 'Active')
                ->where('e.starts_at', '<=', $now)
                ->where('e.expires_at', '>', $now)
                ->where('c.status', 'Active')
                ->whereIn('c.kind', ['department', 'non-department'])
                ->select(
                    'c.id', 'c.kind', 'c.name', 'c.icon', 'c.front_cover', 'c.sort_order',
                    DB::raw('MAX(e.expires_at) as access_expires_at')
                )
                ->groupBy('c.id', 'c.kind', 'c.name', 'c.icon', 'c.front_cover', 'c.sort_order')
                ->orderBy('c.sort_order')
                ->orderBy('c.name')
                ->get();
        }

        return view('ebook.dashboard-categories', compact(
            'packages', 'selectedPackage', 'categories', 'legacyCategories'
        ));
    }
}
