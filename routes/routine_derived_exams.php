<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutineDerivedExamController;
Route::middleware('auth')->group(function () {
    Route::get('routine/{id}/derived-items', [RoutineDerivedExamController::class,'index'])->whereNumber('id')->name('routine.derived.list');
    Route::post('routine/{id}/derived-items/{type}', [RoutineDerivedExamController::class,'store'])->whereNumber('id')->whereIn('type',['revision','model'])->name('routine.derived.store');
    Route::get('routine/derived-item/{id}/edit', [RoutineDerivedExamController::class,'edit'])->whereNumber('id')->name('routine.derived.edit');
    Route::put('routine/derived-item/{id}', [RoutineDerivedExamController::class,'updateItem'])->whereNumber('id')->name('routine.derived.update');
    Route::delete('routine/derived-item/{id}', [RoutineDerivedExamController::class,'destroyItem'])->whereNumber('id')->name('routine.derived.destroy');
    Route::post('routine/derived-item/{id}/date', [RoutineDerivedExamController::class,'updateDate'])->whereNumber('id')->name('routine.derived.date');
});
