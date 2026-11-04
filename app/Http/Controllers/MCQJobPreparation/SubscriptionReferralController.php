<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use App\Services\MCQSubscription\ActivePackageAccess;
use App\Services\MCQSubscription\ReferralQuote;
use App\Services\MCQSubscription\MCQPaymentSms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Referral-balance checkout. Existing order, wallet nonce, entitlement and dashboard remain. */
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
            $already = DB::transaction(function () use ($student, $package, $trx, $request) {
                $wallet = DB::table('students')->where('id', $student->id)->lockForUpdate()->first();
                if (!$wallet) throw new \RuntimeException('Student account not found.');
                $prior = DB::table('lebd_mcq_payment_orders')->where('transaction_id', $trx)->first();
                if ($prior) {
                    if ((int)$prior->student_id === (int)$student->id && $prior->status === 'Complete') return true;
                    throw new \RuntimeException('This checkout was previously attempted. Please start a new preview.');
                }
                $plan = DB::table('mcq_subscription_packages')->where('id', $package)->where('status', 'Active')->first();
                if (!$plan) throw new \RuntimeException('এই প্যাকেজটি বর্তমানে Active নেই।');
                if (!in_array($plan->duration_unit, ['days', 'months', 'years'], true) || (int)$plan->duration_value < 1) {
                    throw new \RuntimeException('Package duration is not valid.');
                }
                if (ActivePackageAccess::rows((int)$student->id)->contains(fn($row) => (int)$row->package_id === $package)) {
                    throw new \RuntimeException('আপনি ইতিমধ্যে এই প্যাকেজে যুক্ত আছেন।');
                }
                $quote = ReferralQuote::forBuyer($plan, $student, $request->session()->get('mcq_referrer_code_'.$package));
                $expectedCents = $request->session()->get('mcq_quote_cents_'.$package);
                if ($expectedCents !== null && (int)$expectedCents !== $quote['payable_cents']) {
                    throw new \RuntimeException('Referral Discount বা প্যাকেজ মূল্য বদলেছে। Order Review থেকে নতুন করে Preview করুন।');
                }
                if ($quote['referrer_id'] && $expectedCents === null) {
                    throw new \RuntimeException('Referral Checkout যাচাই হয়নি। Order Review থেকে আবার চেষ্টা করুন।');
                }
                $priceCents = $quote['payable_cents'];
                if ($priceCents <= 0) throw new \RuntimeException('প্যাকেজের মূল্য সঠিক নয়।');
                $pending = (float)DB::table('withdraw_requests')->where('student_id', $student->id)
                    ->where('status', 'pending')->sum('amount');
                $balanceCents = (int)round(((float)$wallet->referral_balance - $pending)*100);
                if ($balanceCents < $priceCents) throw new \RuntimeException('পর্যাপ্ত Available Referral Balance নেই।');
                $now = Carbon::now('Asia/Dhaka');
                $orderId = DB::table('lebd_mcq_payment_orders')->insertGetId([
                    'student_id'=>$student->id, 'package_id'=>$package,
                    'transaction_id'=>$trx, 'package_name'=>$plan->name,
                    'amount'=>number_format($priceCents/100,2,'.',''), 'currency'=>'BDT',
                    'duration_value'=>$plan->duration_value, 'duration_unit'=>$plan->duration_unit,
                    'status'=>'Complete', 'paid_at'=>$now, 'created_at'=>$now, 'updated_at'=>$now,
                ]);
                ReferralQuote::record($orderId, (int)$student->id, $package, $quote);
                DB::table('students')->where('id', $student->id)->update([
                    'referral_balance'=>number_format(((int)round((float)$wallet->referral_balance*100)-$priceCents)/100,2,'.',''),
                ]);
                $existing = DB::table('lebd_mcq_subscriptions')
                    ->where('student_id', $student->id)->where('package_id', $package)->lockForUpdate()->first();
                $start = $now->copy();
                if ($existing && Carbon::parse($existing->ends_at)->greaterThan($start)) $start = Carbon::parse($existing->ends_at);
                $end = $start->copy();
                switch ($plan->duration_unit) {
                    case 'days': $end->addDays((int)$plan->duration_value); break;
                    case 'months': $end->addMonthsNoOverflow((int)$plan->duration_value); break;
                    case 'years': $end->addYearsNoOverflow((int)$plan->duration_value); break;
                }
                if ($existing) {
                    DB::table('lebd_mcq_subscriptions')->where('id', $existing->id)->update([
                        'ends_at'=>$end, 'last_order_id'=>$orderId, 'updated_at'=>$now,
                    ]);
                } else {
                    DB::table('lebd_mcq_subscriptions')->insert([
                        'student_id'=>$student->id,'package_id'=>$package,
                        'starts_at'=>$now,'ends_at'=>$end,'last_order_id'=>$orderId,
                        'created_at'=>$now,'updated_at'=>$now,
                    ]);
                }
                // Under the same SQL transaction: bonus to link owner, never buyer.
                ReferralQuote::credit($orderId);
                return false;
            }, 3);
        } catch (Throwable $e) {
            Log::warning('MCQ referral payment rejected', [
                'student_id'=>$student->id,'package_id'=>$package,'message'=>$e->getMessage(),
            ]);
            return back()->with('error', $e instanceof \RuntimeException
                ? $e->getMessage() : 'পেমেন্ট সম্পন্ন হয়নি। কোনো টাকা কাটা হলে Account History যাচাই করুন।');
        }
        $order = DB::table('lebd_mcq_payment_orders')->where('transaction_id',$trx)->first();
        if ($order) MCQPaymentSms::notify((int)$order->id);
        $request->session()->forget([$nonceKey, 'mcq_referrer_code_'.$package, 'mcq_quote_cents_'.$package]);
        $notice = $already ? 'এই প্যাকেজের পেমেন্ট ইতিমধ্যে সফল হয়েছে।'
            : 'Payment Successful! আপনার MCQ Study Package Access চালু হয়েছে।';
        $request->session()->flash('success', $notice);
        $request->session()->flash('mcq_purchase_success', $notice);
        // Successful wallet payment goes straight to the student's purchased MCQ packages.
        return redirect()->route('student.mcq.purchased')->with('success', $notice);
    }

    public function orders()
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $orders = DB::table('lebd_mcq_payment_orders as o')
            ->leftJoin('lebd_mcq_subscriptions as s', function ($join) {
                $join->on('s.student_id','=','o.student_id')->on('s.package_id','=','o.package_id');
            })
            ->where('o.student_id', $student->id)
            ->select('o.id','o.transaction_id','o.package_name','o.amount','o.status','o.paid_at','o.created_at','s.ends_at')
            ->orderByDesc('o.id')->paginate(15);
        return view('student.mcq-orders.index',compact('orders'));
    }
}
