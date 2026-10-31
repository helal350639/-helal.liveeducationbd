<?php

namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/** MCQ subscription checkout via SSLCommerz hosted payment. No referral-wallet code here. */
class SubscriptionPaymentController extends Controller
{
    private function credentials(): array
    {
        return [
            'id' => (string) config('services.sslcommerz.store_id', env('SSLCZ_STORE_ID', '')),
            'password' => (string) config('services.sslcommerz.store_password', env('SSLCZ_STORE_PASSWORD', '')),
            'test' => filter_var(config('services.sslcommerz.testmode', env('SSLCZ_TESTMODE', true)), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function gatewayRoot(): string
    {
        return $this->credentials()['test'] ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';
    }

    private function callbackUrl(string $name): string
    {
        // These three routes must exist; see ROUTE_INTEGRATION_REQUIRED.txt.
        return route('student.mcq.ssl.'.$name);
    }

    public function pay(Request $request, string $section, int $package)
    {
        abort_unless($section === 'mcq', 404);
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $creds = $this->credentials();
        if ($creds['id'] === '' || $creds['password'] === '') {
            return back()->with('error', 'SSLCommerz configuration incomplete.');
        }
        // No wallet token is needed for the gateway payment option.
        $plan = DB::table('mcq_subscription_packages')->where('id', $package)->where('status', 'Active')->first();
        if (!$plan || !in_array($plan->duration_unit, ['days', 'months', 'years'], true) || (int) $plan->duration_value < 1) {
            return back()->with('error', 'এই প্যাকেজটি বর্তমানে কেনা যাচ্ছে না।');
        }
        $amount = round((float) $plan->price, 2);
        if ($amount < 10 || $amount > 500000) return back()->with('error', 'SSLCommerz-এ প্যাকেজের মূল্য ১০–৫০০০০০ টাকা হতে হবে।');
        // Gateway transaction ids are <=30 characters.
        $trx = 'MCQS'.strtoupper(Str::random(24));
        $now = Carbon::now('Asia/Dhaka');
        try {
            DB::table('lebd_mcq_payment_orders')->insert([
                'student_id' => $student->id, 'package_id' => $package,
                'transaction_id' => $trx, 'package_name' => $plan->name,
                'amount' => $amount, 'currency' => 'BDT',
                'duration_value' => $plan->duration_value, 'duration_unit' => $plan->duration_unit,
                'status' => 'Pending', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $name = trim((string) ($student->name ?? 'Student')) ?: 'Student';
            $email = trim((string) ($student->email ?? '')) ?: 'student@example.com';
            $phone = trim((string) ($student->phone ?? $student->mobile ?? $student->contact ?? ''));
            if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) $phone = '01700000000';
            $response = Http::asForm()->timeout(25)->post($this->gatewayRoot().'/gwprocess/v4/api.php', [
                'store_id' => $creds['id'], 'store_passwd' => $creds['password'],
                'total_amount' => number_format($amount, 2, '.', ''), 'currency' => 'BDT', 'tran_id' => $trx,
                'success_url' => $this->callbackUrl('success'),
                'fail_url' => $this->callbackUrl('fail'),
                'cancel_url' => $this->callbackUrl('cancel'),
                'ipn_url' => $this->callbackUrl('ipn'),
                'cus_name' => $name, 'cus_email' => $email,
                'cus_add1' => 'Bangladesh', 'cus_city' => 'Dhaka', 'cus_country' => 'Bangladesh',
                'cus_phone' => $phone, 'shipping_method' => 'NO',
                'product_name' => (string) $plan->name,
                'product_category' => 'Education', 'product_profile' => 'non-physical-goods',
            ]);
            $reply = $response->json();
            $gatewayUrl = is_array($reply) ? ($reply['GatewayPageURL'] ?? null) : null;
            if (!$response->successful() || !is_string($gatewayUrl) || !str_starts_with($gatewayUrl, 'https://')) {
                Log::warning('MCQ SSLCommerz session initiation failed', ['transaction_id' => $trx, 'http_status' => $response->status()]);
                return back()->with('error', 'Payment Gateway চালু করা যায়নি। আবার চেষ্টা করুন।');
            }
            // SSLCommerz can return a hosted checkout URL on a different subdomain.
            // Accept only HTTPS under sslcommerz.com, never a lookalike domain.
            $host = parse_url($gatewayUrl, PHP_URL_HOST);
            $scheme = parse_url($gatewayUrl, PHP_URL_SCHEME);
            $host = is_string($host) ? strtolower($host) : '';
            $allowedHost = $host === 'sslcommerz.com' || str_ends_with($host, '.sslcommerz.com');
            if ($scheme !== 'https' || !$allowedHost
                || parse_url($gatewayUrl, PHP_URL_USER) !== null
                || parse_url($gatewayUrl, PHP_URL_PASS) !== null) {
                Log::warning('MCQ SSLCommerz gateway URL rejected', [
                    'transaction_id' => $trx,
                    'gateway_host' => $host,
                ]);
                return back()->with('error', 'Payment Gateway URL যাচাই করা যায়নি।');
            }
            return redirect()->away($gatewayUrl);
        } catch (Throwable $e) {
            Log::error('MCQ SSLCommerz initiation exception', ['message' => $e->getMessage()]);
            return back()->with('error', 'পেমেন্ট চালু করা যায়নি। সিস্টেম লগ পরীক্ষা করুন।');
        }
    }

    /** Keep the existing checkout route action compatible. */
    public function start(Request $request, string $section, int $package)
    {
        return $this->pay($request, $section, $package);
    }

    public function initiate(Request $request, string $section, int $package)
    {
        return $this->pay($request, $section, $package);
    }

    public function success(Request $request)
    {
        return $this->validatePayment($request, false);
    }

    public function ipn(Request $request)
    {
        return $this->validatePayment($request, true);
    }

    private function validatePayment(Request $request, bool $ipn)
    {
        $trx = (string) $request->input('tran_id', '');
        $valId = (string) $request->input('val_id', '');
        if ($trx === '' || $valId === '') return $ipn ? response('Missing transaction details', 400) : redirect()->route('student.mcq.orders')->with('error', 'Payment verification details missing.');
        $order = DB::table('lebd_mcq_payment_orders')->where('transaction_id', $trx)->first();
        if (!$order) return $ipn ? response('Unknown transaction', 404) : redirect()->route('student.mcq.orders')->with('error', 'Order not found.');
        if ($order->status === 'Complete') return $ipn ? response('OK', 200) : redirect()->route('student.mcq.orders')->with('success', 'পেমেন্ট ইতিমধ্যে সম্পন্ন হয়েছে।');
        $creds = $this->credentials();
        try {
            $response = Http::timeout(25)->get($this->gatewayRoot().'/validator/api/validationserverAPI.php', [
                'val_id' => $valId, 'store_id' => $creds['id'], 'store_passwd' => $creds['password'], 'format' => 'json',
            ]);
            $validation = $response->json();
            $valid = $response->successful() && is_array($validation)
                && in_array($validation['status'] ?? '', ['VALID', 'VALIDATED'], true)
                && hash_equals((string) ($validation['tran_id'] ?? ''), $trx)
                && abs((float) ($validation['amount'] ?? -1) - (float) $order->amount) < 0.011
                && strtoupper((string) ($validation['currency'] ?? '')) === 'BDT'
                && (int) ($validation['risk_level'] ?? 0) === 0;
            if (!$valid) {
                Log::warning('MCQ SSLCommerz validation rejected', ['transaction_id' => $trx, 'status' => $validation['status'] ?? null]);
                return $ipn ? response('Validation rejected', 422) : redirect()->route('student.mcq.orders')->with('error', 'Payment verification failed or requires manual review.');
            }
            DB::transaction(function () use ($trx, $order) {
                $current = DB::table('lebd_mcq_payment_orders')->where('transaction_id', $trx)->lockForUpdate()->first();
                if (!$current || $current->status === 'Complete') return;
                $now = Carbon::now('Asia/Dhaka');
                $start = $now->copy();
                $existing = DB::table('lebd_mcq_subscriptions')->where('student_id', $current->student_id)
                    ->where('package_id', $current->package_id)->lockForUpdate()->first();
                if ($existing && Carbon::parse($existing->ends_at)->greaterThan($start)) $start = Carbon::parse($existing->ends_at);
                $end = $start->copy();
                switch ($current->duration_unit) {
                    case 'days': $end->addDays((int) $current->duration_value); break;
                    case 'months': $end->addMonthsNoOverflow((int) $current->duration_value); break;
                    case 'years': $end->addYearsNoOverflow((int) $current->duration_value); break;
                    default: throw new \RuntimeException('Invalid package duration');
                }
                DB::table('lebd_mcq_payment_orders')->where('id', $current->id)->update([
                    'status' => 'Complete', 'paid_at' => $now, 'updated_at' => $now,
                ]);
                if ($existing) {
                    DB::table('lebd_mcq_subscriptions')->where('id', $existing->id)->update([
                        'ends_at' => $end, 'last_order_id' => $current->id, 'updated_at' => $now,
                    ]);
                } else {
                    DB::table('lebd_mcq_subscriptions')->insert([
                        'student_id' => $current->student_id, 'package_id' => $current->package_id,
                        'starts_at' => $start, 'ends_at' => $end, 'last_order_id' => $current->id,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }, 3);
            return $ipn ? response('OK', 200) : redirect()->route('student.mcq.orders')->with('success', 'SSLCommerz Payment Successful! MCQ Package Access চালু হয়েছে।');
        } catch (Throwable $e) {
            Log::error('MCQ SSLCommerz validation failed', ['transaction_id' => $trx, 'message' => $e->getMessage()]);
            return $ipn ? response('Retry later', 503) : redirect()->route('student.mcq.orders')->with('error', 'Payment verification pending. Support-এ Transaction ID জানান।');
        }
    }

    public function fail(Request $request)
    {
        return $this->terminal($request, 'Failed');
    }

    public function cancel(Request $request)
    {
        return $this->terminal($request, 'Cancelled');
    }

    private function terminal(Request $request, string $status)
    {
        $trx = (string) $request->input('tran_id', '');
        // Never mark Complete orders as failed based on a browser/callback request.
        // Do not update pending orders from unverified callbacks, either.
        Log::info('MCQ SSLCommerz non-success redirect', ['transaction_id' => $trx, 'reported_status' => $status]);
        return redirect()->route('student.mcq.orders')->with('error', $status === 'Cancelled' ? 'Payment cancelled.' : 'Payment not completed.');
    }
}
