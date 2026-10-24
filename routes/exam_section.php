<?php
// Dedicated exam-section module. No legacy routine/auto-question routes touched.
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamSection\ExamSectionController;
use App\Http\Controllers\Admin\ExamSection\ExamRoutineController;
use App\Http\Controllers\Admin\ExamSection\ExamPackageController;
Route::middleware(['auth'])->prefix('admin/exam-section')->name('admin.exam-section.')->group(function () {
 Route::get('/categories',[ExamSectionController::class,'category'])->name('categories');
 Route::post('/categories',[ExamSectionController::class,'storeCategory'])->name('categories.store');
 Route::put('/categories/{id}',[ExamSectionController::class,'updateCategory'])->name('categories.update');
 Route::delete('/categories/{id}',[ExamSectionController::class,'deleteCategory'])->name('categories.delete');
 Route::get('/subcategories',[ExamSectionController::class,'subcategory'])->name('subcategories');
 Route::post('/subcategories',[ExamSectionController::class,'storeSubcategory'])->name('subcategories.store');
 Route::put('/subcategories/{id}',[ExamSectionController::class,'updateSubcategory'])->name('subcategories.update');
 Route::delete('/subcategories/{id}',[ExamSectionController::class,'deleteSubcategory'])->name('subcategories.delete');
 Route::get('/routine-topics',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'landing'])->name('routine-topics');
 Route::get('/routine',[ExamRoutineController::class,'categories'])->name('routine');
 Route::get('/routine/category/{category}',[ExamRoutineController::class,'category'])->name('routine.category');
 Route::get('/routine/category/{category}/list',[ExamRoutineController::class,'index'])->name('routine.list');
 Route::get('/routine/create',[ExamRoutineController::class,'create'])->name('routine.create');
 Route::post('/routine/create',[ExamRoutineController::class,'store'])->name('routine.store');
 Route::get('/routine/{routine}/edit',[ExamRoutineController::class,'edit'])->name('routine.edit');
 Route::put('/routine/{routine}',[ExamRoutineController::class,'update'])->name('routine.update');
 Route::delete('/routine/{routine}',[ExamRoutineController::class,'destroy'])->name('routine.delete');
 Route::get('/routine/{routine}/items',[ExamRoutineController::class,'items'])->name('routine.items');
 Route::post('/routine/{routine}/items',[ExamRoutineController::class,'storeItem'])->name('routine.items.store');
 Route::put('/routine/{routine}/items/{item}',[ExamRoutineController::class,'updateItem'])->name('routine.items.update');
 Route::delete('/routine/{routine}/items/{item}',[ExamRoutineController::class,'deleteItem'])->name('routine.items.delete');
 Route::get('/student-enrollments/{category}',[\App\Http\Controllers\Admin\ExamSection\ExamStudentEnrollmentController::class,'index'])->name('student-enrollments');
 Route::post('/student-enrollments/{category}',[\App\Http\Controllers\Admin\ExamSection\ExamStudentEnrollmentController::class,'store'])->name('student-enrollments.store');
 Route::delete('/student-enrollments/{category}/{enrollment}',[\App\Http\Controllers\Admin\ExamSection\ExamStudentEnrollmentController::class,'destroy'])->name('student-enrollments.delete');
 Route::get('/auto-question',[ExamSectionController::class,'autoQuestion'])->name('auto-question');
 Route::get('/auto-question/category/{category}/subcategories',[ExamSectionController::class,'autoQuestionSubcategories'])->name('auto-question.subcategories');
 Route::get('/auto-question/category/{category}/exams',[ExamSectionController::class,'autoQuestionExams'])->name('auto-question.exams');
 Route::get('/auto-question/category/{category}/paper-settings',[ExamSectionController::class,'paperSettings'])->name('auto-question.paper-settings');
 Route::post('/auto-question/category/{category}/paper-settings',[ExamSectionController::class,'savePaperSettings'])->name('auto-question.paper-settings.save');
 Route::get('/auto-question/category/{category}/routine/{routine}/create-question',[\App\Http\Controllers\Admin\ExamSection\AutoQuestionCreateController::class,'create'])->name('auto-question.create');
 Route::post('/auto-question/category/{category}/routine/{routine}/create-question',[\App\Http\Controllers\Admin\ExamSection\AutoQuestionCreateController::class,'store'])->name('auto-question.create.save');
 Route::get('/auto-question/category/{category}/routine/{routine}/question-details',[\App\Http\Controllers\Admin\ExamSection\AutoQuestionCreateController::class,'details'])->name('auto-question.details');
 Route::post('/auto-question/category/{category}/routine/{routine}/reset-questions',[\App\Http\Controllers\Admin\ExamSection\AutoQuestionCreateController::class,'reset'])->name('auto-question.reset');
 Route::get('/packages',[ExamPackageController::class,'index'])->name('packages');
 Route::get('/packages/create',[ExamPackageController::class,'create'])->name('packages.create');
 Route::post('/packages',[ExamPackageController::class,'store'])->name('packages.store');
 Route::get('/packages/{package}/edit',[ExamPackageController::class,'edit'])->name('packages.edit');
 Route::put('/packages/{package}',[ExamPackageController::class,'update'])->name('packages.update');
 Route::delete('/packages/{package}',[ExamPackageController::class,'destroy'])->name('packages.delete');
 // Fix143: standalone Department > Subject > Chapter topic management and main Question IDs.
 Route::get('/routine-topics/{level}',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'index'])->where('level','department|subject|chapter')->name('routine-topic.index');
 Route::post('/routine-topics/{level}',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'store'])->where('level','department|subject|chapter')->name('routine-topic.store');
 Route::put('/routine-topics/{level}/{id}',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'update'])->where('level','department|subject|chapter')->name('routine-topic.update');
 Route::delete('/routine-topics/{level}/{id}',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'delete'])->where('level','department|subject|chapter')->name('routine-topic.delete');
 Route::get('/routine-topics/{level}/{id}/questions',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'picker'])->where('level','department|subject|chapter')->name('routine-topic.questions');
 Route::post('/routine-topics/{level}/{id}/questions',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'attach'])->where('level','department|subject|chapter')->name('routine-topic.questions.attach');
 Route::delete('/routine-topics/{level}/{id}/questions/{questionId}',[\App\Http\Controllers\Admin\ExamSection\RoutineTopicController::class,'detach'])->where('level','department|subject|chapter')->name('routine-topic.questions.detach');
});

// Register student exam browsing routes after the admin exam-section routes.
require_once __DIR__ . '/student_exam_section.php';
