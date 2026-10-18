<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EBook\EBookController;
use App\Http\Controllers\EBook\StudentPremiumController;
use App\Http\Controllers\EBook\EBookPackageController;

Route::middleware('auth')->prefix('admin/e-book/packages')->name('admin.ebook-packages.')->group(function () {
 Route::get('/',[EBookPackageController::class,'index'])->name('index');
 Route::get('/create',[EBookPackageController::class,'form'])->name('create');
 Route::post('/',[EBookPackageController::class,'save'])->name('store');
 Route::get('/{package}/edit',[EBookPackageController::class,'form'])->name('edit');
 Route::put('/{package}',[EBookPackageController::class,'save'])->name('update');
 Route::delete('/{package}',[EBookPackageController::class,'delete'])->name('delete');
});
Route::middleware('auth')->prefix('admin/e-book')->name('admin.ebook.')->group(function () {
 Route::get('/{kind}',[EBookController::class,'categories'])->name('categories');
 Route::post('/{kind}/categories',[EBookController::class,'storeCategory'])->name('categories.store');
 Route::put('/{kind}/categories/{category}',[EBookController::class,'updateCategory'])->name('categories.update');
 Route::delete('/{kind}/categories/{category}',[EBookController::class,'deleteCategory'])->name('categories.delete');
 Route::get('/{kind}/categories/{category}/full-book',[EBookController::class,'fullBook'])->name('full-book');
 Route::get('/{kind}/categories/{category}',[EBookController::class,'chapters'])->name('chapters');
 Route::post('/{kind}/categories/{category}/chapters',[EBookController::class,'storeChapter'])->name('chapters.store');
 Route::put('/{kind}/categories/{category}/chapters/{chapter}',[EBookController::class,'updateChapter'])->name('chapters.update');
 Route::delete('/{kind}/categories/{category}/chapters/{chapter}',[EBookController::class,'deleteChapter'])->name('chapters.delete');
 Route::get('/{kind}/chapters/{chapter}/edit',[EBookController::class,'editor'])->name('editor');
 Route::post('/{kind}/chapters/{chapter}/blocks',[EBookController::class,'addBlock'])->name('blocks.store');
 Route::put('/{kind}/chapters/{chapter}/blocks/{block}',[EBookController::class,'updateBlock'])->name('blocks.update');
 Route::delete('/{kind}/chapters/{chapter}/blocks/{block}',[EBookController::class,'deleteBlock'])->name('blocks.delete');
 Route::post('/{kind}/chapters/{chapter}/blocks/{block}/move',[EBookController::class,'moveBlock'])->name('blocks.move');
 Route::get('/{kind}/chapters/{chapter}/picker',[EBookController::class,'picker'])->name('picker');
 Route::post('/{kind}/chapters/{chapter}/attach',[EBookController::class,'attachQuestions'])->name('attach');
 Route::get('/{kind}/chapters/{chapter}/preview',[EBookController::class,'adminPreview'])->name('preview');
});
Route::prefix('e-books')->name('student.ebook.')->group(function () {
 Route::get('/{kind}/{category}/packages',[EBookPackageController::class,'catalog'])->name('packages');
 Route::get('/{kind}',[StudentPremiumController::class,'categories'])->name('categories');
 Route::get('/{kind}/{category}/full-book',[StudentPremiumController::class,'fullBook'])->name('full-book');
 Route::get('/{kind}/{category}',[StudentPremiumController::class,'chapters'])->name('chapters');
 Route::get('/{kind}/{category}/{chapter}',[StudentPremiumController::class,'read'])->name('read');
});


// E-Book premium SSLCommerz: isolated from MCQ and old Batch/Course gateway callbacks.
Route::post('e-books/{kind}/{category}/packages/{package}/pay',
 [\App\Http\Controllers\EBook\EBookPaymentController::class,'pay'])
 ->middleware('auth:student')->where(['kind'=>'department|non-department','category'=>'[0-9]+','package'=>'[0-9]+'])
 ->name('lebd.ebook.payment.start');
foreach(['success','fail','cancel','ipn'] as $event){
 Route::post('payments/e-book/'.$event,[\App\Http\Controllers\EBook\EBookPaymentController::class,'callback'])
  ->defaults('event',$event)
  ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
  ->name('lebd.ebook.payment.'.$event);
}
