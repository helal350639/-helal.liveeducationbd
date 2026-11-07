<?php
namespace App\Http\Controllers\Students\PremiumClass;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PremiumReferralLinksController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();
        abort_unless($student, 401);
        $packages = collect();
        if (Schema::hasTable('lebd_premium_class_orders')) {
            $ids = DB::table('lebd_premium_class_orders')
                ->where('student_id', $student->id)->where('status','paid')
                ->where('expires_at','>',now())->distinct()->pluck('package_id');
            $packages = DB::table('lebd_premium_class_packages')
                ->whereIn('id', $ids)->where('is_active',1)->get();
        }
        return view('premium-class.referral-links', compact('packages','student'));
    }

    /** The share URL displays ONE package only; other packages are never referral-qualified. */
    public function show(\Illuminate\Http\Request $request, \App\Models\PremiumClass\PremiumClassPackage $package)
    {
        abort_unless($package->is_active, 404);
        $code = trim((string) $request->query('ref', ''));
        abort_unless($code !== '' && strlen($code) <= 255, 404);
        abort_unless(Schema::hasTable('lebd_premium_class_orders'), 404);
        $owner = DB::table('students')->where('referral_id', $code)->first(['id','name']);
        abort_unless($owner, 404);
        $viewerId = auth('student')->id();
        if ($viewerId !== null && (int)$viewerId === (int)$owner->id) {
            return redirect()->route('premium.recordings.packages')
                ->with('info', 'নিজের Referral Link ব্যবহার করে Discount নেওয়া যাবে না।');
        }
        $eligible = DB::table('lebd_premium_class_orders')
            ->where('student_id', $owner->id)
            ->where('package_id', $package->id)
            ->where('status', 'paid')
            ->where('expires_at', '>', now())
            ->exists();
        abort_unless($eligible, 404, 'Referral link is no longer active.');
        $basePrice = round((float)($package->final_price ?? $package->price), 2);
        $extraFee = round((float)($package->extra_fee ?? 0), 2);
        $referralDiscount = min(max(0,round((float)($package->referral_discount ?? 0),2)), max(0,$basePrice+$extraFee-1));
        $payable = round($basePrice + $extraFee - $referralDiscount, 2);
        $purchaseUrl = route('premium.payment.review',['package'=>(int)$package->id]).'?'.http_build_query(['ref'=>$code]);
        return view('premium-class.referral-package', compact('package','owner','basePrice','extraFee','referralDiscount','payable','purchaseUrl'));
    }

}
