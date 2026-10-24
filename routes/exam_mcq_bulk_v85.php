<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamMcqBulkController;

Route::middleware(['web','auth'])->post(
    'admin/mcq-for-exam/{kind}/{categoryId}/bulk-add-v85',
    [ExamMcqBulkController::class,'attach']
)->name('admin.exam-mcq.bulk-v85');
