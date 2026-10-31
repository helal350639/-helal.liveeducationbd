<?php
namespace App\Http\Controllers\Students\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use App\Services\Payments\LebdReferralWallet;
use App\Services\Payments\PurchaseSmsNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReferralPurchaseController extends Controller
{
    public function checkout(Request $request, PremiumClassPackage $package, LebdReferralWallet $wallet, PurchaseSmsNotifier $sms)
    {
        $studentId = auth('student')->id();
        abort_unless($studentId, 401);
        abort_unless($package->is_active, 404);
        if (!Schema::hasTable('lebd_premium_class_orders') || !Schema::hasTable('lebd_referral_purchase_ledger')) {
            return back()->withErrors(['payment' => 'Purchase database setup is incomplete.']);
        }
        try {
            $orderId = DB::transaction(function () use ($studentId, $package, $wallet) {
                // Lock the student BEFORE reading pending withdrawals or checking funds.
                $student = DB::table('students')->where('id', $studentId)->lockForUpdate()->first();
                abort_unless($student, 401);
                $fresh = PremiumClassPackage::query()->whereKey($package->id)->where('is_active', true)->firstOrFail();
                $base = $wallet->paisa($fresh->price);
                $price = $wallet->paisa($fresh->final_price ?? $fresh->price);
                if ($price < 100 || $base < $price) {
                    throw ValidationException::withMessages(['payment' => 'Package price is invalid.']);
                }
                // One active entitlement per student/package: avoid unintended repeat charges.
                $existing = DB::table('lebd_premium_class_orders')->where('student_id', $studentId)
                    ->where('package_id', $fresh->id)->where('status', 'paid')
                    ->where('expires_at', '>', now())->first();
                if ($existing) {
                    throw ValidationException::withMessages(['payment' => 'This package is already active in your account.']);
                }
                $wallet->debitLockedStudent($student, $price);
                $tranId = 'LEBDR'.strtoupper(Str::random(20));
                $now = now();
                $days = max(1, (int) $fresh->validity_days);
                $orderId = DB::table('lebd_premium_class_orders')->insertGetId([
                    'student_id' => $studentId, 'package_id' => $fresh->id, 'tran_id' => $tranId,
                    'val_id' => null, 'amount' => number_format($price / 100, 2, '.', ''),
                    'currency' => 'BDT', 'status' => 'paid',
                    'package_type' => $fresh->package_type,
                    'department_categories' => json_encode($fresh->department_categories ?: []),
                    'non_department_categories' => json_encode($fresh->non_department_categories ?: []),
                    'validity_days' => $days, 'paid_at' => $now,
                    'expires_at' => $now->copy()->addDays($days),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('lebd_referral_purchase_ledger')->insert([
                    'student_id' => $studentId, 'order_id' => $orderId,
                    'tran_id' => $tranId, 'amount' => number_format($price / 100, 2, '.', ''),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                return $orderId;
            }, 3);
        } catch (ValidationException $e) {
            throw $e;
        }
        // SMS is optional and must not roll back a confirmed purchase.
        $sms->notifyPremiumOrder($orderId);
        return redirect()->route('premium.payment.purchased.success', ['order' => $orderId]);
    }
}
