<?php
namespace App\Services\MCQSubscription;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/** Post-commit SMS, one dispatch attempt per order/recipient; never debit or grant access. */
final class MCQPaymentSms
{
    public static function notify(int $orderId): void
    {
        try {
            if (!Schema::hasTable('lebd_mcq_payment_sms')) {
                Log::warning('MCQ purchase SMS table missing', ['order_id'=>$orderId]);
                return;
            }
            $order = DB::table('lebd_mcq_payment_orders')->where('id', $orderId)->first();
            if (!$order || $order->status !== 'Complete') return;
            // Never send a purchase success SMS for an order without an entitlement.
            if (!DB::table('lebd_mcq_subscriptions')->where('student_id',$order->student_id)
                ->where('package_id',$order->package_id)->where('last_order_id',$orderId)->exists()) return;
            $buyer = DB::table('students')->where('id',$order->student_id)->first();
            if ($buyer) {
                $message = 'Live Education BD: MCQ payment successful. BDT '.number_format((float)$order->amount,2,'.','')
                    .'. Package activated. Login to access your MCQ Study.';
                self::dispatch($orderId, 'buyer', $buyer->contact ?? null, $message);
            }
            if (!Schema::hasTable('lebd_mcq_referral_credits')) return;
            $credit = DB::table('lebd_mcq_referral_credits')->where('order_id',$orderId)
                ->where('status','credited')->first();
            if ($credit && (float)$credit->referrer_bonus > 0) {
                $owner = DB::table('students')->where('id',$credit->referrer_id)->first();
                if ($owner) {
                    $message = 'Live Education BD: You earned a referral bonus of BDT '
                        .number_format((float)$credit->referrer_bonus,2,'.','')
                        .' from a successful MCQ package purchase.';
                    self::dispatch($orderId, 'referrer', $owner->contact ?? null, $message);
                }
            }
        } catch (Throwable $e) {
            Log::warning('MCQ purchase SMS processing failed', ['order_id'=>$orderId,'error'=>$e->getMessage()]);
        }
    }

    /**
     * The existing SourceCtrl::sms_send() has no connection/total timeout.
     * Keep its same BulkSMS endpoint and env credentials without changing that
     * global helper or the payment transaction. Never block purchase redirects
     * indefinitely when the SMS gateway is unavailable.
     */
    private static function sendBulkSmsWithTimeout(string $phone, string $message)
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new \RuntimeException('SMS cURL initialization failed.');
        }
        try {
            curl_setopt_array($ch, [
                CURLOPT_URL => 'http://bulksmsbd.net/api/smsapi',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => [
                    'api_key' => (string) env('SMS_API_KEY', ''),
                    'senderid' => (string) env('SMS_ID', ''),
                    'number' => $phone,
                    'message' => $message,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 5,
            ]);
            $response = curl_exec($ch);
            if ($response === false) {
                throw new \RuntimeException('BulkSMS network request failed.');
            }
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode < 200 || $httpCode >= 300) {
                throw new \RuntimeException('BulkSMS HTTP response failed ('.$httpCode.').');
            }
            return $response;
        } finally {
            curl_close($ch);
        }
    }

    private static function dispatch(int $orderId, string $recipient, ?string $rawPhone, string $message): void
    {
        $phone = preg_replace('/[\s-]+/', '', (string)$rawPhone);
        if (!preg_match('/^01[0-9]{9}$/', $phone)) {
            // Legacy student.contact is the verified project phone field; do not send to an invented number.
            Log::warning('MCQ purchase SMS skipped: invalid contact', ['order_id'=>$orderId,'recipient'=>$recipient]);
            return;
        }
        if (!preg_match('/^[\x20-\x7E]+$/', $message) || strlen($message) > 150) {
            Log::error('MCQ purchase SMS invalid length', ['order_id'=>$orderId,'recipient'=>$recipient]);
            return;
        }
        // Unique (order_id, recipient_type) means callbacks/replays cannot dispatch twice.
        // Claim BEFORE contacting gateway; ambiguous network failures are NOT auto-retried.
        $inserted = DB::table('lebd_mcq_payment_sms')->insertOrIgnore([
            'order_id'=>$orderId,'recipient_type'=>$recipient,'phone'=>$phone,
            'message'=>$message,'status'=>'attempted','created_at'=>now(),'updated_at'=>now(),
        ]);
        if (!$inserted) return;
        try {
            // Reuse the existing BulkSMS BD integration. Do not modify SourceCtrl or .env.
            $response = self::sendBulkSmsWithTimeout($phone, $message);
            $status = $response === false || $response === null || trim((string)$response) === ''
                ? 'unknown' : 'submitted';
            DB::table('lebd_mcq_payment_sms')->where('order_id',$orderId)
                ->where('recipient_type',$recipient)->update(['status'=>$status,'updated_at'=>now()]);
        } catch (Throwable $e) {
            DB::table('lebd_mcq_payment_sms')->where('order_id',$orderId)
                ->where('recipient_type',$recipient)->update(['status'=>'error','updated_at'=>now()]);
            Log::warning('MCQ purchase SMS gateway exception', ['order_id'=>$orderId,'recipient'=>$recipient,'error'=>$e->getMessage()]);
        }
    }
}
