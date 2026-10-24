<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoutineAutoQuestion\AutoQuestionController;
use App\Http\Controllers\Admin\RoutineAutoQuestion\BatchPaperSettingsController;
use App\Http\Controllers\Admin\RoutineAutoQuestion\ExamDepartmentDetailsController;
use App\Http\Controllers\Admin\RoutineAutoQuestion\ExamSettingsViewController;
use App\Http\Controllers\Admin\RoutineAutoQuestion\RoutineQuestionBuilderController;

Route::middleware(['web','auth'])->prefix('admin/routine-auto-question')
    ->name('admin.routine-auto-question.')->group(function () {
        Route::get('/', [AutoQuestionController::class, 'index'])->name('index');
        Route::get('/course/{courseId}', [AutoQuestionController::class, 'course'])->name('course');
        Route::get('/course/{courseId}/batch/{batchId}', [AutoQuestionController::class, 'batch'])->name('batch');
        Route::get('/course/{courseId}/batch/{batchId}/paper-settings-preview',
            [BatchPaperSettingsController::class, 'show'])->name('batch-settings-preview');
        Route::post('/course/{courseId}/batch/{batchId}/paper-settings-preview',
            [BatchPaperSettingsController::class,'save'])->name('batch-settings-save');
        Route::get('/course/{courseId}/batch/{batchId}/exam/{examId}/create-question',
            [RoutineQuestionBuilderController::class, 'show'])->name('create-question');
        Route::post('/course/{courseId}/batch/{batchId}/exam/{examId}/create-question', [RoutineQuestionBuilderController::class, 'save'])->name('question-plan-save');
        Route::post('/course/{courseId}/batch/{batchId}/exam/{examId}/reset-questions',
            [RoutineQuestionBuilderController::class, 'reset'])->name('question-plan-reset');
        Route::get('/course/{courseId}/batch/{batchId}/exam/{examId}/question-preview/{departmentId}', [RoutineQuestionBuilderController::class, 'preview'])->name('question-department-preview');
        Route::get('/course/{courseId}/batch/{batchId}/exam/{examId}/source/{source}', [RoutineQuestionBuilderController::class, 'extra'])->name('question-extra-source');
        Route::get('/course/{courseId}/batch/{batchId}/exam/{examId}/view-settings',
            [ExamSettingsViewController::class, 'show'])->name('exam-settings-view');
        Route::get('/course/{courseId}/batch/{batchId}/exam/{examId}/details',
            [ExamDepartmentDetailsController::class, 'show'])->name('exam-details');
        Route::get('/course/{courseId}/batch/{batchId}/exam/{examId}/department/{departmentId}/full-paper',
            [ExamDepartmentDetailsController::class, 'paper'])->name('department-paper');
    });
