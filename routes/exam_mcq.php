<?php
// Fix62: Safe category/subject/chapter edit-delete management; exact route before generic routes.
Route::middleware(['web','auth'])->prefix('admin/mcq-for-exam/manage')->name('admin.exam-mcq.manage.')->group(function () {
    Route::get('/{kind}', [\App\Http\Controllers\Admin\ExamMcqManagementController::class, 'index'])->name('index');
    Route::patch('/{kind}/{level}/{id}', [\App\Http\Controllers\Admin\ExamMcqManagementController::class, 'update'])->name('update');
    Route::delete('/{kind}/{level}/{id}', [\App\Http\Controllers\Admin\ExamMcqManagementController::class, 'destroy'])->name('destroy');
});

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamMcqController;
use App\Http\Controllers\RoutineMcqSourceController;

// Fix68: Dedicated Chapter page; existing MCQ routes remain unchanged.
Route::middleware(['web','auth'])->get('admin/mcq-for-exam/{kind}/{categoryId}/subject/{subjectId}/chapters',
    [\App\Http\Controllers\Admin\ExamMcqChapterPageController::class, 'show'])
    ->where(['kind'=>'department|non-department','categoryId'=>'[0-9]+','subjectId'=>'[0-9]+'])
    ->name('admin.exam-mcq.subject.chapters');

// Existing MCQ for Exam routes: preserved.
Route::middleware(['web','auth'])->prefix('admin/mcq-for-exam')->name('admin.exam-mcq.')->group(function(){
 Route::get('/{kind}',[ExamMcqController::class,'index'])->name('index');
 Route::post('/{kind}/categories',[ExamMcqController::class,'addCategory'])->name('categories.store');
 Route::get('/{kind}/{categoryId}',[ExamMcqController::class,'detail'])->name('detail');
 Route::post('/{kind}/{categoryId}/subjects',[ExamMcqController::class,'addSubject'])->name('subjects.store');
 Route::post('/{kind}/{categoryId}/subjects/{subjectId}/chapters',[ExamMcqController::class,'addChapter'])->name('chapters.store');
 Route::get('/{kind}/{categoryId}/picker',[ExamMcqController::class,'picker'])->name('picker');
 Route::post('/{kind}/{categoryId}/questions',[ExamMcqController::class,'attach'])->name('attach');
 Route::get('/{kind}/{categoryId}/questions',[ExamMcqController::class,'questions'])->name('questions');
 Route::delete('/{kind}/{categoryId}/questions/{linkId}',[ExamMcqController::class,'detach'])->name('detach');
 Route::put('/{kind}/{categoryId}/{level}/{id}',[ExamMcqController::class,'updateEntity'])->name('entity.update');
 Route::delete('/{kind}/{categoryId}/{level}/{id}',[ExamMcqController::class,'deleteEntity'])->name('entity.delete');
});

// LEBD-04 Routine's read-only MCQ source filter.
// Defined outside the generic /{kind} group to avoid routing ambiguity.
Route::middleware(['web','auth'])
    ->get('/admin/routine-mcq/options', [RoutineMcqSourceController::class, 'options'])
    ->name('routine.mcq.options');

// LEBD-04 Routine MCQ output; authenticated preview and browser Save as PDF.
Route::middleware(['web','auth'])->get('/routine/item/{id}/mcq-output', [\App\Http\Controllers\RoutineMcqOutputController::class, 'preview'])->whereNumber('id')->name('routine.mcq.preview');
