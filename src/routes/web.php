<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\NotificationController as SettingsNotificationController;
use App\Http\Controllers\Settings\UserController as SettingsUserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\TicketRatingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // ── Profile ──────────────────────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Tickets ───────────────────────────────────────────────────────
    Route::resource('tickets', TicketController::class);

    Route::post('tickets/{ticket}/messages',               [TicketMessageController::class, 'store']);
    Route::delete('tickets/{ticket}/messages/{message}',   [TicketMessageController::class, 'destroy']);

    Route::post('tickets/{ticket}/attachments',            [TicketMessageController::class, 'storeAttachment']);
    Route::delete('attachments/{attachment}',              [TicketMessageController::class, 'destroyAttachment']);
    Route::get('attachments/{attachment}/download',        [TicketMessageController::class, 'downloadAttachment'])
        ->name('attachments.download');

    Route::post('tickets/{ticket}/rating',   [TicketRatingController::class, 'store']);
    Route::delete('tickets/{ticket}/rating', [TicketRatingController::class, 'destroy']);

    // ── Settings (admin only) ─────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('users',               [SettingsUserController::class, 'index'])->name('users.index');
        Route::post('users',              [SettingsUserController::class, 'store'])->name('users.store');
        Route::put('users/{user}',        [SettingsUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle', [SettingsUserController::class, 'toggle'])->name('users.toggle');

        Route::get('notifications',       [SettingsNotificationController::class, 'index'])->name('notifications.index');
        Route::put('notifications',       [SettingsNotificationController::class, 'update'])->name('notifications.update');
    });
});

require __DIR__.'/auth.php';
