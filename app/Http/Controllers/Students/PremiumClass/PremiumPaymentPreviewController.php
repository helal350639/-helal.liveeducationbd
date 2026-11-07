<?php
namespace App\Http\Controllers\Students\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\PremiumClass\PremiumReferralQuote;

class PremiumPaymentPreviewController extends Controller
{
    public function show(Request $request, PremiumClassPackage $package)
    {
        $student = auth('student')->user();
        abort_unless($student, 403);
        abort_unless($package->is_active, 404);
        if (\App\Services\PremiumClass\PremiumManualAccess::active((int)$student->id,(int)$package->id))
            return redirect()->route('students.premium.all-access')->with('info','এই প্যাকেজ কিনেছেন / Already active.');
        $method = $request->query('payment_method', 'sslcommerz');
        abort_unless(in_array($method, ['referral','sslcommerz'], true), 422);
        // Review passes the valid referral code explicitly. Bind it to this package only.
        // A missing ref is a normal checkout and must never inherit an old referral.
        PremiumReferralQuote::capture($request, (int) $package->id);
        $code = $request->session()->get('premium_ref_code_'.$package->id);
        $quote = PremiumReferralQuote::quote($package, (int)$student->id, $code);
        if ($code && !$quote['referrer_id']) {
            return redirect()->route('premium.payment.review', ['package'=>(int)$package->id, 'ref'=>$code])
                ->withErrors(['payment'=>'Referral Link is no longer valid for this package.']);
        }
        $request->session()->put('premium_quote_cents_'.$package->id, $quote['payable_cents']);
        $main = round((float)$package->price, 2);
        $final = $quote['payable_cents'] / 100;
        abort_if($final < 1, 422, 'Invalid package price.');
        $pending = Schema::hasTable('withdraw_requests')
            ? (float)DB::table('withdraw_requests')->where('student_id', $student->id)->where('status', 'pending')->sum('amount') : 0.0;
        $balance = max(0, round((float)$student->referral_balance - $pending, 2));
        $available = $method === 'referral' && $balance >= $final;
        $remaining = $method === 'referral' ? max(0,round($balance-$final,2)) : $balance;
        $payRoute = $method === 'referral' ? 'premium.payment.referral.checkout' : 'premium.payment.checkout';
        abort_unless(\Illuminate\Support\Facades\Route::has($payRoute), 503);
        return view('premium-class.payment-preview', compact('package','method','main','final','balance','available','remaining','payRoute','quote'));
    }
}
