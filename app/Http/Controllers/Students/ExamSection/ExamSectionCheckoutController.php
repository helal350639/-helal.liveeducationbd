<?php
namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\ExamSection\ExamReferralSettlement;

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
        return redirect()->route('student.exam-section.categories')
            ->with('success', 'আপনি ইতিমধ্যে এই প্যাকেজ কিনেছেন। মেয়াদ শেষ হলে আবার কিনতে পারবেন। মেয়াদ শেষ: '.Carbon::parse($access->ends_at)->format('d M Y, h:i A'));
    }
    private function amount(object $package): int
    {
        // Legacy normal-price helper; referred checkout uses server-verified quote.
        return (int) round(((float) $package->final_price) * 100);
    }
    private function referral(): ExamReferralSettlement
    {
        return app(ExamReferralSettlement::class);
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
        if (request()->has('ref')) {
            $code = trim((string)request()->query('ref'));
            // Referral validation precedes session persistence; a bad link fails closed.
            $this->referral()->quote($plan,(int)$student->id,$code);
            request()->session()->put(ExamReferralSettlement::SESSION,[
                'package_id'=>(int)$plan->id,'buyer_id'=>(int)$student->id,
                'code'=>$code,'at'=>time(),
            ]);
        } else {
            // Ordinary catalogue visits must always show the ordinary price.
            request()->session()->forget(ExamReferralSettlement::SESSION);
        }
        $quote=$this->referral()->sessionQuote($plan,(int)$student->id,request()->session());
        $scopes = DB::table('lebd_exam_section_package_scopes as s')
            ->leftJoin('lebd_exam_section_categories as c', 'c.id', '=', 's.category_id')
            ->leftJoin('lebd_exam_section_subcategories as sub', 'sub.id', '=', 's.subcategory_id')
            ->where('s.package_id', $plan->id)->select('c.name as category_name', 'sub.name as subcategory_name')->get();
        $available = $this->available($student) / 100;
        return view('student.exam-section.package-review', compact('plan','scopes','student','available','quote'));
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
                ->with('checkout_notice', 'আগের Checkout session পাওয়া যায়নি বা মেয়াদ শেষ হয়েছে। নতুন করে পেমেন্ট পদ্ধতি নির্বাচন করুন।');
        }
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $method = $pending['method'];
        $token = $pending['token'];
        $available = $this->available($student) / 100;
        $intent = DB::table('lebd_exam_section_checkout_intents')
            ->where('token_hash', hash('sha256', (string)$token))
            ->where('student_id', (int)$student->id)->where('package_id', $package)
            ->where('method', $method)->where('status', 'pending')
            ->where('expires_at', '>', now())->first();
        $quote = $intent && !empty($intent->quote_payload)
            ? json_decode($intent->quote_payload, true)
            : null;
        if (!is_array($quote) || (int)($pending['quote_cents']??-1)!==(int)($quote['payable']??-2)) {
            return redirect()->route('student.exam-section.package.review',$package)
                ->with('checkout_notice', 'আগের Checkout তথ্য পাওয়া যায়নি বা মেয়াদ শেষ হয়েছে। নতুন করে পেমেন্ট পদ্ধতি নির্বাচন করুন।');
        }
        return view('student.exam-section.package-checkout-confirm', compact('plan', 'method', 'available', 'token','quote'));
    }

    /** Review selection only. NO debit and NO external payment happens here. */
    public function checkoutPage(Request $request, int $package)
    {
        $student = $this->student(); abort_unless($student, 401);
        $data = $request->validate(['method' => 'required|in:wallet,sslcommerz', 'terms' => 'accepted']);
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $quote=$this->referral()->sessionQuote($plan,(int)$student->id,$request->session());
        $amount = $quote['payable'];
        $available = $this->available($student) / 100;
        if ($amount < 100) return redirect()->route('student.exam-section.package.review',$package)
            ->withErrors(['payment'=>'Package price is invalid.']);
        if ($data['method'] === 'wallet' && (int) round($available * 100) < $amount) {
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'Available Referral Balance যথেষ্ট নয়।']);
        }
        $method = $data['method'];
        $token = Str::random(40);
        // Keep the verified quote with the server-side intent. The pay step must
        // not depend on a browser session surviving between Preview and Confirm.
        if (!Schema::hasColumn('lebd_exam_section_checkout_intents', 'quote_payload')) {
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'Checkout update SQL install করুন, তারপর আবার চেষ্টা করুন।']);
        }
        DB::table('lebd_exam_section_checkout_intents')->insert([
            'student_id'=>(int)$student->id, 'package_id'=>(int)$plan->id,
            'token_hash'=>hash('sha256',$token), 'method'=>$method,
            'quote_payload'=>json_encode($quote, JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),
            'status'=>'pending', 'expires_at'=>now()->addMinutes(15),
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        $request->session()->put('exam_section_checkout', [
            'package_id'=>(int)$plan->id,'method'=>$method,'token'=>$token,
            'student_id'=>(int)$student->id,'created_at'=>time(),'quote_cents'=>$amount,
        ]);
        return view('student.exam-section.package-checkout-confirm',compact('plan','method','available','token','quote'));
    }
    private function createOrder(object $plan, int $studentId, string $method, array $quote): string
    {
        $id = 'LEBDE'.strtoupper(Str::random(20));
        DB::table('lebd_exam_section_package_orders')->insert([
            'student_id'=>$studentId, 'package_id'=>$plan->id, 'transaction_id'=>$id,
            'amount'=>number_format($quote['payable']/100,2,'.',''), 'method'=>$method,
            'status'=>'pending', 'created_at'=>now(), 'updated_at'=>now(),
        ]);
        $this->referral()->attach($id,$studentId,(int)$plan->id,$quote);
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
        $plan = $this->package($package);
        if ($owned = $this->alreadyOwned((int)$student->id, (int)$plan->id)) return $owned;
        $quote = !empty($intent->quote_payload) ? json_decode($intent->quote_payload, true) : null;
        $currentBase = (int) round((float)$plan->final_price * 100);
        $quoteValid = is_array($quote)
            && isset($quote['base'], $quote['discount'], $quote['bonus'], $quote['payable'])
            && (int)$quote['base'] === $currentBase
            && (int)$quote['discount'] >= 0
            && (int)$quote['bonus'] >= 0
            && (int)$quote['payable'] === ((int)$quote['base'] - (int)$quote['discount'])
            && (int)$quote['payable'] >= 100;
        if (!$quoteValid) {
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]);
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'Checkout মূল্য যাচাই করা যায়নি। নতুন করে Payment Preview করুন।']);
        }
        $request->session()->forget('exam_section_checkout');
        $request->session()->forget(ExamReferralSettlement::SESSION);
        $price = $quote['payable'];
        if ($price < 100) return back()->withErrors(['payment'=>'Package price must be at least ৳1.00.']);
        if ($request->input('method') === 'wallet') {
            try {
                DB::transaction(function () use ($student, $plan, $price, $quote) {
                    // A locked student row serializes concurrent wallet purchases.
                    $locked = DB::table('students')->where('id', $student->id)->lockForUpdate()->first();
                    if (!$locked) throw new \DomainException('Student not found');
                    if ($this->activeAccess((int)$student->id,(int)$plan->id)) throw new \DomainException('এই প্যাকেজটি ইতিমধ্যে সক্রিয় আছে। মেয়াদ শেষ হলে কিনুন।');
                    if ($this->available($locked) < $price) throw new \DomainException('Available Referral Balance যথেষ্ট নয়।');
                    $reference=$this->createOrder($plan,(int)$student->id,'wallet',$quote);
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
                    $this->referral()->credit($reference);
                },3);
            } catch (\DomainException $e) { DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]); return redirect()->route('student.exam-section.package.review',$package)->withErrors(['payment'=>$e->getMessage()]); } catch (\Throwable $e) { DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]); Log::error('Exam package wallet transaction failed',['student_id'=>$student->id, 'package_id'=>$package]); return redirect()->route('student.exam-section.package.review',$package)->withErrors(['payment'=>'পেমেন্ট সম্পন্ন হয়নি। আবার চেষ্টা করুন।']); }
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'completed','updated_at'=>now()]);
            return redirect()->route('student.exam-section.categories')->with('success','অভিনন্দন! আপনার প্যাকেজ কেনা সফল হয়েছে। Premium Exam Access চালু হয়েছে। এখন Exam Section থেকে পরীক্ষা দিতে পারবেন।');
        }
        [$store,$secret,$sandbox]=$this->credentials();
        if ($store===''||$secret==='') {
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]);
            return redirect()->route('student.exam-section.package.review',$package)->withErrors(['payment'=>'SSLCommerz Credentials এখনো Configure করা হয়নি।']);
        }
        $reference=DB::transaction(fn()=>$this->createOrder($plan,(int)$student->id,'sslcommerz',$quote));
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
            $res=Http::asForm()->acceptJson()->connectTimeout(12)->timeout(35)
                ->post($this->host($sandbox).'/gwprocess/v4/api.php',$payload);
            $reply=$res->json();
            $url=is_array($reply) ? ($reply['GatewayPageURL'] ?? null) : null;
            $host=is_string($url) ? strtolower((string)parse_url($url, PHP_URL_HOST)) : '';
            $allowedHost=$host==='sslcommerz.com' || str_ends_with($host,'.sslcommerz.com');
            $safeGatewayUrl=is_string($url)
                && filter_var($url, FILTER_VALIDATE_URL)
                && strtolower((string)parse_url($url, PHP_URL_SCHEME))==='https'
                && $allowedHost
                && parse_url($url, PHP_URL_USER)===null
                && parse_url($url, PHP_URL_PASS)===null;
            // Accept a valid SSLCommerz hosted GatewayPageURL even if the optional
            // response status field is absent or differently cased.
            if (!$res->successful() || !$safeGatewayUrl) {
                throw new \RuntimeException('Session initialization failed');
            }
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'completed','updated_at'=>now()]);
            return redirect()->away($url);
        } catch (\Throwable $e) {
            DB::table('lebd_exam_section_package_orders')->where('transaction_id',$reference)->where('status','pending')->update(['status'=>'failed','updated_at'=>now()]);
            DB::table('lebd_exam_section_checkout_intents')->where('id',$intent->id)->update(['status'=>'failed','updated_at'=>now()]);
            $gatewayReply=isset($res) ? $res->json() : null;
            Log::warning('Exam Section SSL session failure',[
                'order'=>$reference,
                'error'=>$e->getMessage(),
                'http_status'=>isset($res) ? $res->status() : null,
                'gateway_status'=>is_array($gatewayReply) ? ($gatewayReply['status'] ?? null) : null,
                'gateway_reason'=>is_array($gatewayReply) ? ($gatewayReply['failedreason'] ?? $gatewayReply['error'] ?? $gatewayReply['message'] ?? null) : null,
                'gateway_response_keys'=>is_array($gatewayReply) ? array_keys($gatewayReply) : [],
                'gateway_body_excerpt'=>isset($res) ? mb_substr((string)$res->body(),0,1200) : null,
                'sandbox'=>$sandbox,
            ]);
            return redirect()->route('student.exam-section.package.review',$package)
                ->withErrors(['payment'=>'SSLCommerz Session তৈরি হয়নি। Gateway response ও credentials/log যাচাই করে আবার চেষ্টা করুন।']);
        }
    }
    private function credentials(): array
    {
        // Use the project's canonical SSLCommerz config file. Direct env()
        // reads from controllers can fail when Laravel configuration is cached.
        $store = (string) config('sslcommerz.apiCredentials.store_id',
            config('lebd_premium_ssl.store_id', ''));
        $secret = (string) config('sslcommerz.apiCredentials.store_password',
            config('lebd_premium_ssl.store_password', ''));
        $sandboxValue = config('lebd_premium_ssl.sandbox',
            config('services.sslcommerz.testmode', true));
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
                    $this->referral()->credit($id);
                });
            } catch (\Throwable $e) {Log::error('Exam Section payment validation failure',['order'=>$id]);return response('Verification error',502);}
            if ($event==='ipn') return response('OK',200);
            return redirect()->route('student.exam-section.categories')->with('success','অভিনন্দন! আপনার প্যাকেজ কেনা সফল হয়েছে। Premium Exam Access চালু হয়েছে। এখন Exam Section থেকে পরীক্ষা দিতে পারবেন।');
        }
        DB::table('lebd_exam_section_package_orders')->where('transaction_id',$id)->where('status','pending')->update(['status'=>$event,'updated_at'=>now()]);
        return redirect()->route('student.exam-section.packages')->withErrors(['payment'=>'Payment '.($event==='failed'?'failed':'cancelled')]);
    }
    public function success(Request $r) {return $this->callback($r,'success');}
    public function ipn(Request $r) {return $this->callback($r,'ipn');}
    public function fail(Request $r) {return $this->callback($r,'failed');}
    public function cancel(Request $r) {return $this->callback($r,'cancelled');}
}
