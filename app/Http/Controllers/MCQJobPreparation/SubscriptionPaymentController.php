<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use App\Library\SslCommerz\MCQSubscriptionSslCommerz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/** Isolated MCQ checkout: never creates a legacy batch/course order. */
class SubscriptionPaymentController extends Controller
{
    public function start(Request $request, string $section, int $package)
    {
        abort_unless($section === 'mcq', 404);
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $record = DB::table('mcq_subscription_packages')->where('id', $package)->where('status','Active')->first();
        abort_unless($record, 404);
        $amount = round((float)$record->price, 2);
        if ($amount < 10 || $amount > 500000) {
            return back()->with('error', 'এই প্যাকেজের মূল্য SSLCommerz-এর গ্রহণযোগ্য সীমার বাইরে।');
        }
        $phone = preg_replace('/[^0-9+]/', '', (string)($student->contact ?? $student->phone ?? ''));
        if ($phone === '' || !filter_var((string)$student->email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'পেমেন্টের আগে Student Profile-এ সঠিক ইমেইল ও মোবাইল নম্বর দিন।');
        }
        $trx = 'MCQ'.strtoupper(Str::random(20));
        $now = now('Asia/Dhaka');
        DB::table('lebd_mcq_payment_orders')->insert([
            'student_id'=>$student->id, 'package_id'=>$record->id, 'transaction_id'=>$trx,
            'package_name'=>$record->name, 'amount'=>$amount, 'currency'=>'BDT',
            'duration_value'=>$record->duration_value, 'duration_unit'=>$record->duration_unit,
            'status'=>'Pending', 'created_at'=>$now, 'updated_at'=>$now,
        ]);
        $payload = [
            'total_amount'=>number_format($amount,2,'.',''), 'currency'=>'BDT', 'tran_id'=>$trx,
            'cus_name'=>mb_substr((string)($student->name ?: 'Student'),0,50),
            'cus_email'=>$student->email, 'cus_phone'=>$phone, 'cus_add1'=>'Bangladesh',
            'cus_city'=>'Dhaka', 'cus_country'=>'Bangladesh', 'shipping_method'=>'NO',
            'product_name'=>mb_substr($record->name,0,150),
            'product_category'=>'Online MCQ Subscription', 'product_profile'=>'non-physical-goods',
            'value_a'=>$trx, 'value_b'=>(string)$record->id,
        ];
        try {
            $answer = (new MCQSubscriptionSslCommerz())->makePayment($payload, 'checkout', 'json');
            $parsed = is_array($answer) ? $answer : json_decode((string)$answer, true);
            $url = $parsed['data'] ?? null;
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
                && str_starts_with(strtolower($url), 'https://')
                && preg_match('/(^|\\.)sslcommerz\\.com$/i', (string)parse_url($url, PHP_URL_HOST))) {
                return redirect()->away($url);
            }
            Log::warning('MCQ SSLCommerz session initiation failed', ['transaction_id'=>$trx]);
        } catch (Throwable $e) {
            Log::error('MCQ gateway start error', ['transaction_id'=>$trx, 'message'=>$e->getMessage()]);
        }
        DB::table('lebd_mcq_payment_orders')->where('transaction_id',$trx)->where('status','Pending')
            ->update(['status'=>'Failed', 'updated_at'=>now()]);
        return back()->with('error', 'Payment Gateway চালু করা যায়নি। পরে আবার চেষ্টা করুন।');
    }

    public function callback(Request $request, string $event)
    {
        abort_unless(in_array($event,['success','fail','cancel','ipn'],true),404);
        $trx = (string)$request->input('tran_id','');
        if ($trx === '' || strlen($trx) > 30) return response('Invalid transaction',400);
        $order = DB::table('lebd_mcq_payment_orders')->where('transaction_id',$trx)->first();
        if (!$order) return response('Order not found',404);
        if ($event === 'fail' || $event === 'cancel') {
            DB::table('lebd_mcq_payment_orders')->where('id',$order->id)->where('status','Pending')
                ->update(['status'=>$event==='fail'?'Failed':'Canceled','updated_at'=>now()]);
            return $this->result($order->id, $event === 'fail' ? 'failed':'canceled', $event !== 'ipn');
        }
        if ($order->status === 'Complete') return $this->result($order->id, 'success', $event !== 'ipn');
        if ($order->status !== 'Pending') return $this->result($order->id, 'failed', $event !== 'ipn');
        $callbackTrx = (string)$request->input('tran_id','');
        $postedAmount = $request->input('amount');
        $valId = $request->input('val_id');
        if (!$valId || !is_numeric($postedAmount) || abs((float)$postedAmount-(float)$order->amount) > 0.01
            || strtoupper((string)$request->input('currency')) !== 'BDT' || $callbackTrx !== $order->transaction_id) {
            Log::warning('MCQ payment callback does not match order', ['transaction_id'=>$trx]);
            return $this->result($order->id, 'pending', $event !== 'ipn');
        }
        try {
            $validated = (new MCQSubscriptionSslCommerz())->orderValidate($request->all(),
                $order->transaction_id, (float)$order->amount, $order->currency);
        } catch (Throwable $e) {
            Log::error('MCQ payment verification exception', ['transaction_id'=>$trx,'message'=>$e->getMessage()]);
            $validated = false;
        }
        if ($validated !== true) return $this->result($order->id, 'pending', $event !== 'ipn');
        DB::transaction(function () use ($order, $request) {
            $locked = DB::table('lebd_mcq_payment_orders')->where('id',$order->id)->lockForUpdate()->first();
            if (!$locked || $locked->status !== 'Pending') return;
            $start = Carbon::now('Asia/Dhaka');
            $existing = DB::table('lebd_mcq_subscriptions')
                ->where('student_id',$locked->student_id)->where('package_id',$locked->package_id)
                ->lockForUpdate()->first();
            if ($existing && Carbon::parse($existing->ends_at)->greaterThan($start)) {
                $start = Carbon::parse($existing->ends_at);
            }
            $ends = $start->copy();
            switch($locked->duration_unit) {
                case 'days': $ends->addDays((int)$locked->duration_value); break;
                case 'months': $ends->addMonthsNoOverflow((int)$locked->duration_value); break;
                case 'years': $ends->addYearsNoOverflow((int)$locked->duration_value); break;
                default: throw new \RuntimeException('Invalid subscription duration');
            }
            if ($existing) {
                DB::table('lebd_mcq_subscriptions')->where('id',$existing->id)->update([
                    'ends_at'=>$ends, 'updated_at'=>now(), 'last_order_id'=>$locked->id,
                ]);
            } else {
                DB::table('lebd_mcq_subscriptions')->insert([
                    'student_id'=>$locked->student_id,'package_id'=>$locked->package_id,
                    'starts_at'=>Carbon::now('Asia/Dhaka'), 'ends_at'=>$ends,
                    'last_order_id'=>$locked->id, 'created_at'=>now(), 'updated_at'=>now(),
                ]);
            }
            DB::table('lebd_mcq_payment_orders')->where('id',$locked->id)->update([
                'status'=>'Complete','gateway_validation_id'=>substr((string)$request->input('val_id'),0,100),
                'paid_at'=>now(), 'updated_at'=>now(),
            ]);
        });
        return $this->result($order->id, 'success', $event !== 'ipn');
    }

    private function result(int $orderId, string $state, bool $browser)
    {
        if (!$browser) return response('OK',200);
        $order = DB::table('lebd_mcq_payment_orders')->find($orderId);
        return view('homepage.packages.payment-result', compact('order','state'));
    }
}
