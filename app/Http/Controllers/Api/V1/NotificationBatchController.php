<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Notifications\CreateNotificationBatch;
use App\DTO\Notifications\CreateNotificationBatchData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationBatchRequest;
use App\Http\Resources\NotificationBatchResource;
use App\Models\NotificationBatch;
use App\Repositories\NotificationBatchRepository;
use Illuminate\Http\JsonResponse;

class NotificationBatchController extends Controller
{
    public function store(
        StoreNotificationBatchRequest $request,
        CreateNotificationBatch $createNotificationBatch,
    ): JsonResponse {
        $batch = $createNotificationBatch->handle(CreateNotificationBatchData::fromValidated($request->validated()));

        return (new NotificationBatchResource($batch))
            ->response()
            ->setStatusCode(202);
    }

    public function show(
        NotificationBatch $batch,
        NotificationBatchRepository $notificationBatches,
    ): NotificationBatchResource {
        return new NotificationBatchResource($notificationBatches->withNotificationsCount($batch));
    }
}
