<?php
// LEBD-05: independent admin Extra Student module. Include once from routes/web.php.
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExtraStudent\LebdExtraStudentController;

Route::middleware(['web', 'auth'])->prefix('admin/extra-students')->name('admin.extra-students.')->group(function () {
    // Existing routes — unchanged.
    Route::get('/', [LebdExtraStudentController::class, 'index'])->name('index');
    Route::get('/create', [LebdExtraStudentController::class, 'create'])->name('create');
    Route::post('/', [LebdExtraStudentController::class, 'store'])->name('store');

    // Edit and status update MUST be inside this group for the correct route names and prefix.
    Route::get('/{student}/edit', [LebdExtraStudentController::class, 'edit'])
        ->whereNumber('student')->name('edit');
    Route::put('/{student}', [LebdExtraStudentController::class, 'update'])
        ->whereNumber('student')->name('update');
});
