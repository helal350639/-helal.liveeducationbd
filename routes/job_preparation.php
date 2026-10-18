<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MCQJobPreparation\JobPreparationController as JobPrep;

Route::middleware(['auth'])->prefix('admin/job-preparation')->name('admin.job-preparation.')->group(function () {
    Route::get('/{kind}', [JobPrep::class, 'categories'])->name('categories');
    Route::get('/{kind}/subjects/create', [JobPrep::class, 'createSubjectGlobal'])->name('subjects.create-global');
    Route::post('/{kind}/subjects/create', [JobPrep::class, 'storeSubjectGlobal'])->name('subjects.store-global');
    Route::get('/{kind}/chapters/create', [JobPrep::class, 'createChapterGlobal'])->name('chapters.create-global');
    Route::post('/{kind}/chapters/create', [JobPrep::class, 'storeChapterGlobal'])->name('chapters.store-global');
    Route::get('/{kind}/create', [JobPrep::class, 'createCategory'])->name('categories.create');
    Route::get('/{kind}/categories/{category}/edit', [JobPrep::class, 'editCategory'])->whereNumber('category')->name('categories.edit');
    Route::post('/{kind}', [JobPrep::class, 'storeCategory'])->name('categories.store');
    Route::get('/{kind}/categories/{category}/overview', [JobPrep::class, 'categoryOverview'])->whereNumber('category')->name('categories.overview');
    Route::put('/{kind}/categories/{category}', [JobPrep::class, 'updateCategory'])->name('categories.update');
    Route::delete('/{kind}/categories/{category}', [JobPrep::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/{kind}/categories/{category}/subjects', [JobPrep::class, 'subjects'])->name('subjects');
    Route::post('/{kind}/categories/{category}/subjects', [JobPrep::class, 'storeSubject'])->name('subjects.store');
    Route::put('/{kind}/categories/{category}/subjects/{subject}', [JobPrep::class, 'updateSubject'])->name('subjects.update');
    Route::delete('/{kind}/categories/{category}/subjects/{subject}', [JobPrep::class, 'destroySubject'])->name('subjects.destroy');
    Route::get('/{kind}/categories/{category}/subjects/{subject}/chapters', [JobPrep::class, 'chapters'])->name('chapters');
    Route::post('/{kind}/categories/{category}/subjects/{subject}/chapters', [JobPrep::class, 'storeChapter'])->name('chapters.store');
    Route::put('/{kind}/categories/{category}/subjects/{subject}/chapters/{chapter}', [JobPrep::class, 'updateChapter'])->name('chapters.update');
    Route::delete('/{kind}/categories/{category}/subjects/{subject}/chapters/{chapter}', [JobPrep::class, 'destroyChapter'])->name('chapters.destroy');
    Route::get('/{kind}/questions/{level}/{entity}', [JobPrep::class, 'questions'])->name('questions');
    Route::get('/{kind}/questions/{level}/{entity}/add', [JobPrep::class, 'picker'])->name('questions.picker');
    Route::post('/{kind}/questions/{level}/{entity}', [JobPrep::class, 'attach'])->name('questions.attach');
    Route::delete('/{kind}/questions/{level}/{entity}/{question}', [JobPrep::class, 'detach'])->name('questions.detach');
});

// Student-facing MCQ preparation. Loaded by the existing web.php require once.
Route::prefix('mcq-preparation')->name('student.jobprep.')->group(function () {

    Route::get('/{kind}/quiz/{level}/{entity}/create', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'quizCreate'])->name('quiz.create');
    Route::post('/{kind}/quiz/{level}/{entity}/start', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'quizStart'])->name('quiz.start');
    Route::get('/quiz/exam/{token}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'quizExam'])->name('quiz.exam');
    Route::post('/quiz/exam/{token}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'quizSubmit'])->name('quiz.submit');
    Route::get('/quiz/result/{token}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'quizResult'])->name('quiz.result');
    Route::post('/{kind}/questions/{level}/{entity}/reaction/{question}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'saveReaction'])->middleware('auth:student')->name('reaction.save');
    Route::post('/{kind}/questions/{level}/{entity}/rating/{question}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'saveRating'])->middleware('auth:student')->name('rating.save');
    Route::get('/{kind}/share/{level}/{entity}/{question}', [\App\Http\Controllers\MCQJobPreparation\QuestionShareController::class, 'show'])->name('share.show');
    Route::get('/{kind}/share/{level}/{entity}/{question}/card.jpg', [\App\Http\Controllers\MCQJobPreparation\QuestionShareController::class, 'image'])->name('share.image');
    Route::post('/{kind}/questions/{level}/{entity}/comment/{question}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'saveComment'])->middleware('auth:student')->name('comment.save');
    Route::get('/{kind}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'categories'])->name('categories');
    Route::get('/{kind}/category/{category}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'category'])->name('category');
    Route::get('/{kind}/category/{category}/subject/{subject}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'subject'])->name('subject');
    Route::get('/{kind}/category/{category}/subject/{subject}/chapter/{chapter}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'chapter'])->name('chapter');
    Route::get('/{kind}/questions/{level}/{entity}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'questions'])->name('questions');
    Route::post('/{kind}/questions/{level}/{entity}/reading/{question}', [\App\Http\Controllers\MCQJobPreparation\StudentJobPreparationController::class, 'saveReading'])->middleware('auth:student')->name('reading.save');
});
