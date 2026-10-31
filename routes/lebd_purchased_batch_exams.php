<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\PurchasedBatchExamsController;
Route::middleware(['web', 'auth:student'])->get('/students/my-batch/{batch}/exams', [PurchasedBatchExamsController::class, 'index'])->whereNumber('batch')->name('students.batch.purchased-exams');
