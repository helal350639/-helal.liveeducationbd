<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\ExamSection\StudentExamCategoryController;

// Student-only Wrong & Unanswered review, scoped to the currently opened Category / Sub-category.
Route::middleware('auth:student')->group(function () {
    Route::get('/lebd-exam-wrong-unanswered/{category}', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredController::class, 'index'])
        ->whereNumber('category')->name('student.exam-section.wrong-unanswered');
    Route::get('/lebd-exam-wrong-unanswered/{category}/exam/{item}', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredController::class, 'show'])
        ->whereNumber('category')->whereNumber('item')->name('student.exam-section.wrong-unanswered.show');
});

// Student-only Merit List, ranked from published/submitted Exam Section results.
Route::middleware('auth:student')->get('/lebd-exam-merit-list/{category}/{item?}', [\App\Http\Controllers\Students\ExamSection\StudentMeritListController::class, 'index'])
    ->whereNumber('category')->whereNumber('item')->name('student.exam-section.merit-list');

// Student-only Exam Mark Progress report (read-only; uses submitted Exam Section attempts).
Route::middleware('auth:student')->get('/lebd-exam-mark-progress', [\App\Http\Controllers\Students\ExamSection\ExamMarkProgressController::class, 'index'])
    ->name('student.exam-section.mark-progress');

// Explicit non-overlapping browser paths; avoid old /student/{id} route and query routing.
Route::get('/lebd-exam-browse/category/{category}', [StudentExamCategoryController::class, 'category'])
    ->whereNumber('category')->name('student.exam-section.browse-category');
Route::get('/lebd-exam-browse/category/{category}/exams', [StudentExamCategoryController::class, 'exams'])
    ->whereNumber('category')->name('student.exam-section.browse-exams');

// Shared Routine Exam PDF: the first student creates one file; all students view/download that same file.
Route::get('/browse/category/{category}/exam-pdf', [\App\Http\Controllers\Students\ExamSection\StudentExamPdfController::class, 'index'])
    ->whereNumber('category')->name('student.exam-section.exam-pdfs');
Route::middleware('auth')->post('/browse/category/{category}/exam-pdf/{item}/reset', [\App\Http\Controllers\Students\ExamSection\StudentExamPdfController::class, 'resetSharedPdf'])
    ->whereNumber('category')->whereNumber('item')->name('student.exam-section.exam-pdf.reset');

Route::middleware('auth:student')->group(function () {
    Route::post('/browse/category/{category}/exam-pdf/{item}/create', [\App\Http\Controllers\Students\ExamSection\StudentExamPdfController::class, 'build'])
        ->whereNumber('category')->whereNumber('item')->name('student.exam-section.exam-pdf.build');
    Route::get('/browse/category/{category}/exam-pdf/{item}/view', [\App\Http\Controllers\Students\ExamSection\StudentExamPdfController::class, 'view'])
        ->whereNumber('category')->whereNumber('item')->name('student.exam-section.exam-pdf.view');
    Route::get('/browse/category/{category}/exam-pdf/{item}/download', [\App\Http\Controllers\Students\ExamSection\StudentExamPdfController::class, 'download'])
        ->whereNumber('category')->whereNumber('item')->name('student.exam-section.exam-pdf.download');
});
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

// Practice quizzes built only from this student's wrong / unanswered questions in the active scope.
Route::middleware('auth:student')->group(function () {
    Route::get('/lebd-exam-wrong-unanswered/{category}/make-quiz', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredQuizController::class, 'setup'])
        ->whereNumber('category')->name('student.exam-section.wrong-unanswered.quiz.setup');
    Route::post('/lebd-exam-wrong-unanswered/{category}/make-quiz', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredQuizController::class, 'start'])
        ->whereNumber('category')->name('student.exam-section.wrong-unanswered.quiz.start');
    Route::get('/lebd-exam-wrong-unanswered/{category}/make-quiz/{quiz}', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredQuizController::class, 'take'])
        ->whereNumber('category')->whereNumber('quiz')->name('student.exam-section.wrong-unanswered.quiz.take');
    Route::post('/lebd-exam-wrong-unanswered/{category}/make-quiz/{quiz}', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredQuizController::class, 'submit'])
        ->whereNumber('category')->whereNumber('quiz')->name('student.exam-section.wrong-unanswered.quiz.submit');
    Route::get('/lebd-exam-wrong-unanswered/{category}/make-quiz/{quiz}/result', [\App\Http\Controllers\Students\ExamSection\StudentWrongUnansweredQuizController::class, 'result'])
        ->whereNumber('category')->whereNumber('quiz')->name('student.exam-section.wrong-unanswered.quiz.result');
});
