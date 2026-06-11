<?php

use App\Http\Controllers\Api\V1\NotificationBatchController;
use App\Http\Controllers\Api\V1\ProviderEventController;
use App\Http\Controllers\Api\V1\SubscriberNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('notification-batches', [NotificationBatchController::class, 'store'])
        ->name('notification-batches.store');
    Route::get('notification-batches/{batch}', [NotificationBatchController::class, 'show'])
        ->name('notification-batches.show');
    Route::get('subscribers/{externalId}/notifications', [SubscriberNotificationController::class, 'index'])
        ->name('subscribers.notifications.index');
    Route::post('provider-events/{provider}', [ProviderEventController::class, 'store'])
        ->name('provider-events.store');
});
