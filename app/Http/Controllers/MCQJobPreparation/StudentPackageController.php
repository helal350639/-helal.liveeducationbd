<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use App\Services\MCQSubscription\ActivePackageAccess;
use App\Services\MCQSubscription\ReferralQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Preserve the existing MCQ storefront; add read-only active-entitlement awareness. */
class StudentPackageController extends Controller
{
    private function checkSection(string $section): void
    {
        abort_unless($section === 'mcq', 404);
    }

    private function groups(array $ids): array
    {
        if (!$ids) { return []; }
        $records = DB::table('mcq_subscription_package_categories as pc')
            ->join('job_prep_categories as c', 'c.id', '=', 'pc.category_id')
            ->whereIn('pc.package_id', $ids)
            ->where('c.status', 'Active')
            ->select('pc.package_id', 'c.name', 'c.kind')
            ->orderBy('c.sort_order')->orderBy('c.id')->get();
        $result = [];
        foreach ($records as $row) {
            $result[(int)$row->package_id][$row->kind][] = $row->name;
        }
        return $result;
    }

    /** Map active paid/manual packages to their latest access end-date. */
    private function activeAccess()
    {
        $studentId = Auth::guard('student')->id();
        if (!$studentId) return collect();
        return ActivePackageAccess::rows((int)$studentId)->keyBy('package_id');
    }

    public function catalog(string $section = 'mcq')
    {
        $this->checkSection($section);
        $packages = DB::table('mcq_subscription_packages')
            ->where('status', 'Active')->orderBy('sort_order')->orderBy('id')->get();
        $ids = $packages->pluck('id')->map(fn ($id) => (int)$id)->all();
        $categories = $this->groups($ids);
        $activePackageAccess = $this->activeAccess();
        return view('homepage.packages.index', compact('packages', 'categories', 'section', 'activePackageAccess'));
    }

    public function detail(Request $request, string $section, int $package)
    {
        $this->checkSection($section);
        $record = DB::table('mcq_subscription_packages')
            ->where('status','Active')->where('id', $package)->first();
        abort_unless($record, 404);
        // Capture the sharer's code before login, then verify ownership, identity,
        // discount and status again at checkout and at the payment POST.
        if ($request->has('ref')) {
            $incoming = trim((string)$request->query('ref', ''));
            if ($incoming !== '' && strlen($incoming) <= 20
                && DB::table('students')->where('referral_id', $incoming)->exists()) {
                $request->session()->put('mcq_referrer_code_'.$package, $incoming);
            } else {
                $request->session()->forget('mcq_referrer_code_'.$package);
            }
        } else {
            // A normal, non-referral package visit never inherits an older referral.
            $request->session()->forget('mcq_referrer_code_'.$package);
        }
        $groups = $this->groups([(int)$record->id]);
        $activePackageAccess = $this->activeAccess();
        $ownedAccess = $activePackageAccess->get((int)$record->id);
        return view('homepage.packages.details', [
            'package' => $record, 'categories' => $groups[(int)$record->id] ?? [],
            'section' => $section, 'activePackageAccess' => $activePackageAccess,
            'ownedAccess' => $ownedAccess,
            'referralQuote' => ReferralQuote::forBuyer($record, Auth::guard('student')->user(), $request->session()->get('mcq_referrer_code_'.$package)),
        ]);
    }

    /** Existing checkout remains unchanged for non-owners. Active owners cannot re-checkout. */
    public function checkout(Request $request, string $section, int $package)
    {
        $this->checkSection($section);
        $record = DB::table('mcq_subscription_packages')
            ->where('status', 'Active')->where('id', $package)->first();
        abort_unless($record, 404);
        $activePackageAccess = $this->activeAccess();
        if ($activePackageAccess->has((int)$record->id)) {
            return redirect()->route('student.mcq.purchased')
                ->with('success', 'আপনি ইতিমধ্যে এই প্যাকেজে যুক্ত আছেন। মেয়াদ শেষ না হওয়া পর্যন্ত আবার কেনার প্রয়োজন নেই।');
        }
        $quote = ReferralQuote::forBuyer($record, Auth::guard('student')->user(), $request->session()->get('mcq_referrer_code_'.$package));
        if (Auth::guard('student')->check()) {
            $request->session()->put('mcq_quote_cents_'.$package, $quote['payable_cents']);
        }
        $groups = $this->groups([(int) $record->id]);
        return view('homepage.packages.order-review', [
            'package' => $record,
            'categories' => $groups[(int) $record->id] ?? [],
            'section' => $section,
            'activePackageAccess' => $activePackageAccess,
            'ownedAccess' => null,
            'referralQuote' => $quote,
        ]);
    }

    public function index() { return $this->catalog('mcq'); }
    public function show(int $package) { return $this->detail(request(), 'mcq', $package); }
}
