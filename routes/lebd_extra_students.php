<?php
// LEBD-05: independent admin Extra Student module. Include once from routes/web.php.
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExtraStudent\LebdExtraStudentController;
Route::middleware(['web','auth'])->prefix('admin/extra-students')->name('admin.extra-students.')->group(function () {
    Route::get('/', [LebdExtraStudentController::class, 'index'])->name('index');
    Route::get('/create', [LebdExtraStudentController::class, 'create'])->name('create');
    Route::post('/', [LebdExtraStudentController::class, 'store'])->name('store');
});
