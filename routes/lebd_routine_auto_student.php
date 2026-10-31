<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\RoutineAutoStudentExamController;

Route::middleware('auth:student')->prefix('students/routine-auto-exam')
    ->name('students.routine-auto.')->group(function () {
        Route::get('{courseId}/{batchId}/{examId}/{departmentId}', [RoutineAutoStudentExamController::class, 'info'])->name('info');
        Route::post('{courseId}/{batchId}/{examId}/{departmentId}/start', [RoutineAutoStudentExamController::class, 'start'])->name('start');
        Route::get('{courseId}/{batchId}/{examId}/{departmentId}/attempt', [RoutineAutoStudentExamController::class, 'attempt'])->name('attempt');
        Route::post('{courseId}/{batchId}/{examId}/{departmentId}/submit', [RoutineAutoStudentExamController::class, 'submit'])->name('submit');
        Route::get('{courseId}/{batchId}/{examId}/{departmentId}/result', [RoutineAutoStudentExamController::class, 'result'])->name('result');
    });
