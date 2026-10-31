<?php
namespace App\Http\Controllers\Students\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PremiumPaymentPreviewController extends Controller
{
    public function show(Request $request, PremiumClassPackage $package)
    {
        $student = auth('student')->user();
        abort_unless($student, 403);
        abort_unless($package->is_active, 404);
        $method = $request->query('payment_method', 'sslcommerz');
        abort_unless(in_array($method, ['referral','sslcommerz'], true), 422);
        $main = round((float)$package->price, 2);
        $final = round((float)($package->final_price ?? $package->price), 2);
        abort_if($final < 1 || $final > $main, 422, 'Invalid package price.');
        $pending = Schema::hasTable('withdraw_requests')
            ? (float)DB::table('withdraw_requests')->where('student_id', $student->id)->where('status', 'pending')->sum('amount') : 0.0;
        $balance = max(0, round((float)$student->referral_balance - $pending, 2));
        $available = $method === 'referral' && $balance >= $final;
        $remaining = $method === 'referral' ? max(0,round($balance-$final,2)) : $balance;
        $payRoute = $method === 'referral' ? 'premium.payment.referral.checkout' : 'premium.payment.checkout';
        abort_unless(\Illuminate\Support\Facades\Route::has($payRoute), 503);
        return view('premium-class.payment-preview', compact('package','method','main','final','balance','available','remaining','payRoute'));
    }
}
