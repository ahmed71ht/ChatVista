<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // غرف المحادثة – القائمة والإنشاء
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');

    // غرفة محددة (باستخدام slug)
    Route::get('/chat/{room:slug}', [ChatController::class, 'show'])->name('chat.room');

    // الانضمام للغرف (عامة / محمية)
    Route::get('/chat/{room:slug}/join', [ChatController::class, 'joinForm'])->name('chat.join');
    Route::post('/chat/{room:slug}/join', [ChatController::class, 'join']);

    // طلب الانضمام للغرف الخاصة
    Route::post('/chat/{room:slug}/request-join', [ChatController::class, 'requestJoin'])->name('chat.request-join');

    // إدارة طلبات الانضمام (للمدير)
    Route::get('/chat/{room:slug}/requests', [ChatController::class, 'manageRequests'])->name('chat.manage-requests');
    Route::post('/chat/{room:slug}/requests/{joinRequest}/{action}', [ChatController::class, 'handleRequest'])->name('chat.handle-request');

    Route::get('/chat/{room:slug}/requests/check', [ChatController::class, 'checkRequests'])->name('chat.check-requests');

    Route::get('/chat/{room:slug}/system-messages/check', [ChatController::class, 'checkSystemMessages']);

    // مغادرة الغرفة
    Route::post('/chat/{room}/leave', [ChatController::class, 'leaveRoom'])->name('chat.leave');

    // الرسائل
    Route::post('/chat/{room:slug}/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/{room:slug}/messages/load', [ChatController::class, 'loadMessages'])->name('chat.load');

    // مسح الرسائل
    Route::delete('/messages/{message}', [ChatController::class, 'destroyMessage'])->name('chat.destroy-message');

    // التفاعلات
    Route::post('/chat/{room:slug}/poll-reactions', [ChatController::class, 'pollReactions'])->name('chat.poll-reactions');
    Route::post('/messages/{message}/reaction', [ChatController::class, 'toggleReaction'])->name('chat.reaction');

    // Seen By (مشاهدات الرسائل)
    Route::post('/messages/{message}/mark-view', [ChatController::class, 'markAsViewed'])->name('chat.mark-view');

    // تعديل الرسائل
    Route::post('/messages/{message}/update', [ChatController::class, 'updateMessage'])->name('messages.update');

    // تثبيت رسالة
    Route::post('/chat/{room:slug}/pin/{message}', [ChatController::class, 'pinMessage'])->name('chat.pin');

    // الإبلاغ عن رسالة
    Route::post('/messages/{message}/report', [ChatController::class, 'reportMessage'])->name('chat.report');
});

Route::get('/api/users/search', [ChatController::class, 'searchUsers'])->name('api.users.search');

Route::get('/api/rooms/{room}/unread', [ChatController::class, 'getUnreadCounts'])->name('api.rooms.unread');

Route::post('/chat/{room:slug}/mark-left', [ChatController::class, 'markLeft']);

require __DIR__.'/auth.php';