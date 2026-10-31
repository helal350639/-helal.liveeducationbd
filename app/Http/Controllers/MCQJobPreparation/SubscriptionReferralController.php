<?php

namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Referral-wallet MCQ purchases only; SSLCommerz belongs in SubscriptionPaymentController. */
class SubscriptionReferralController extends Controller
{
    public function pay(Request $request, string $section, int $package)
    {
        abort_unless($section === 'mcq', 404);
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $data = $request->validate(['checkout_token' => ['required', 'string', 'size:40']]);
        $nonceKey = 'mcq_wallet_nonce_'.$package;
        $expected = $request->session()->get($nonceKey);
        if (!is_string($expected) || !hash_equals($expected, $data['checkout_token'])) {
            return redirect()->route('student.mcq.orders')->with('error', 'Checkout মেয়াদোত্তীর্ণ হয়েছে। প্যাকেজের Payment Preview থেকে আবার চেষ্টা করুন।');
        }
        $trx = 'MCQR'.strtoupper(substr(hash('sha256', $student->id.':'.$package.':'.$expected), 0, 24));
        try {
            $wasAlreadyPaid = DB::transaction(function () use ($student, $package, $trx) {
                $wallet = DB::table('students')->where('id', $student->id)->lockForUpdate()->first();
                if (!$wallet) throw new \RuntimeException('Student account not found.');
                $prior = DB::table('lebd_mcq_payment_orders')->where('transaction_id', $trx)->first();
                if ($prior) {
                    if ($prior->student_id == $student->id && $prior->status === 'Complete') return true;
                    throw new \RuntimeException('This checkout was previously attempted. Please start a new preview.');
                }
                $plan = DB::table('mcq_subscription_packages')->where('id', $package)->where('status', 'Active')->first();
                if (!$plan) throw new \RuntimeException('এই প্যাকেজটি বর্তমানে Active নেই।');
                if (!in_array($plan->duration_unit, ['days', 'months', 'years'], true) || (int) $plan->duration_value < 1) {
                    throw new \RuntimeException('Package duration is not valid.');
                }
                $priceCents = (int) round(((float) $plan->price) * 100);
                if ($priceCents <= 0) throw new \RuntimeException('প্যাকেজের মূল্য সঠিক নয়।');
                $pending = (float) DB::table('withdraw_requests')->where('student_id', $student->id)
                    ->where('status', 'pending')->sum('amount');
                $balanceCents = (int) round(((float) $wallet->referral_balance - $pending) * 100);
                if ($balanceCents < $priceCents) throw new \RuntimeException('পর্যাপ্ত Available Referral Balance নেই।');
                $now = Carbon::now('Asia/Dhaka');
                $orderId = DB::table('lebd_mcq_payment_orders')->insertGetId([
                    'student_id' => $student->id, 'package_id' => $package,
                    'transaction_id' => $trx, 'package_name' => $plan->name,
                    'amount' => round($priceCents / 100, 2), 'currency' => 'BDT',
                    'duration_value' => $plan->duration_value, 'duration_unit' => $plan->duration_unit,
                    'status' => 'Complete', 'paid_at' => $now,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('students')->where('id', $student->id)->update([
                    'referral_balance' => round(((float) $wallet->referral_balance) - $priceCents / 100, 2),
                ]);
                $existing = DB::table('lebd_mcq_subscriptions')
                    ->where('student_id', $student->id)->where('package_id', $package)->lockForUpdate()->first();
                $start = $now->copy();
                if ($existing && Carbon::parse($existing->ends_at)->greaterThan($start)) {
                    $start = Carbon::parse($existing->ends_at);
                }
                $end = $start->copy();
                switch ($plan->duration_unit) {
                    case 'days': $end->addDays((int) $plan->duration_value); break;
                    case 'months': $end->addMonthsNoOverflow((int) $plan->duration_value); break;
                    case 'years': $end->addYearsNoOverflow((int) $plan->duration_value); break;
                }
                if ($existing) {
                    DB::table('lebd_mcq_subscriptions')->where('id', $existing->id)->update([
                        'ends_at' => $end, 'last_order_id' => $orderId, 'updated_at' => $now,
                    ]);
                } else {
                    DB::table('lebd_mcq_subscriptions')->insert([
                        'student_id' => $student->id, 'package_id' => $package,
                        'starts_at' => $now, 'ends_at' => $end, 'last_order_id' => $orderId,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
                return false;
            }, 3);
        } catch (Throwable $e) {
            Log::warning('MCQ referral payment rejected', [
                'student_id' => $student->id, 'package_id' => $package, 'message' => $e->getMessage(),
            ]);
            return back()->with('error', $e instanceof \RuntimeException
                ? $e->getMessage() : 'পেমেন্ট সম্পন্ন হয়নি। কোনো টাকা কাটা হলে Account History যাচাই করুন।');
        }
        $request->session()->forget($nonceKey);
        // Show a confirmation only after the committed / previously successful purchase.
        // The standalone notice then redirects to the existing Student Dashboard.
        $notice = $wasAlreadyPaid
            ? 'এই প্যাকেজের পেমেন্ট ইতিমধ্যে সফল হয়েছে।'
            : 'Payment Successful! আপনার MCQ Study Package Access চালু হয়েছে।';

        $request->session()->flash('success', $notice);
        $request->session()->flash('mcq_purchase_success', $notice);

        return response()->view('mcq-study.referral-purchase-success', [
            'notice' => $notice,
            'dashboardUrl' => route('students.dashboard'),
        ]);
    }

    public function orders()
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $orders = DB::table('lebd_mcq_payment_orders as o')
            ->leftJoin('lebd_mcq_subscriptions as s', function ($join) {
                $join->on('s.student_id', '=', 'o.student_id')->on('s.package_id', '=', 'o.package_id');
            })
            ->where('o.student_id', $student->id)
            ->select('o.id', 'o.transaction_id', 'o.package_name', 'o.amount', 'o.status', 'o.paid_at', 'o.created_at', 's.ends_at')
            ->orderByDesc('o.id')->paginate(15);
        return view('student.mcq-orders.index', compact('orders'));
    }
}
