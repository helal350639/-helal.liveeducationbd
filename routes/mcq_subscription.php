<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MCQSubscription\PackageController;
use App\Http\Controllers\Admin\MCQSubscription\ManualStudentGrantController;
use App\Http\Controllers\MCQJobPreparation\StudentPackageController;
use App\Http\Controllers\MCQJobPreparation\SubscriptionPaymentController;
use App\Http\Controllers\MCQJobPreparation\SubscriptionReferralController;

// LEBD-05: Existing MCQ admin routes, unchanged.
Route::middleware('auth')->prefix('admin/mcq-subscription')->name('admin.mcq-subscription.')->group(function () {
    Route::get('/', [PackageController::class, 'index'])->name('index');
    Route::get('/create', [PackageController::class, 'create'])->name('create');
    Route::post('/', [PackageController::class, 'store'])->name('store');
    Route::get('/{package}/edit', [PackageController::class, 'edit'])->whereNumber('package')->name('edit');
    Route::put('/{package}', [PackageController::class, 'update'])->whereNumber('package')->name('update');
    Route::patch('/{package}/status', [PackageController::class, 'toggle'])->whereNumber('package')->name('status');

    // Existing manual grants: no payment or order created.
    Route::get('/{package}/students', [ManualStudentGrantController::class, 'index'])
        ->whereNumber('package')->name('manual-students');
    Route::post('/{package}/students', [ManualStudentGrantController::class, 'store'])
        ->whereNumber('package')->name('manual-store');
    Route::patch('/{package}/students/{grant}/revoke', [ManualStudentGrantController::class, 'revoke'])
        ->whereNumber('package')->whereNumber('grant')->name('manual-revoke');
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

// Existing SSLCommerz callbacks; retain URLs, middleware and route names.
foreach (['success', 'fail', 'cancel', 'ipn'] as $event) {
    Route::post('/payments/mcq/'.$event, [SubscriptionPaymentController::class, $event])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->name('lebd.subscription.payment.'.$event);

    Route::post('/payments/mcq/ssl/'.$event, [SubscriptionPaymentController::class, $event])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->name('student.mcq.ssl.'.$event);
}

// Existing referral wallet checkout, unchanged.
Route::post('/packages/{section}/{package}/pay-referral', [SubscriptionReferralController::class, 'pay'])
    ->middleware('auth:student')->where(['section'=>'mcq','package'=>'[0-9]+'])
    ->name('lebd.subscription.payment.referral');
Route::get('/student/mcq-orders', [SubscriptionReferralController::class, 'orders'])
    ->middleware('auth:student')->name('student.mcq.orders');

Route::get('/mcq-study/my-categories', [\App\Http\Controllers\MCQJobPreparation\PurchasedMcqCategoriesController::class, 'index'])
    ->name('student.mcq.purchased-categories');
Route::get('/mcq-study/my-purchased', [\App\Http\Controllers\MCQJobPreparation\PurchasedMcqAccessController::class, 'index'])
    ->middleware('auth:student')->name('student.mcq.purchased');

// FIX: Do not use /student/all-referral-links; legacy /student/{student} resource route
// can capture it and render StudentCtrl@show -> layouts.students.read with null $value.
// Keep the SAME route NAME so dashboard route('student.all-referral-links') keeps working.
Route::get('/mcq-study/referral-center',
    [\App\Http\Controllers\Students\Referral\AllReferralLinksController::class, 'index'])
    ->middleware('auth:student')->name('student.all-referral-links');
