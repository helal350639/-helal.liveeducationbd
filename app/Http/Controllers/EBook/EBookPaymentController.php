<?php
namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use App\Library\SslCommerz\EBookSslCommerz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EBookPaymentController extends Controller
{
    public function pay(Request $request, string $kind, int $category, int $package)
    {
        $request->validate(['agree' => 'accepted']);
        abort_unless(in_array($kind, ['department', 'non-department'], true), 404);
        $student = Auth::guard('student')->user();
        abort_unless($student, 401, 'Please sign in to your student account.');
        $cat = DB::table('lebd_ebook_categories')->where('id', $category)
            ->where('kind', $kind)->where('status', 'Active')->first();
        abort_unless($cat, 404);
        $p = DB::table('lebd_ebook_packages as p')
            ->join('lebd_ebook_package_categories as pc', 'pc.package_id', '=', 'p.id')
            ->where('p.id', $package)->where('pc.category_id', $category)
            ->where('p.status', 'Active')->select('p.*')->first();
        abort_unless($p, 404);
        $now = Carbon::now('Asia/Dhaka');
        $offerActive = !$p->offer_ends_at || Carbon::parse($p->offer_ends_at, 'Asia/Dhaka')->endOfDay()->gte($now);
        $amount = round((float)($offerActive ? $p->price : $p->main_price), 2);
        if ($amount < 10 || $amount > 500000) {
            return back()->with('error', 'প্যাকেজের মূল্য Gateway গ্রহণযোগ্য সীমায় নেই।');
        }
        if (!in_array($p->duration_unit, ['days', 'months', 'years'], true) || (int)$p->duration_value < 1) {
            return back()->with('error', 'প্যাকেজের মেয়াদ সঠিক নয়।');
        }
        $phone = preg_replace('/[^0-9+]/', '', (string)($student->contact ?? $student->phone ?? ''));
        $email = (string)($student->email ?? '');
        if ($phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Payment-এর আগে Student Profile-এ সঠিক ফোন ও ইমেইল যোগ করুন।');
        }
        $trx = 'EBK'.strtoupper(Str::random(20));
        DB::table('lebd_ebook_payment_orders')->insert([
            'student_id'=>$student->id,'category_id'=>$category,'package_id'=>$p->id,
            'transaction_id'=>$trx,'package_name'=>$p->name,'amount'=>$amount,'currency'=>'BDT',
            'duration_value'=>$p->duration_value,'duration_unit'=>$p->duration_unit,
            'status'=>'Pending','created_at'=>now(),'updated_at'=>now(),
        ]);
        $payload = [
            'total_amount'=>number_format($amount,2,'.',''),'currency'=>'BDT','tran_id'=>$trx,
            'cus_name'=>mb_substr((string)($student->name ?: 'Student'),0,50),
            'cus_email'=>$email,'cus_phone'=>$phone,'cus_add1'=>'Bangladesh',
            'cus_city'=>'Dhaka','cus_country'=>'Bangladesh','shipping_method'=>'NO',
            'product_name'=>mb_substr((string)$p->name,0,150),
            'product_category'=>'E-Book Premium Subscription',
            'product_profile'=>'non-physical-goods','value_a'=>$trx,
            'value_b'=>(string)$p->id,'value_c'=>(string)$category,
        ];
        try {
            $answer = (new EBookSslCommerz())->makePayment($payload, 'checkout', 'json');
            $parsed = is_array($answer) ? $answer : json_decode((string)$answer, true);
            $url = is_array($parsed) ? ($parsed['data'] ?? null) : null;
            // Allow only SSLCommerz HTTPS checkout hosts, never an arbitrary redirect URL.
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
                $host = strtolower((string)parse_url($url, PHP_URL_HOST));
                if ($scheme === 'https' && preg_match('/(^|\.)sslcommerz\.com$/i', $host)) {
                    return redirect()->away($url);
                }
            }
            Log::warning('E-Book SSLCommerz checkout URL unavailable or untrusted', ['transaction_id'=>$trx]);
        } catch (Throwable $e) {
            Log::error('E-Book payment start error', ['transaction_id'=>$trx,'message'=>$e->getMessage()]);
        }
        DB::table('lebd_ebook_payment_orders')->where('transaction_id',$trx)->where('status','Pending')
            ->update(['status'=>'Failed','updated_at'=>now()]);
        return back()->with('error','Payment Gateway চালু করা যায়নি। SSLCommerz সেটিং পরীক্ষা করুন।');
    }

    public function callback(Request $request, string $event)
    {
        abort_unless(in_array($event,['success','fail','cancel','ipn'],true),404);
        $trx=(string)$request->input('tran_id','');
        if(!preg_match('/^EBK[A-Za-z0-9]{20}$/',$trx)) return response('Invalid transaction',400);
        $order=DB::table('lebd_ebook_payment_orders')->where('transaction_id',$trx)->first();
        if(!$order) return response('Order not found',404);
        if($event==='fail'||$event==='cancel') {
            DB::table('lebd_ebook_payment_orders')->where('id',$order->id)->where('status','Pending')
                ->update(['status'=>$event==='fail'?'Failed':'Canceled','updated_at'=>now()]);
            return $this->result($order->id,$event==='fail'?'failed':'canceled',$event!=='ipn');
        }
        if($order->status==='Complete') return $this->result($order->id,'success',$event!=='ipn');
        if($order->status!=='Pending') return $this->result($order->id,'failed',$event!=='ipn');
        $valId=(string)$request->input('val_id','');
        $amount=$request->input('amount');
        if($valId==='' || !is_numeric($amount)
            || abs((float)$amount-(float)$order->amount)>0.01
            || strtoupper((string)$request->input('currency'))!==$order->currency){
            Log::warning('E-Book callback did not match order',['transaction_id'=>$trx]);
            return $this->result($order->id,'pending',$event!=='ipn');
        }
        try {
            $valid=(new EBookSslCommerz())->orderValidate($request->all(),
                $order->transaction_id,(float)$order->amount,$order->currency);
        } catch(Throwable $e) {
            Log::error('E-Book validation failed',['transaction_id'=>$trx,'message'=>$e->getMessage()]);
            $valid=false;
        }
        if($valid!==true) return $this->result($order->id,'pending',$event!=='ipn');
        DB::transaction(function() use($order,$valId){
            $locked=DB::table('lebd_ebook_payment_orders')->where('id',$order->id)->lockForUpdate()->first();
            if(!$locked || $locked->status!=='Pending') return;
            $now=Carbon::now('Asia/Dhaka');
            $existing=DB::table('lebd_ebook_entitlements')
                ->where('user_id',$locked->student_id)->where('category_id',$locked->category_id)
                ->where('status','Active')->where('expires_at','>',$now)->lockForUpdate()
                ->orderByDesc('expires_at')->first();
            $start=$existing?Carbon::parse($existing->expires_at,'Asia/Dhaka'):$now->copy();
            $end=$start->copy();
            switch($locked->duration_unit){
                case 'days': $end->addDays((int)$locked->duration_value);break;
                case 'months': $end->addMonthsNoOverflow((int)$locked->duration_value);break;
                case 'years': $end->addYearsNoOverflow((int)$locked->duration_value);break;
                default: throw new \RuntimeException('Invalid package duration');
            }
            if($existing){
                DB::table('lebd_ebook_entitlements')->where('id',$existing->id)->update([
                    'expires_at'=>$end,'package_id'=>$locked->package_id,'updated_at'=>now(),
                ]);
            }else{
                DB::table('lebd_ebook_entitlements')->insert([
                    'user_id'=>$locked->student_id,'category_id'=>$locked->category_id,
                    'package_id'=>$locked->package_id,'status'=>'Active',
                    'starts_at'=>$now,'expires_at'=>$end,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            DB::table('lebd_ebook_payment_orders')->where('id',$locked->id)->update([
                'status'=>'Complete','gateway_validation_id'=>mb_substr($valId,0,100),
                'paid_at'=>now(),'updated_at'=>now(),
            ]);
        });
        return $this->result($order->id,'success',$event!=='ipn');
    }

    private function result(int $orderId,string $state,bool $browser)
    {
        if(!$browser) return response('OK',200);
        $order=DB::table('lebd_ebook_payment_orders')->find($orderId);
        return view('ebook.payment-result',compact('order','state'));
    }
}
