<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MCQSubscription\PackageController;
use App\Http\Controllers\MCQJobPreparation\StudentPackageController;

// Preserve every existing admin subscription route.
Route::middleware('auth')->prefix('admin/mcq-subscription')->name('admin.mcq-subscription.')->group(function () {
    Route::get('/', [PackageController::class, 'index'])->name('index');
    Route::get('/create', [PackageController::class, 'create'])->name('create');
    Route::post('/', [PackageController::class, 'store'])->name('store');
    Route::get('/{package}/edit', [PackageController::class, 'edit'])->whereNumber('package')->name('edit');
    Route::put('/{package}', [PackageController::class, 'update'])->whereNumber('package')->name('update');
    Route::patch('/{package}/status', [PackageController::class, 'toggle'])->whereNumber('package')->name('status');
});

// Universal package URLs; currently "mcq" is the only configured datasource.
Route::get('/packages/{section}', [StudentPackageController::class, 'catalog'])
    ->where('section', 'mcq')->name('lebd.packages.index');
Route::get('/packages/{section}/{package}', [StudentPackageController::class, 'detail'])
    ->where(['section' => 'mcq', 'package' => '[0-9]+'])->name('lebd.packages.details');

// Legacy page addresses stay valid (same universal design).
Route::get('/mcq-packages', [StudentPackageController::class, 'index'])->name('mcq.packages.index');
Route::get('/mcq-packages/{package}', [StudentPackageController::class, 'show'])
    ->whereNumber('package')->name('mcq.packages.show');

// Public order review, no payment captured at this stage. Keep this before wiring the gateway.
Route::get('/packages/{section}/{package}/checkout', [StudentPackageController::class, 'checkout'])
    ->where(['section' => 'mcq', 'package' => '[0-9]+'])->name('lebd.packages.checkout');

// MCQ package checkout only. Legacy Course/Batch payment routes remain untouched.
Route::post('/packages/{section}/{package}/pay', [\App\Http\Controllers\MCQJobPreparation\SubscriptionPaymentController::class, 'start'])
    ->middleware('auth:student')->where(['section'=>'mcq','package'=>'[0-9]+'])
    ->name('lebd.subscription.payment.start');
foreach (['success','fail','cancel','ipn'] as $event) {
    Route::post('/payments/mcq/'.$event, [\App\Http\Controllers\MCQJobPreparation\SubscriptionPaymentController::class, 'callback'])
        ->defaults('event', $event)
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->name('lebd.subscription.payment.'.$event);
}
