<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\ExamSection\StudentExamCategoryController;

// Explicit non-overlapping browser paths; avoid old /student/{id} route and query routing.
Route::get('/lebd-exam-browse/category/{category}', [StudentExamCategoryController::class, 'category'])
    ->whereNumber('category')->name('student.exam-section.browse-category');
Route::get('/lebd-exam-browse/category/{category}/exams', [StudentExamCategoryController::class, 'exams'])
    ->whereNumber('category')->name('student.exam-section.browse-exams');
Route::prefix('lebd-student-exams')->name('student.exam-section.')->group(function () {
    Route::get('/', [StudentExamCategoryController::class, 'categories'])->name('categories');
    Route::get('/category/{category}', [StudentExamCategoryController::class, 'category'])->whereNumber('category')->name('category');
    Route::get('/category/{category}/exams', [StudentExamCategoryController::class, 'exams'])->whereNumber('category')->name('exams');
});

// Fix204: separate student exam-taking endpoints; never route through legacy paper IDs.
Route::middleware('auth:student')->group(function () {
    Route::get('/lebd-exam-take/{item}', [\App\Http\Controllers\Students\ExamSection\StudentRoutineExamController::class, 'show'])
        ->whereNumber('item')->name('student.exam-section.take');
    Route::post('/lebd-exam-take/{item}', [\App\Http\Controllers\Students\ExamSection\StudentRoutineExamController::class, 'submit'])
        ->whereNumber('item')->name('student.exam-section.take.submit');
    Route::get('/lebd-exam-take/{item}/result', [\App\Http\Controllers\Students\ExamSection\StudentRoutineExamController::class, 'result'])
        ->whereNumber('item')->name('student.exam-section.take.result');
});

// Fix207: the four student exam menus use the NEW Exam Section's routine/attempt data.
Route::get('/lebd-exam-menu/{category}/{section}', [\App\Http\Controllers\Students\ExamSection\StudentRoutineExamController::class, 'overview'])
    ->whereNumber('category')->whereIn('section', ['routine','results','papers','missing'])
    ->name('student.exam-section.menu');

// Fix214: all exam-category pages link to this public price-list page.
Route::get('/lebd-exam-packages', [\App\Http\Controllers\Students\ExamSection\ExamSectionPackageController::class, 'index'])->name('student.exam-section.packages');

// Fix217: Exam Section package checkout. Student login is required for review and purchase.
Route::middleware('auth:student')->group(function () {
    Route::get('/lebd-exam-packages/{package}/review', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'review'])
        ->whereNumber('package')->name('student.exam-section.package.review');
    Route::get('/lebd-exam-packages/{package}/checkout-review', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'checkoutResume'])
        ->whereNumber('package')->name('student.exam-section.package.checkout.resume');
    Route::post('/lebd-exam-packages/{package}/checkout-review', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'checkoutPage'])
        ->whereNumber('package')->name('student.exam-section.package.checkout.review');
    Route::post('/lebd-exam-packages/{package}/checkout', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'pay'])
        ->whereNumber('package')->name('student.exam-section.package.checkout');
});
// SSLCommerz server/browser callback routes (server-to-server IPN cannot carry a CSRF token).
Route::post('/lebd-exam-payment/success', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'success'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->name('student.exam-section.payment.success');
Route::post('/lebd-exam-payment/fail', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'fail'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->name('student.exam-section.payment.fail');
Route::post('/lebd-exam-payment/cancel', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'cancel'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->name('student.exam-section.payment.cancel');
Route::post('/lebd-exam-payment/ipn', [\App\Http\Controllers\Students\ExamSection\ExamSectionCheckoutController::class,'ipn'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->name('student.exam-section.payment.ipn');
