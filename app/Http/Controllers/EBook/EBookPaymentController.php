<?php
namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;
use App\Services\EBook\EBookReferralSettlement;

class EBookPaymentController extends Controller
{
    /**
     * Use the same SSLCommerz merchant credentials and hosted-checkout API
     * pattern as the working MCQ Preparation subscription flow.
     * E-Book keeps its own order table, callbacks and entitlement activation.
     */
    private function gatewayCredentials(): array
    {
        return [
            'id' => (string) config('services.sslcommerz.store_id', env('SSLCZ_STORE_ID', '')),
            'password' => (string) config('services.sslcommerz.store_password', env('SSLCZ_STORE_PASSWORD', '')),
            'test' => filter_var(config('services.sslcommerz.testmode', env('SSLCZ_TESTMODE', true)), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function gatewayRoot(): string
    {
        return $this->gatewayCredentials()['test']
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    private function callbackUrl(string $event): string
    {
        return route('lebd.ebook.payment.'.$event);
    }

    public function pay(Request $request, string $kind, int $category, int $package)
    {
        $request->validate([
            'agree' => 'accepted',
            'student_id' => 'required|integer|min:1',
            'package_id' => 'required|integer|min:1',
            'category_id' => 'required|integer|min:1',
            'kind' => 'required|in:department,non-department',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:30',
            'address1' => 'nullable|string|max:255',
            'total' => 'required|numeric|min:10|max:500000',
        ]);
        abort_unless(in_array($kind, ['department', 'non-department'], true), 404);
        $student = Auth::guard('student')->user();
        abort_unless($student, 401, 'Please sign in to your student account.');
        abort_unless((int) $request->input('student_id') === (int) $student->id, 403, 'Student checkout information mismatch.');
        abort_unless((int) $request->input('package_id') === $package
            && (int) $request->input('category_id') === $category
            && (string) $request->input('kind') === $kind, 422, 'Package checkout information mismatch. Please start again.');
        $cat = DB::table('lebd_ebook_categories')->where('id', $category)
            ->where('kind', $kind)->where('status', 'Active')->first();
        abort_unless($cat, 404);
        $p = DB::table('lebd_ebook_packages as p')
            ->join('lebd_ebook_package_categories as pc', 'pc.package_id', '=', 'p.id')
            ->where('p.id', $package)->where('pc.category_id', $category)
            ->where('p.status', 'Active')->select('p.*')->first();
        abort_unless($p, 404);
        // Stop repeat gateway charges for a package already active for this student.
        // The existing per-category entitlements are authoritative for E-Book access.
        // Entitlements are category-scoped. The same package may be linked to
        // multiple E-Book categories, so access in another category must not
        // block a legitimate purchase for the category selected on this page.
        $samePackageActive = DB::table('lebd_ebook_entitlements')
            ->where('user_id', $student->id)
            ->where('category_id', $category)
            ->where('package_id', $package)
            ->where('status', 'Active')
            ->where('expires_at', '>', now('Asia/Dhaka'))
            ->exists();
        if ($samePackageActive) {
            return back()->with('error','এই প্যাকেজ কিনেছেন। আবার পেমেন্ট প্রয়োজন নেই।');
        }
        $now = Carbon::now('Asia/Dhaka');
        $offerActive = !$p->offer_ends_at || Carbon::parse($p->offer_ends_at, 'Asia/Dhaka')->endOfDay()->gte($now);
        $quote = app(EBookReferralSettlement::class)->sessionQuote($p,(int)$student->id,$request->session());
        $amount = $quote['total'] / 100;
        $postedAmountCents = (int) round((float) $request->input('total') * 100);
        if ($postedAmountCents !== (int) $quote['total']) {
            return back()->with('error', 'প্যাকেজের মূল্য পরিবর্তিত হয়েছে। Payment Preview থেকে আবার চেষ্টা করুন।');
        }
        if ($amount < 10 || $amount > 500000) {
            return back()->with('error', 'প্যাকেজের মূল্য Gateway গ্রহণযোগ্য সীমায় নেই।');
        }
        if (!in_array($p->duration_unit, ['days', 'months', 'years'], true) || (int)$p->duration_value < 1) {
            return back()->with('error', 'প্যাকেজের মেয়াদ সঠিক নয়।');
        }
        // Match the working MCQ gateway behavior: use the student's saved
        // contact details when valid, otherwise supply safe gateway fallbacks.
        $email = trim((string) ($student->email ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'student@example.com';
        }
        $phone = trim((string) ($student->phone ?? $student->mobile ?? $student->contact ?? ''));
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
            $phone = '01700000000';
        }
        $trx = 'EBK'.strtoupper(Str::random(20));
        DB::transaction(function () use ($student,$category,$p,$trx,$amount,$quote) {
        $orderId = DB::table('lebd_ebook_payment_orders')->insertGetId([
            'student_id'=>$student->id,'category_id'=>$category,'package_id'=>$p->id,
            'transaction_id'=>$trx,'package_name'=>$p->name,'amount'=>$amount,'currency'=>'BDT',
            'duration_value'=>$p->duration_value,'duration_unit'=>$p->duration_unit,
            'status'=>'Pending','created_at'=>now(),'updated_at'=>now(),
        ]);
        app(EBookReferralSettlement::class)->addAttribution($orderId,(int)$student->id,(int)$p->id,$quote);
        },3);
        $credentials = $this->gatewayCredentials();
        if ($credentials['id'] === '' || $credentials['password'] === '') {
            DB::table('lebd_ebook_payment_orders')->where('transaction_id',$trx)->where('status','Pending')
                ->update(['status'=>'Failed','updated_at'=>now()]);
            return back()->with('error','SSLCommerz configuration incomplete. Store ID এবং Store Password পরীক্ষা করুন।');
        }

        $payload = [
            'store_id' => $credentials['id'],
            'store_passwd' => $credentials['password'],
            'total_amount' => number_format($amount, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => $trx,
            'success_url' => $this->callbackUrl('success'),
            'fail_url' => $this->callbackUrl('fail'),
            'cancel_url' => $this->callbackUrl('cancel'),
            'ipn_url' => $this->callbackUrl('ipn'),
            'cus_name' => mb_substr((string)($student->name ?: 'Student'), 0, 50),
            'cus_email' => $email,
            'cus_phone' => $phone,
            'cus_add1' => 'Bangladesh',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'shipping_method' => 'NO',
            'product_name' => mb_substr((string)$p->name, 0, 150),
            'product_category' => 'Education',
            'product_profile' => 'non-physical-goods',
            'value_a' => $trx,
            'value_b' => (string)$p->id,
            'value_c' => (string)$category,
        ];

        try {
            // Match MCQ Preparation's hosted SSLCommerz session initiation.
            $response = Http::asForm()->timeout(25)->post(
                $this->gatewayRoot().'/gwprocess/v4/api.php',
                $payload
            );
            $reply = $response->json();
            $gatewayUrl = is_array($reply) ? ($reply['GatewayPageURL'] ?? null) : null;

            if ($response->successful() && is_string($gatewayUrl) && filter_var($gatewayUrl, FILTER_VALIDATE_URL)) {
                $scheme = strtolower((string) parse_url($gatewayUrl, PHP_URL_SCHEME));
                $host = strtolower((string) parse_url($gatewayUrl, PHP_URL_HOST));
                $allowedHost = $host === 'sslcommerz.com' || str_ends_with($host, '.sslcommerz.com');

                if ($scheme === 'https' && $allowedHost
                    && parse_url($gatewayUrl, PHP_URL_USER) === null
                    && parse_url($gatewayUrl, PHP_URL_PASS) === null) {
                    return redirect()->away($gatewayUrl);
                }
            }

            Log::warning('E-Book SSLCommerz session initiation failed', [
                'transaction_id' => $trx,
                'http_status' => $response->status(),
                'gateway_status' => is_array($reply) ? ($reply['status'] ?? null) : null,
            ]);
        } catch (Throwable $e) {
            Log::error('E-Book payment start error', [
                'transaction_id' => $trx,
                'message' => $e->getMessage(),
            ]);
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
        if($valId===''){
            Log::warning('E-Book callback missing validation ID',['transaction_id'=>$trx]);
            if ($event === 'ipn') return response('Missing validation ID', 400);
            return $this->result($order->id,'pending',true);
        }
        $valid = false;
        $validationUnavailable = false;
        try {
            // Validate directly with SSLCommerz, following the working MCQ flow.
            // Never trust the browser callback's amount/status as proof of payment.
            $credentials = $this->gatewayCredentials();
            $response = Http::timeout(25)->get(
                $this->gatewayRoot().'/validator/api/validationserverAPI.php',
                [
                    'val_id' => $valId,
                    'store_id' => $credentials['id'],
                    'store_passwd' => $credentials['password'],
                    'format' => 'json',
                ]
            );
            $validation = $response->json();
            $valid = $response->successful()
                && is_array($validation)
                && in_array($validation['status'] ?? '', ['VALID', 'VALIDATED'], true)
                && hash_equals((string)$order->transaction_id, (string)($validation['tran_id'] ?? ''))
                && abs((float)($validation['amount'] ?? -1) - (float)$order->amount) < 0.011
                && strtoupper((string)($validation['currency'] ?? '')) === 'BDT'
                && (int)($validation['risk_level'] ?? 0) === 0;

            if (!$valid) {
                Log::warning('E-Book SSLCommerz validation rejected', [
                    'transaction_id' => $trx,
                    'status' => is_array($validation) ? ($validation['status'] ?? null) : null,
                    'risk_level' => is_array($validation) ? ($validation['risk_level'] ?? null) : null,
                ]);
            }
        } catch(Throwable $e) {
            Log::error('E-Book validation failed',['transaction_id'=>$trx,'message'=>$e->getMessage()]);
            $valid = false;
            $validationUnavailable = true;
        }
        if ($valid !== true) {
            // Do not acknowledge an unverified IPN as successful; SSLCommerz
            // must be able to retry if its validation service was unavailable.
            if ($event === 'ipn') {
                return response($validationUnavailable ? 'Retry later' : 'Validation rejected', $validationUnavailable ? 503 : 422);
            }
            return $this->result($order->id, 'pending', true);
        }
        DB::transaction(function() use($order,$valId){
            $locked=DB::table('lebd_ebook_payment_orders')->where('id',$order->id)->lockForUpdate()->first();
            if(!$locked || $locked->status!=='Pending') return;
            $now=Carbon::now('Asia/Dhaka');
            if (Schema::hasTable('lebd_ebook_access_revocations')) {
                DB::table('lebd_ebook_access_revocations')
                    ->where('student_id',$locked->student_id)->where('package_id',$locked->package_id)->delete();
            }
            $existing=DB::table('lebd_ebook_entitlements')
                ->where('user_id',$locked->student_id)->where('category_id',$locked->category_id)
                ->where('package_id',$locked->package_id)
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
            app(EBookReferralSettlement::class)->credit((int)$locked->id);
        });
        return $this->result($order->id,'success',$event!=='ipn');
    }

    private function result(int $orderId,string $state,bool $browser)
    {
        if(!$browser) return response('OK',200);
        $order=DB::table('lebd_ebook_payment_orders')->find($orderId);
        abort_unless($order, 404);

        if ($state === 'success') {
            $kind = DB::table('lebd_ebook_categories')->where('id', $order->category_id)->value('kind');
            return redirect()->route('student.ebook.dashboard')->with('ebook_purchase_success', [
                'category_id' => (int) $order->category_id,
                'kind' => in_array($kind, ['department', 'non-department'], true) ? $kind : 'department',
                'package_name' => (string) $order->package_name,
                'order' => (string) $order->transaction_id,
                'method' => 'sslcommerz',
            ]);
        }

        return view('ebook.payment-result',compact('order','state'));
    }
}
