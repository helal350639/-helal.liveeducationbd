<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\PremiumClass\PremiumPaymentPreviewController;

// Require ONCE from routes/web.php, after the existing premium_class.php include.
// GET is read-only: money is never deducted on preview.
Route::get('/premium-recordings/checkout/{package}/payment-preview', [PremiumPaymentPreviewController::class, 'show'])
    ->middleware('auth:student')->whereNumber('package')->name('premium.payment.preview');
