<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PremiumClass\LebdPremiumRecordingController;
use App\Http\Controllers\Admin\PremiumClass\PackageController;
use App\Http\Middleware\PremiumClass\PremiumClassAdminOnly;

// Include this route file ONCE from routes/web.php. Admin-only protection is mandatory.
Route::middleware(['auth',PremiumClassAdminOnly::class])->prefix('admin')->group(function () {
    Route::prefix('premium-recordings')->name('admin.premium-recordings.')->group(function () {
        Route::get('/',[LebdPremiumRecordingController::class,'index'])->name('index');
        Route::post('/subjects',[LebdPremiumRecordingController::class,'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{subject}',[LebdPremiumRecordingController::class,'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{subject}',[LebdPremiumRecordingController::class,'destroySubject'])->name('subjects.destroy');
        Route::post('/chapters',[LebdPremiumRecordingController::class,'storeChapter'])->name('chapters.store');
        Route::put('/chapters/{chapter}',[LebdPremiumRecordingController::class,'updateChapter'])->name('chapters.update');
        Route::delete('/chapters/{chapter}',[LebdPremiumRecordingController::class,'destroyChapter'])->name('chapters.destroy');
        Route::post('/videos',[LebdPremiumRecordingController::class,'storeVideo'])->name('videos.store');
        Route::get('/videos/{video}/view',[LebdPremiumRecordingController::class,'viewVideo'])->name('videos.view');
        Route::put('/videos/{video}',[LebdPremiumRecordingController::class,'updateVideo'])->name('videos.update');
        Route::delete('/videos/{video}',[LebdPremiumRecordingController::class,'destroyVideo'])->name('videos.destroy');
    });
    Route::prefix('premium-class-packages')->name('admin.premium-class-packages.')->group(function () {
        Route::get('/',[PackageController::class,'index'])->name('index');
        Route::get('/create',[PackageController::class,'create'])->name('create');
        Route::get('/{package}/edit',[PackageController::class,'edit'])->whereNumber('package')->name('edit');
        Route::post('/',[PackageController::class,'store'])->name('store');
        Route::put('/{package}',[PackageController::class,'update'])->name('update');
        Route::delete('/{package}',[PackageController::class,'destroy'])->name('destroy');
    });
});

// Public package catalog and free-only video playback, separate from admin routes.
Route::get('/premium-recordings/packages',[\App\Http\Controllers\Students\PremiumClass\RecordedClassController::class,'packages'])->name('premium.recordings.packages');
Route::get('/premium-recordings/watch/{video}',[\App\Http\Controllers\Students\PremiumClass\RecordedClassController::class,'watch'])->whereNumber('video')->name('premium.recordings.watch');

// First show payment summary; payment initiation remains a CSRF-protected POST.
Route::get('/premium-recordings/checkout/{package}/review',[\App\Http\Controllers\Students\PremiumClass\PremiumPaymentController::class,'review'])
 ->whereNumber('package')->name('premium.payment.review');
// SSLCommerz premium checkout. Student must be logged in on the student guard.
Route::post('/premium-recordings/checkout/{package}',[\App\Http\Controllers\Students\PremiumClass\PremiumPaymentController::class,'checkout'])
 ->middleware('auth:student')->whereNumber('package')->name('premium.payment.checkout');

// NEW: Referral Wallet checkout, separate from SSLCommerz. Student auth and CSRF stay enabled.
Route::post('/premium-recordings/checkout/{package}/referral',
 [\App\Http\Controllers\Students\PremiumClass\ReferralPurchaseController::class,'checkout'])
 ->middleware(['auth:student','throttle:6,1'])
 ->whereNumber('package')->name('premium.payment.referral.checkout');

// SSLCommerz posts callbacks, which cannot carry Laravel CSRF tokens. Keep restricted to these 4 exact routes.
Route::post('/premium-recordings/payment/success',[\App\Http\Controllers\Students\PremiumClass\PremiumPaymentController::class,'success'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->name('premium.payment.success');
Route::post('/premium-recordings/payment/fail',[\App\Http\Controllers\Students\PremiumClass\PremiumPaymentController::class,'fail'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->name('premium.payment.fail');
Route::post('/premium-recordings/payment/cancel',[\App\Http\Controllers\Students\PremiumClass\PremiumPaymentController::class,'cancel'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->name('premium.payment.cancel');
Route::post('/premium-recordings/payment/ipn',[\App\Http\Controllers\Students\PremiumClass\PremiumPaymentController::class,'ipn'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->name('premium.payment.ipn');

Route::middleware('auth:student')->group(function () {
    Route::get('/premium-recordings/purchased/{order}/success',
        [\App\Http\Controllers\Students\PremiumClass\PremiumPurchaseLandingController::class, 'success'])
        ->whereNumber('order')->name('premium.payment.purchased.success');

    Route::get('/premium-recordings/purchased/{order}/videos',
        [\App\Http\Controllers\Students\PremiumClass\PremiumPurchaseLandingController::class, 'videos'])
        ->whereNumber('order')->name('premium.payment.purchased.videos');
});

// LEBD Fix116: logged-in student can report a PUBLISHED recorded video, even if premium-locked.
Route::post('/premium-recordings/video-complaints',
 [\App\Http\Controllers\Students\PremiumClass\VideoComplaintController::class,'store'])
 ->middleware(['auth:student','throttle:5,1'])->name('premium.recordings.complaints.store');
// Admin review; use the SAME authorization middleware as existing Premium Recording management.
Route::middleware(['auth',PremiumClassAdminOnly::class])->prefix('admin/premium-video-complaints')
 ->name('admin.premium-video-complaints.')->group(function(){
  Route::get('/',[\App\Http\Controllers\Admin\PremiumClass\VideoComplaintController::class,'index'])->name('index');
  Route::patch('/{complaint}',[\App\Http\Controllers\Admin\PremiumClass\VideoComplaintController::class,'update'])
   ->whereNumber('complaint')->name('update');
 });


// LEBD Video Class: purchased active premium packages, student only.
Route::get('/students/my-video-classes',
 [\App\Http\Controllers\Students\PremiumClass\StudentPurchasedVideoController::class,'index'])
 ->middleware('auth:student')->name('students.premium.video.classes');

// LEBD-05 Purchased Class Navigation. Existing public/catalog/player routes unchanged.
Route::middleware('auth:student')->prefix('students/video-class')->name('students.premium.video.')->group(function () {
    $ctrl=\App\Http\Controllers\Students\PremiumClass\StudentPurchasedVideoController::class;
    Route::get('/purchased/{order}',[$ctrl,'departments'])->whereNumber('order')->name('departments');
    Route::get('/purchased/{order}/subjects/{group}/{topic}',[$ctrl,'subjects'])->whereNumber('order')->where('group','department|non-department')->name('subjects');
    Route::get('/purchased/{order}/subject/{subject}/chapters',[$ctrl,'chapters'])->whereNumber('order')->whereNumber('subject')->name('chapters');
    Route::get('/purchased/{order}/chapter/{chapter}/videos',[$ctrl,'videos'])->whereNumber('order')->whereNumber('chapter')->name('videos');
    Route::get('/purchased/{order}/watch/{video}',[$ctrl,'watch'])->whereNumber('order')->whereNumber('video')->name('watch');
});
