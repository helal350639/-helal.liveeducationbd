<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchasedMcqAccessController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        foreach (['lebd_mcq_subscriptions', 'mcq_subscription_packages', 'mcq_subscription_package_categories', 'job_prep_categories'] as $table) {
            abort_unless(Schema::hasTable($table), 503, 'MCQ subscription data is unavailable.');
        }
        $now = now();
        $packages = DB::table('lebd_mcq_subscriptions as s')
            ->join('mcq_subscription_packages as p', 'p.id', '=', 's.package_id')
            ->where('s.student_id', $student->id)
            ->where('s.starts_at', '<=', $now)
            ->where('s.ends_at', '>', $now)
            ->select('s.package_id', 's.ends_at', 'p.name', 'p.access_type')
            ->orderBy('s.ends_at')->get()->unique('package_id')->values();
        $categoryMap = collect();
        if ($packages->isNotEmpty()) {
            $categoryMap = DB::table('mcq_subscription_package_categories as m')
                ->join('job_prep_categories as c', 'c.id', '=', 'm.category_id')
                ->whereIn('m.package_id', $packages->pluck('package_id'))
                ->where('c.status', 'Active')
                ->whereIn('c.kind', ['department', 'non-department'])
                ->select('m.package_id', 'c.id', 'c.kind', 'c.name')
                ->orderBy('c.id')->get()->groupBy('package_id');
        }
        $selectedPackage = null;
        $categories = collect();
        if ($request->filled('package')) {
            $data = $request->validate(['package' => 'required|integer|min:1']);
            $selectedPackage = $packages->first(fn ($p) => (int)$p->package_id === (int)$data['package']);
            abort_unless($selectedPackage, 403, 'The purchased package is not active.');
            $categories = $categoryMap->get($selectedPackage->package_id, collect());
        }
        return view('mcq-study.purchased-access', compact('packages', 'categoryMap', 'selectedPackage', 'categories'));
    }
}
