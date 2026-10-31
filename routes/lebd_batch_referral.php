<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\BatchReferralCheckoutController;

// Add this file ONCE to web.php, inside the web routes; no changes to legacy SSLCommerz routes.
Route::middleware(['web','auth:student'])->prefix('students/batch-referral')->name('students.batch.referral.')->group(function () {
    Route::post('/preview',[BatchReferralCheckoutController::class,'preview'])->name('preview');
    Route::post('/confirm',[BatchReferralCheckoutController::class,'confirm'])->name('confirm');
});
