<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeSuccessController;
Route::middleware(['web','auth'])->prefix('admin/home-success')->name('admin.home-success.')->group(function () {
 Route::get('/',[HomeSuccessController::class,'index'])->name('index');
 Route::get('/create',[HomeSuccessController::class,'create'])->name('create');
 Route::post('/',[HomeSuccessController::class,'store'])->name('store');
 Route::get('/{success}/edit',[HomeSuccessController::class,'edit'])->name('edit');
 Route::put('/{success}',[HomeSuccessController::class,'update'])->name('update');
 Route::delete('/{success}',[HomeSuccessController::class,'destroy'])->name('destroy');
 Route::delete('/{success}/photo',[HomeSuccessController::class,'deletePhoto'])->name('delete-photo');
});
