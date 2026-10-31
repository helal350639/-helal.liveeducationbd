<?php

namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PurchasedMcqCategoriesController extends Controller
{
    private const DEPARTMENTS = [
        'Civil','Electrical','Mechanical','Computer','Electronic',
        'Power','RAC','Architecture','Textile','Agriculture',
    ];
    private const GENERAL = ['Bangla','English','Bangladesh','International','Math','Others'];

    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        // No access is granted without an authenticated student and a current paid subscription.
        $subscriptions = collect();
        $categoryKeys = [];
        if ($student && Schema::hasTable('lebd_mcq_subscriptions') && Schema::hasTable('mcq_subscription_packages')) {
            $subscriptions = DB::table('lebd_mcq_subscriptions as s')
                ->join('mcq_subscription_packages as p', 'p.id', '=', 's.package_id')
                ->where('s.student_id', $student->id)
                ->where('s.starts_at', '<=', now('Asia/Dhaka'))
                ->where('s.ends_at', '>', now('Asia/Dhaka'))
                ->select('s.package_id', 's.ends_at', 'p.name', 'p.access_type')
                ->get();

            // Fail-closed mapping: a package's access_type alone is NOT evidence
            // that every category is included. Resolve only IDs proven in the
            // category junction table AND a verified category-name table.
            if ($subscriptions->isNotEmpty() && Schema::hasTable('mcq_subscription_package_categories')) {
                $ids = $subscriptions->pluck('package_id')->all();
                $assigned = DB::table('mcq_subscription_package_categories')
                    ->whereIn('package_id', $ids)->pluck('category_id')->unique()->all();
                // The actual category identity table/field has not yet been established.
                // Intentionally keep cards locked rather than guessing category IDs.
                // $assigned is deliberately not used to authorize access until verified.
            }
        }
        return view('mcq-study.purchased-categories', [
            'departments' => self::DEPARTMENTS,
            'general' => self::GENERAL,
            'subscriptions' => $subscriptions,
            'categoryKeys' => $categoryKeys,
            'isStudent' => (bool) $student,
        ]);
    }
}
