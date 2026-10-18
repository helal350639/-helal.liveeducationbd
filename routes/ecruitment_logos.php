<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecruitmentLogoCtrl;
Route::middleware('auth')->prefix('admin/course/recruitment-logos')->name('course.recruitment-logos.')->group(function(){
 Route::get('/',[RecruitmentLogoCtrl::class,'index'])->name('index');
 Route::post('/',[RecruitmentLogoCtrl::class,'store'])->name('store');
 Route::put('/{id}',[RecruitmentLogoCtrl::class,'update'])->name('update');
});
