<?php
namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Exam Section only. Separate ledger, never rewrites legacy course/payment orders. */
class ExamSectionCheckoutController extends Controller
{
    private function student()
    {
        return Auth::guard('student')->user();
    }
    private function package(int $id)
    {
        return DB::table('lebd_exam_section_packages')->where('id', $id)->where('status', 'Active')->firstOrFail();
    }
    /** A student may hold only one CURRENT access grant for the same package. */
    private function activeAccess(int $studentId, int $packageId): ?object
    {
        return DB::table('lebd_exam_section_student_package_access')
            ->where('student_id', $studentId)->where('package_id', $packageId)
            ->where('status', 'active')
            ->where('starts_at', '<=', now('Asia/Dhaka')->format('Y-m-d H:i:s'))
            ->where('ends_at', '>', now('Asia/Dhaka')->format('Y-m-d H:i:s'))
            ->orderByDesc('ends_at')->first();
    }
    private function alreadyOwned(int $studentId, int $packageId)
    {
        $access = $this->activeAccess($studentId, $packageId);
        if (!$access) return null;
        return redirect()->route('student.exam-section.packages')
            ->with('success', 'আপনি ইতিমধ্যে এই প্যাকেজ কিনেছেন। মেয়াদ শেষ হলে আবার কিনতে পারবেন। মেয়াদ শেষ: '.Carbon::parse($access->ends_at)->format('d M Y, h:i A'));
    }
    private function amount(object $package): int
    {
        // integer paisa, never trust a submitted or browser-rendered amount.
        return (int) round(((float) $package->final_price) * 100);
    }
    private function durationEnds(object $package, Carbon $from): Carbon
    {
        $units = strtolower((string) $package->duration_unit);
        $qty = max(1, (int) $package->duration);
        if ($units === 'days') return $from->copy()->addDays($qty);
        if ($units === 'weeks') return $from->copy()->addWeeks($qty);
        if ($units === 'years') return $from->copy()->addYearsNoOverflow($qty);
        return $from->copy()->addMonthsNoOverflow($qty);
    }
    private function pendingWithdraw(int $studentId): int
    {
        return (int) round((float) DB::table('withdraw_requests')->where('student_id', $studentId)
            ->where('status', 'pending')->sum('amount') * 100);
    }
    private function available(object $student): int
    {
        return max(0, (int) round(((float) $student->referral_balance) * 100) - $this->pendingWithdraw((int)$student->id));
    }
    public function review(int $package)
    {
        $student = $this->student(); abort_unless($student, 401);
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $scopes = DB::table('lebd_exam_section_package_scopes as s')
            ->leftJoin('lebd_exam_section_categories as c', 'c.id', '=', 's.category_id')
            ->leftJoin('lebd_exam_section_subcategories as sub', 'sub.id', '=', 's.subcategory_id')
            ->where('s.package_id', $plan->id)->select('c.name as category_name', 'sub.name as subcategory_name')->get();
        $available = $this->available($student) / 100;
        return view('student.exam-section.package-review', compact('plan','scopes','student','available'));
    }
    /** Safe refresh/bookmark of the checkout confirmation URL. Never initiates payment. */
    public function checkoutResume(Request $request, int $package)
    {
        $student = $this->student();
        abort_unless($student, 401);
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $pending = $request->session()->get('exam_section_checkout');
        if (!is_array($pending)
            || (int) ($pending['student_id'] ?? 0) !== (int) $student->id
            || (int) ($pending['package_id'] ?? 0) !== $package
            || !in_array(($pending['method'] ?? ''), ['wallet', 'sslcommerz'], true)
            || empty($pending['token'])
            || (time() - (int) ($pending['created_at'] ?? 0)) > 900
        ) {
            return redirect()->route('student.exam-section.package.review', $package)
                ->withErrors(['payment' => 'Checkout session পাওয়া যায়নি বা মেয়াদ শেষ। পেমেন্ট পদ্ধতি আবার নির্বাচন করুন।']);
        }
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $method = $pending['method'];
        $token = $pending['token'];
        $available = $this->available($student) / 100;
        return view('student.exam-section.package-checkout-confirm', compact('plan', 'method', 'available', 'token'));
    }

    /** Review selection only. NO debit and NO external payment happens here. */
    public function checkoutPage(Request $request, int $package)
    {
        $student = $this->student(); abort_unless($student, 401);
        $data = $request->validate(['method' => 'required|in:wallet,sslcommerz', 'terms' => 'accepted']);
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $amount = $this->amount($plan);
        $available = $this->available($student) / 100;
        if ($amount < 100) return redirect()->route('student.exam-section.package.review',$package)
            ->withErrors(['payment'=>'Package price is invalid.']);
        if ($data['method'] === 'wallet' && (int) round($available * 100) < $amount) {
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'Available Referral Balance যথেষ্ট নয়।']);
        }
        $method = $data['method'];
        $token = Str::random(40);
        // Persist a short-lived, one-time checkout intent independently of browser session cookies.
        DB::table('lebd_exam_section_checkout_intents')->insert([
            'student_id'=>(int)$student->id, 'package_id'=>(int)$plan->id,
            'token_hash'=>hash('sha256',$token), 'method'=>$method,
            'status'=>'pending', 'expires_at'=>now()->addMinutes(15),
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        $request->session()->put('exam_section_checkout', [
            'package_id'=>(int)$plan->id,'method'=>$method,'token'=>$token,
            'student_id'=>(int)$student->id,'created_at'=>time(),
        ]);
        return view('student.exam-section.package-checkout-confirm',compact('plan','method','available','token'));
    }
    private function createOrder(object $plan, int $studentId, string $method): string
    {
        $id = 'LEBDE'.strtoupper(Str::random(20));
        DB::table('lebd_exam_section_package_orders')->insert([
            'student_id'=>$studentId, 'package_id'=>$plan->id, 'transaction_id'=>$id,
            'amount'=>number_format($this->amount($plan)/100,2,'.',''), 'method'=>$method,
            'status'=>'pending', 'created_at'=>now(), 'updated_at'=>now(),
        ]);
        return $id;
    }
    private function grant(object $order, string $reference): void
    {
        $plan = DB::table('lebd_exam_section_packages')->where('id',$order->package_id)->first();
        if (!$plan) throw new \RuntimeException('Package missing');
        // Enforced again while the owning student row is locked by the caller.
        if ($this->activeAccess((int)$order->student_id,(int)$order->package_id)) return;
        $now=Carbon::now('Asia/Dhaka');
        DB::table('lebd_exam_section_student_package_access')->insert([
            'student_id'=>$order->student_id, 'package_id'=>$order->package_id,
            'status'=>'active', 'starts_at'=>$now->format('Y-m-d H:i:s'),
            'ends_at'=>$this->durationEnds($plan,$now)->format('Y-m-d H:i:s'),
            'payment_reference'=>$reference, 'created_at'=>now(),
        ]);
    }
    public function pay(Request $request, int $package)
    {
        $student = $this->student(); abort_unless($student, 401);
        $request->validate(['method'=>'required|in:wallet,sslcommerz']);
        // SSLCommerz only: recover a missing hidden form token from the checkout
        // session created by checkoutPage(). Wallet behaviour remains unchanged.
        // A recovered token is never sufficient by itself: the matching pending
        // intent is still checked below against student, package, method and expiry.
        if ($request->input('method') === 'sslcommerz' && !$request->filled('checkout_token')) {
            $pending = $request->session()->get('exam_section_checkout');
            if (is_array($pending)
                && (int)($pending['student_id'] ?? 0) === (int)$student->id
                && (int)($pending['package_id'] ?? 0) === $package
                && ($pending['method'] ?? '') === 'sslcommerz'
                && is_string($pending['token'] ?? null)
                && $pending['token'] !== ''
                && (time() - (int)($pending['created_at'] ?? 0)) >= 0
                && (time() - (int)($pending['created_at'] ?? 0)) <= 900) {
                $request->merge(['checkout_token' => $pending['token']]);
            }
        }
        $request->validate(['checkout_token'=>'required|string']);
        // Never accept a submitted or recovered token alone: validate and
        // consume its server-held pending intent before any order is created.
        $checkoutHash = hash('sha256',(string)$request->input('checkout_token'));
        $intent = DB::table('lebd_exam_section_checkout_intents')
            ->where('token_hash',$checkoutHash)
            ->where('student_id',(int)$student->id)
            ->where('package_id',$package)
            ->where('method',(string)$request->input('method'))
            ->where('status','pending')
            ->where('expires_at','>',now())
            ->first();
        if (!$intent) {
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'Checkout নির্বাচনটি পাওয়া যায়নি, মেয়াদ শেষ হয়েছে অথবা আগেই ব্যবহার হয়েছে। আবার চেষ্টা করুন।']);
        }
        $claimed = DB::table('lebd_exam_section_checkout_intents')
            ->where('id',$intent->id)->where('status','pending')
            ->where('expires_at','>',now())
            ->update(['status'=>'processing','updated_at'=>now()]);
        if ($claimed !== 1) {
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'এই Checkout আগে থেকেই Process করা হয়েছে।']);
        }
        $request->session()->forget('exam_section_checkout');
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $price = $this->amount($plan);
        if ($price < 100) return back()->withErrors(['payment'=>'Package price must be at least ৳1.00.']);
        if ($request->input('method') === 'wallet') {
            try {
                DB::transaction(function () use ($student, $plan, $price) {
                    // A locked student row serializes concurrent wallet purchases.
                    $locked = DB::table('students')->where('id', $student->id)->lockForUpdate()->first();
                    if (!$locked) throw new \DomainException('Student not found');
                    if ($this->activeAccess((int)$student->id,(int)$plan->id)) throw new \DomainException('এই প্যাকেজটি ইতিমধ্যে সক্রিয় আছে। মেয়াদ শেষ হলে কিনুন।');
                    if ($this->available($locked) < $price) throw new \DomainException('Available Referral Balance যথেষ্ট নয়।');
                    $reference=$this->createOrder($plan,(int)$student->id,'wallet');
                    $old=(int)round((float)$locked->referral_balance*100);
                    DB::table('students')->where('id',$student->id)->update(['referral_balance'=>number_format(($old-$price)/100,2,'.',''),'updated_at'=>now()]);
                    DB::table('lebd_exam_section_wallet_debits')->insert([
                        'student_id'=>$student->id,'order_transaction_id'=>$reference,
                        'amount'=>number_format($price/100,2,'.',''),'balance_before'=>number_format($old/100,2,'.',''),
                        'balance_after'=>number_format(($old-$price)/100,2,'.',''),'created_at'=>now(),
                    ]);
                    $order=DB::table('lebd_exam_section_package_orders')->where('transaction_id',$reference)->lockForUpdate()->first();
                    $this->grant($order,$reference);
                    DB::table('lebd_exam_section_package_orders')->where('transaction_id',$reference)->update(['status'=>'paid','paid_at'=>now(),'updated_at'=>now()]);
                },3);
            } catch (\DomainException $e) { DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]); return redirect()->route('student.exam-section.package.review',$package)->withErrors(['payment'=>$e->getMessage()]); } catch (\Throwable $e) { DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]); Log::error('Exam package wallet transaction failed',['student_id'=>$student->id, 'package_id'=>$package]); return redirect()->route('student.exam-section.package.review',$package)->withErrors(['payment'=>'পেমেন্ট সম্পন্ন হয়নি। আবার চেষ্টা করুন।']); }
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'completed','updated_at'=>now()]);
            return redirect()->route('student.exam-section.packages')->with('success','Referral Balance থেকে পেমেন্ট সফল। প্রিমিয়াম পরীক্ষার অ্যাক্সেস চালু হয়েছে।');
        }
        [$store,$secret,$sandbox]=$this->credentials();
        if ($store===''||$secret==='') {
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]);
            return redirect()->route('student.exam-section.package.review',$package)->withErrors(['payment'=>'SSLCommerz Credentials এখনো Configure করা হয়নি।']);
        }
        $reference=$this->createOrder($plan,(int)$student->id,'sslcommerz');
        $payload=[
            'store_id'=>$store,'store_passwd'=>$secret,'total_amount'=>number_format($price/100,2,'.',''),
            'currency'=>'BDT','tran_id'=>$reference,'success_url'=>route('student.exam-section.payment.success'),
            'fail_url'=>route('student.exam-section.payment.fail'),'cancel_url'=>route('student.exam-section.payment.cancel'),
            'ipn_url'=>route('student.exam-section.payment.ipn'),
            'product_name'=>mb_substr($plan->name,0,100),'product_category'=>'Education','product_profile'=>'non-physical-goods',
            'shipping_method'=>'NO','cus_name'=>(string)$student->name,'cus_email'=>(string)($student->email ?: 'customer@example.com'),
            'cus_phone'=>(string)($student->contact ?: '01000000000'),'cus_add1'=>'Bangladesh','cus_city'=>'Dhaka','cus_country'=>'Bangladesh',
            'value_a'=>(string)$student->id,'value_b'=>(string)$plan->id,
        ];
        try {
            $res=Http::asForm()->timeout(25)->post($this->host($sandbox).'/gwprocess/v4/api.php',$payload);
            $url=(string)$res->json('GatewayPageURL');
            if (!$res->successful() || $res->json('status')!=='SUCCESS' || !str_starts_with($url,'https://')) throw new \RuntimeException('Session initialization failed');
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'completed','updated_at'=>now()]);
            return redirect()->away($url);
        } catch (\Throwable $e) {
            DB::table('lebd_exam_section_package_orders')->where('transaction_id',$reference)->where('status','pending')->update(['status'=>'failed','updated_at'=>now()]);
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]);
            Log::warning('Exam Section SSL session failure',['order'=>$reference,'error'=>$e->getMessage()]);
            return back()->withErrors(['payment'=>'SSLCommerz Checkout খুলতে পারেনি। Credentials/Network যাচাই করুন।']);
        }
    }
    private function credentials(): array
    {
        // Reuse the existing SSLCZ_* .env credentials without storing secrets in source code.
        // Exam-specific credentials, when configured, take precedence.
        $store = (string) config('services.lebd_exam_ssl.store_id', '');
        $secret = (string) config('services.lebd_exam_ssl.store_password', '');
        if ($store === '') {
            $store = (string) env('LEBD_EXAM_SSL_STORE_ID', env('SSLCZ_STORE_ID', ''));
        }
        if ($secret === '') {
            $secret = (string) env('LEBD_EXAM_SSL_STORE_PASSWORD', env('SSLCZ_STORE_PASSWORD', ''));
        }
        // Live credentials supplied by user: production is the default ONLY when
        // no sandbox setting already exists; explicit configuration always wins.
        $sandboxValue = config('services.lebd_exam_ssl.sandbox', env('LEBD_EXAM_SSL_SANDBOX', false));
        return [$store, $secret, filter_var($sandboxValue, FILTER_VALIDATE_BOOLEAN)];
    }
    private function host(bool $sandbox):string {return $sandbox?'https://sandbox.sslcommerz.com':'https://securepay.sslcommerz.com';}
    private function callback(Request $request,string $event)
    {
        $id=(string)$request->input('tran_id','');
        if (!preg_match('/^LEBDE[A-Z0-9]{20}$/',$id)) return response('Invalid transaction',400);
        $order=DB::table('lebd_exam_section_package_orders')->where('transaction_id',$id)->first();
        if (!$order || $order->method!=='sslcommerz') return response('Unknown transaction',404);
        if ($event==='success' || $event==='ipn') {
            $val=(string)$request->input('val_id',''); if ($val==='') return response('Validation ID required',400);
            [$store,$secret,$sandbox]=$this->credentials(); if (!$store || !$secret) return response('Gateway not configured',503);
            try {
                $res=Http::timeout(25)->get($this->host($sandbox).'/validator/api/validationserverAPI.php',[
                    'val_id'=>$val,'store_id'=>$store,'store_passwd'=>$secret,'v'=>1,'format'=>'json']);
                $v=$res->json();
                $valid=$res->successful() && is_array($v) && in_array($v['status']??'',['VALID','VALIDATED'],true)
                    && hash_equals($id,(string)($v['tran_id']??''))
                    && abs((float)($v['amount']??-999) - (float)$order->amount) < 0.01
                    && strtoupper((string)($v['currency']??''))==='BDT' && (int)($v['risk_level']??0)===0;
                if (!$valid) return response('Unverified payment',422);
                DB::transaction(function () use ($id,$val) {
                    $locked=DB::table('lebd_exam_section_package_orders')->where('transaction_id',$id)->lockForUpdate()->first();
                    if (!$locked || $locked->status !== 'pending') return;
                    // Serialize access grants with a locked student record, including wallet purchases.
                    DB::table('students')->where('id',$locked->student_id)->lockForUpdate()->first();
                    $this->grant($locked,$id);
                    DB::table('lebd_exam_section_package_orders')->where('id',$locked->id)->update(['status'=>'paid','gateway_validation_id'=>$val,'paid_at'=>now(),'updated_at'=>now()]);
                });
            } catch (\Throwable $e) {Log::error('Exam Section payment validation failure',['order'=>$id]);return response('Verification error',502);}
            if ($event==='ipn') return response('OK',200);
            return redirect()->route('student.exam-section.packages')->with('success','✅ পেমেন্ট সফল! SSLCommerz পেমেন্ট যাচাই সম্পন্ন হয়েছে। আপনার Exam Package-এর Premium Access চালু হয়েছে।');
        }
        DB::table('lebd_exam_section_package_orders')->where('transaction_id',$id)->where('status','pending')->update(['status'=>$event,'updated_at'=>now()]);
        return redirect()->route('student.exam-section.packages')->withErrors(['payment'=>'Payment '.($event==='failed'?'failed':'cancelled')]);
    }
    public function success(Request $r) {return $this->callback($r,'success');}
    public function ipn(Request $r) {return $this->callback($r,'ipn');}
    public function fail(Request $r) {return $this->callback($r,'failed');}
    public function cancel(Request $r) {return $this->callback($r,'cancelled');}
}
