<?php
// Include ONCE from routes/web.php using: require __DIR__.'/lebd_admin_orders.php';
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LebdOrdersDashboardController;
use App\Http\Controllers\Admin\McqStudyOrdersController;
use App\Http\Middleware\PremiumClass\PremiumClassAdminOnly;

Route::middleware(['auth', PremiumClassAdminOnly::class])
    ->prefix('admin/lebd-orders')->name('admin.lebd-orders.')->group(function () {
        Route::get('/',[LebdOrdersDashboardController::class,'index'])->name('index');
        Route::get('/overview',[\App\Http\Controllers\Admin\LebdOrdersOverviewController::class,'index'])->name('overview');
        Route::get('/batch',[LebdOrdersDashboardController::class,'batch'])->name('batch');
        Route::get('/e-book',[LebdOrdersDashboardController::class,'ebook'])->name('ebook');
        Route::get('/video',[LebdOrdersDashboardController::class,'video'])->name('video');
        Route::get('/video/{order}',[LebdOrdersDashboardController::class,'videoShow'])->whereNumber('order')->name('video.show');
        // Exam Section: admin-only read-only order reporting.
        Route::get('/exam', [\App\Http\Controllers\Admin\ExamSectionOrdersController::class, 'index'])->name('exam');
        Route::get('/exam/{order}', [\App\Http\Controllers\Admin\ExamSectionOrdersController::class, 'show'])->whereNumber('order')->name('exam.show');

    });

// Existing MCQ views/navigation expect admin.lebd.mcq-orders.* (not admin.lebd-orders.*).
// Keep the existing admin-only middleware and URL prefix, but set the matching name prefix.
Route::middleware(['auth', PremiumClassAdminOnly::class])
    ->prefix('admin/lebd-orders')->name('admin.lebd.mcq-orders.')->group(function () {
        Route::get('/mcq', [McqStudyOrdersController::class, 'index'])->name('index');
        Route::get('/mcq/{order}', [McqStudyOrdersController::class, 'show'])->whereNumber('order')->name('show');
    });
