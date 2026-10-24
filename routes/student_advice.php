<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StudentAdvice\StudentAdviceController;
Route::middleware(['auth'])->prefix('admin/student-advice')->name('admin.student-advice.')->group(function(){
 Route::get('/',[StudentAdviceController::class,'index'])->name('index');
 Route::post('/',[StudentAdviceController::class,'store'])->name('store');
 Route::put('/{advice}',[StudentAdviceController::class,'update'])->name('update');
 Route::delete('/{advice}',[StudentAdviceController::class,'destroy'])->name('destroy');
});

Route::get('/student-advice',function(){
 abort_unless(\Illuminate\Support\Facades\Schema::hasTable('lebd_student_advices'),404);
 $items=\App\Models\StudentAdvice\StudentAdvice::where('status','Published')->orderBy('sort_order')->orderByDesc('id')->paginate(12);
 return view('student-advice.index',compact('items'));
})->name('student.advice.index');
