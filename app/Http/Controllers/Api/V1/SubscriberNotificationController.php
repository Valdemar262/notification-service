<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Notifications\SubscriberNotificationHistoryFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriberNotificationHistoryRequest;
use App\Http\Resources\NotificationResource;
use App\Repositories\NotificationRepository;
use App\Repositories\SubscriberRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriberNotificationController extends Controller
{
    public function index(
        SubscriberNotificationHistoryRequest $request,
        string $externalId,
        SubscriberRepository $subscribers,
        NotificationRepository $notifications,
    ): AnonymousResourceCollection {
        $subscriber = $subscribers->findByExternalIdOrFail($externalId);

        $notifications = $notifications->paginateForSubscriber(
            $subscriber,
            SubscriberNotificationHistoryFilters::fromValidated($request->validated()),
        );

        return NotificationResource::collection($notifications);
    }
}
