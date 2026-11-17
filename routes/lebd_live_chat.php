<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveChat\LiveChatVisitorController;
use App\Http\Controllers\LiveChat\LiveChatAdminController;

// Same-site session identifies each visitor; backend validates CSRF on POST requests.
Route::prefix('lebd-live-chat')->name('lebd.chat.')->group(function () {
    Route::get('messages', [LiveChatVisitorController::class, 'messages'])->name('messages');
    Route::post('send', [LiveChatVisitorController::class, 'send'])->middleware('throttle:30,1')->name('send');
});
Route::middleware('auth')->prefix('admin/live-chat')->name('admin.live-chat.')->group(function () {
    Route::get('/', [LiveChatAdminController::class, 'index'])->name('index');
    Route::get('conversations', [LiveChatAdminController::class, 'conversations'])->name('conversations');
    Route::get('{conversation}/messages', [LiveChatAdminController::class, 'messages'])->name('messages');
    Route::post('{conversation}/reply', [LiveChatAdminController::class, 'reply'])->middleware('throttle:90,1')->name('reply');
    Route::post('{conversation}/status', [LiveChatAdminController::class, 'status'])->name('status');
});
