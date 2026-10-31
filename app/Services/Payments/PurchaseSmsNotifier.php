<?php
namespace App\Services\Payments;

use App\Http\Controllers\SourceCtrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/** At-most-one automatic send attempt per confirmed purchase. No SMS on unsuccessful orders. */
class PurchaseSmsNotifier
{
    public function notifyPremiumOrder(int $orderId): void
    {
        if (!Schema::hasTable('lebd_purchase_sms_outbox')) return;
        $order = DB::table('lebd_premium_class_orders')->where('id', $orderId)->where('status', 'paid')->first();
        if (!$order) return;
        $student = DB::table('students')->where('id', $order->student_id)->first();
        $phone = trim((string) ($student->contact ?? ''));
        $message = 'Live Education BD: Your purchase is successful! Your course/package is now active. Log in to start learning. Thank you for choosing us.';
        if (strlen($message) > 150) return;
        DB::table('lebd_purchase_sms_outbox')->insertOrIgnore([
            'order_id' => $orderId, 'student_id' => $order->student_id, 'phone' => $phone,
            'message' => $message, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        if (!filter_var(config('lebd_payments.sms_enabled', false), FILTER_VALIDATE_BOOLEAN) || !$phone) return;
        // Claim once: callbacks/repeated requests will not send another message.
        $claimed = DB::table('lebd_purchase_sms_outbox')->where('order_id', $orderId)
            ->where('status', 'pending')->update(['status' => 'attempted', 'updated_at' => now()]);
        if (!$claimed) return;
        try {
            // SourceCtrl::sms_send() must already have working private provider settings.
            (new SourceCtrl())->sms_send($phone, $message);
        } catch (\Throwable $e) {
            Log::warning('Purchase SMS attempt failed', ['order_id' => $orderId, 'reason' => $e->getMessage()]);
            DB::table('lebd_purchase_sms_outbox')->where('order_id', $orderId)->update([
                'status' => 'failed', 'updated_at' => now(),
            ]);
        }
    }
}
