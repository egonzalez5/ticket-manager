<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\TicketRatingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::apiResource('tickets', TicketController::class);

    // Messages
    Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store']);
    Route::delete('tickets/{ticket}/messages/{message}', [TicketMessageController::class, 'destroy']);

    // Attachments
    Route::post('tickets/{ticket}/attachments', [TicketMessageController::class, 'storeAttachment']);
    Route::delete('attachments/{attachment}', [TicketMessageController::class, 'destroyAttachment']);
    Route::get('attachments/{attachment}/download', [TicketMessageController::class, 'downloadAttachment'])
        ->name('attachments.download');

    // Ratings
    Route::post('tickets/{ticket}/rating', [TicketRatingController::class, 'store']);
    Route::delete('tickets/{ticket}/rating', [TicketRatingController::class, 'destroy']);
});
