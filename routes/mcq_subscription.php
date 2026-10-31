<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MCQSubscription\PackageController;
use App\Http\Controllers\MCQJobPreparation\StudentPackageController;
use App\Http\Controllers\MCQJobPreparation\SubscriptionPaymentController;
use App\Http\Controllers\MCQJobPreparation\SubscriptionReferralController;

// Existing MCQ admin routes — unchanged.
Route::middleware('auth')->prefix('admin/mcq-subscription')->name('admin.mcq-subscription.')->group(function () {
    Route::get('/', [PackageController::class, 'index'])->name('index');
    Route::get('/create', [PackageController::class, 'create'])->name('create');
    Route::post('/', [PackageController::class, 'store'])->name('store');
    Route::get('/{package}/edit', [PackageController::class, 'edit'])->whereNumber('package')->name('edit');
    Route::put('/{package}', [PackageController::class, 'update'])->whereNumber('package')->name('update');
    Route::patch('/{package}/status', [PackageController::class, 'toggle'])->whereNumber('package')->name('status');
});
Route::get('/packages/{section}', [StudentPackageController::class, 'catalog'])
    ->where('section', 'mcq')->name('lebd.packages.index');
Route::get('/packages/{section}/{package}', [StudentPackageController::class, 'detail'])
    ->where(['section' => 'mcq', 'package' => '[0-9]+'])->name('lebd.packages.details');
Route::get('/mcq-packages', [StudentPackageController::class, 'index'])->name('mcq.packages.index');
Route::get('/mcq-packages/{package}', [StudentPackageController::class, 'show'])
    ->whereNumber('package')->name('mcq.packages.show');
Route::get('/packages/{section}/{package}/checkout', [StudentPackageController::class, 'checkout'])
    ->where(['section' => 'mcq', 'package' => '[0-9]+'])->name('lebd.packages.checkout');
Route::post('/packages/{section}/{package}/pay', [SubscriptionPaymentController::class, 'start'])
    ->middleware('auth:student')->where(['section'=>'mcq','package'=>'[0-9]+'])
    ->name('lebd.subscription.payment.start');

// Preserve original callback URLs and names, but dispatch to methods that actually exist.
// Add separate, uniquely named callback URLs used by the current SSLCommerz controller.
// SSLCommerz should POST to the appropriate student.mcq.ssl.* URL returned by route().
foreach (['success', 'fail', 'cancel', 'ipn'] as $event) {
    Route::post('/payments/mcq/'.$event, [SubscriptionPaymentController::class, $event])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->name('lebd.subscription.payment.'.$event);

    Route::post('/payments/mcq/ssl/'.$event, [SubscriptionPaymentController::class, $event])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->name('student.mcq.ssl.'.$event);
}

// STEP-79: Referral wallet checkout remains separate from SSLCommerz.
Route::post('/packages/{section}/{package}/pay-referral', [SubscriptionReferralController::class, 'pay'])
    ->middleware('auth:student')->where(['section'=>'mcq','package'=>'[0-9]+'])
    ->name('lebd.subscription.payment.referral');
Route::get('/student/mcq-orders', [SubscriptionReferralController::class, 'orders'])
    ->middleware('auth:student')->name('student.mcq.orders');

// STEP-80: Purchased MCQ categories (kept inside existing subscription routes).
Route::get('/mcq-study/my-categories', [\App\Http\Controllers\MCQJobPreparation\PurchasedMcqCategoriesController::class, 'index'])
    ->name('student.mcq.purchased-categories');

    // LEBD-05: MCQ purchased packages (student only) — insert ONCE in existing routes/mcq_subscription.php
\Illuminate\Support\Facades\Route::get('/mcq-study/my-purchased',
    [\App\Http\Controllers\MCQJobPreparation\PurchasedMcqAccessController::class, 'index'])
    ->middleware('auth:student')
    ->name('student.mcq.purchased');
