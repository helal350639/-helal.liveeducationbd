<?php
namespace App\Http\Controllers\Students\PremiumClass;
use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
class PremiumPaymentController extends Controller {
 private function credentials():array {
  return [(string)config('lebd_premium_ssl.store_id',''),(string)config('lebd_premium_ssl.store_password',''),(bool)config('lebd_premium_ssl.sandbox',true)];
 }
 private function host(bool $sandbox):string{return $sandbox?'https://sandbox.sslcommerz.com':'https://securepay.sslcommerz.com';}
 public function review(Request $r,PremiumClassPackage $package){
  abort_unless($package->is_active,404);
  $main=(float)$package->price;
  $final=(float)($package->final_price??$package->price);
  $discount=max(0,$main-$final);
  abort_if($final<1 || $final>$main,422,'Package price is invalid. Please contact support.');
  // Always use the logged-in student's current wallet minus pending withdrawals.
  // This is display-only: the ReferralPurchaseController rechecks funds under row lock.
  $referralBalance = null;
  if (Auth::guard('student')->check()) {
   $student = Auth::guard('student')->user();
   $pending = DB::table('withdraw_requests')
    ->where('student_id', $student->id)->where('status', 'pending')->sum('amount');
   $referralBalance = max(0, round((float) $student->referral_balance - (float) $pending, 2));
  }
  if (Auth::guard('student')->check() && Schema::hasTable('lebd_premium_class_orders')) {
   $activeOrder = DB::table('lebd_premium_class_orders')->where('student_id', Auth::guard('student')->id())
      ->where('package_id', $package->id)->where('status', 'paid')
      ->where('expires_at', '>', now())->orderByDesc('expires_at')->first();
   if ($activeOrder) {
     return redirect()->route('premium.payment.purchased.videos', ['order' => $activeOrder->id])
       ->with('info', 'This package is already active in your account.');
   }
  }
  return view('premium-class.checkout-review',compact('package','main','final','discount','referralBalance'));
 }
 public function checkout(Request $r,PremiumClassPackage $package){
  $student=Auth::guard('student')->user();abort_unless($student,401);
  if(!Schema::hasTable('lebd_premium_class_orders')){Log::error('Premium checkout: orders table missing');return redirect()->route('premium.payment.review',$package)->withErrors(['payment'=>'Premium payment database setup সম্পূর্ণ হয়নি। অনুগ্রহ করে অ্যাডমিনের সঙ্গে যোগাযোগ করুন (Orders table missing)।']);}
  abort_unless($package->is_active,404);
  // An already-paid and unexpired package must not open another gateway checkout.
  // Recheck on the server: disabled browser buttons are not payment protection.
  $activeOrder = DB::table('lebd_premium_class_orders')
    ->where('student_id', $student->id)
    ->where('package_id', $package->id)
    ->where('status', 'paid')
    ->where('expires_at', '>', now())
    ->orderByDesc('expires_at')->first();
  if ($activeOrder) {
    return redirect()->route('premium.payment.purchased.videos', ['order' => $activeOrder->id])
      ->with('info', 'This package is already active in your account.');
  }
  [$store,$secret,$sandbox]=$this->credentials();
  if($store===''||$secret===''){Log::error('Premium checkout: store credentials missing');return redirect()->route('premium.payment.review',$package)->withErrors(['payment'=>'SSLCommerz Payment এখনো Configuration সম্পূর্ণ হয়নি। অ্যাডমিনকে Store ID/Password সেট করতে হবে।']);}
  $amount=round((float)($package->final_price??$package->price),2);
  abort_if($amount<1,422,'Zero-price package needs a separate grant workflow.');
  $id='LEBDP'.strtoupper(Str::random(20));
  $days=max(1,(int)$package->validity_days);
  $row=['student_id'=>$student->id,'package_id'=>$package->id,'tran_id'=>$id,'amount'=>$amount,'currency'=>'BDT','status'=>'pending',
   'package_type'=>$package->package_type,'department_categories'=>json_encode($package->department_categories?:[]),
   'non_department_categories'=>json_encode($package->non_department_categories?:[]),'validity_days'=>$days,'created_at'=>now(),'updated_at'=>now()];
  DB::table('lebd_premium_class_orders')->insert($row);
  $callback=['success_url'=>route('premium.payment.success'),'fail_url'=>route('premium.payment.fail'),'cancel_url'=>route('premium.payment.cancel'),'ipn_url'=>route('premium.payment.ipn')];
  $payload=array_merge($callback,['store_id'=>$store,'store_passwd'=>$secret,'total_amount'=>number_format($amount,2,'.',''),
   'currency'=>'BDT','tran_id'=>$id,'product_category'=>'Education','product_name'=>substr($package->name,0,100),
   'product_profile'=>'non-physical-goods','shipping_method'=>'NO','cus_name'=>(string)($student->name?:'Student'),
   'cus_email'=>(string)($student->email?:'student@example.com'),'cus_phone'=>(string)($student->phone?:'01000000000'),
   'cus_add1'=>'Bangladesh','cus_city'=>'Dhaka','cus_country'=>'Bangladesh','value_a'=>(string)$student->id,'value_b'=>(string)$package->id]);
  try{$response=Http::asForm()->timeout(25)->post($this->host($sandbox).'/gwprocess/v4/api.php',$payload);
   $url=(string)$response->json('GatewayPageURL');
   if(!$response->successful()||$response->json('status')!=='SUCCESS'||!str_starts_with($url,'https://'))throw new \RuntimeException('Session failed');
   return redirect()->away($url);
  }catch(\Throwable $e){Log::warning('Premium SSLCommerz init failed',['transaction'=>$id,'reason'=>$e->getMessage()]);
   return redirect()->route('premium.payment.review',$package)->withErrors(['payment'=>'SSLCommerz Checkout শুরু করা যায়নি। Gateway/Network অথবা Credentials পরীক্ষা করুন। আবার চেষ্টা করুন।']);}
 }
 private function callback(Request $r,string $event){
  $id=(string)$r->input('tran_id','');
  if(!preg_match('/^LEBDP[A-Z0-9]{20}$/',$id))return response('Invalid transaction',400);
  $order=DB::table('lebd_premium_class_orders')->where('tran_id',$id)->first();
  if(!$order)return response('Unknown transaction',404);
  if($event==='success'||$event==='ipn'){
   $val=(string)$r->input('val_id','');
   if($val==='')return response('Missing validation ID',400);
   [$store,$secret,$sandbox]=$this->credentials();
   if($store===''||$secret==='')return response('Gateway not configured',503);
   try{$response=Http::timeout(25)->get($this->host($sandbox).'/validator/api/validationserverAPI.php',[
       'val_id'=>$val,'store_id'=>$store,'store_passwd'=>$secret,'v'=>1,'format'=>'json']);
    $data=$response->json();if(!$response->successful()||!is_array($data))throw new \RuntimeException('Validation failed');
    $valid=in_array($data['status']??null,['VALID','VALIDATED'],true)
      && hash_equals($id,(string)($data['tran_id']??''))
      && abs((float)($data['amount']??-999)-(float)$order->amount)<0.01
      && strtoupper((string)($data['currency']??''))==='BDT'
      && (int)($data['risk_level']??0)===0;
    if(!$valid)return response('Payment not verified',422);
    DB::transaction(function()use($id,$val){
      $locked=DB::table('lebd_premium_class_orders')->where('tran_id',$id)->lockForUpdate()->first();
      if(!$locked || $locked->status==='paid')return;
      if($locked->status!=='pending')return;
      DB::table('lebd_premium_class_orders')->where('id',$locked->id)->update([
       'status'=>'paid','val_id'=>$val,'paid_at'=>now(),
       'expires_at'=>now()->addDays(max(1,(int)$locked->validity_days)), 'updated_at'=>now()]);
    });
    if($event==='ipn')return response('OK',200);
    return redirect()->route('premium.payment.purchased.success', ['order' => $order->id]);
   }catch(\Throwable $e){Log::warning('Premium payment verification failed',['transaction'=>$id]);return response('Unable to verify payment',502);}
  }
  DB::table('lebd_premium_class_orders')->where('tran_id',$id)->where('status','pending')->update(['status'=>$event,'updated_at'=>now()]);
  return redirect()->route('premium.recordings.packages')->withErrors(['payment'=>'Payment '.($event==='cancel'?'cancelled':'failed').'.']);
 }
 public function success(Request $r){return $this->callback($r,'success');}
 public function ipn(Request $r){return $this->callback($r,'ipn');}
 public function fail(Request $r){return $this->callback($r,'failed');}
 public function cancel(Request $r){return $this->callback($r,'cancelled');}
}
