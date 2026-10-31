<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\MyBatchExamIndexController;
use App\Http\Controllers\Students\BatchRoutineViewController;
use App\Http\Controllers\Students\AllBatchExamPaperController;

// LEBD-05 My Batch routes: retain existing URL, name and student guard.
Route::middleware('auth:student')->get('/students/my-batch/exams', [MyBatchExamIndexController::class, 'index'])
    ->name('students.my-batch.exam-index');

// Published Routine for the student-owned batch; read-only, same route name and URI.
Route::middleware(['web', 'auth:student'])->get('/students/my-batch/{batchId}/routine', [BatchRoutineViewController::class, 'show'])
    ->whereNumber('batchId')->name('students.batch.routine-live');

// Preserve STEP-6 Routine Auto Exam routes (existing file remains unchanged).
require_once __DIR__ . '/lebd_routine_auto_student.php';

// LEBD-05 STEP-10: all scheduled routine exams for the verified purchased department.
Route::middleware(['web', 'auth:student'])
    ->get('/students/my-batch/{batchId}/all-exam-papers', [AllBatchExamPaperController::class, 'index'])
    ->whereNumber('batchId')->name('students.batch.all-exam-papers');

// STEP-37: Result list uses the same purchased-batch exam listing controller/view.
Route::middleware(['web', 'auth:student'])
    ->get('/students/my-batch/{batchId}/exam-results', [AllBatchExamPaperController::class, 'index'])
    ->whereNumber('batchId')->name('students.batch.exam-results');

// STEP-38: Missing exams — same purchased-batch/department security as All Exam Paper.
Route::middleware(['web', 'auth:student'])
    ->get('/students/my-batch/{batchId}/missing-exams', [AllBatchExamPaperController::class, 'index'])
    ->whereNumber('batchId')->name('students.batch.missing-exams');
