<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamActivity\AutoExamOverviewController;
use App\Http\Controllers\Admin\ExamActivity\StudyQuestionPdfController;

// LEBD-05: Admin Exam Activity Routes
Route::middleware(['auth'])->group(function () {

    // Existing Auto Exams — DO NOT CHANGE
    Route::get(
        '/admin/exam-activity/auto-exams',
        [AutoExamOverviewController::class, 'index']
    )->name('admin.exam-activity.auto-exams');

    // NEW: Admin Study PDF
    Route::get(
        '/admin/exam-activity/study-pdf/exam-section/{routine}/{item}',
        [StudyQuestionPdfController::class, 'examSection']
    )
    ->whereNumber('routine')
    ->whereNumber('item')
    ->name('admin.exam-activity.study-pdf');

});