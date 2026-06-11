<?php

namespace App\Actions\Notifications;

use App\DTO\Notifications\NotificationStatusTransitionData;
use App\DTO\Notifications\ProviderDeliveryEventData;
use App\Enums\NotificationStatus;
use App\Models\ProviderDeliveryEvent;
use App\Repositories\NotificationRepository;
use App\Repositories\ProviderDeliveryEventRepository;
use Illuminate\Support\Facades\DB;

readonly class ApplyProviderDeliveryEvent
{
    public function __construct(
        private RecordNotificationStatus $recordNotificationStatus,
        private NotificationRepository $notifications,
        private ProviderDeliveryEventRepository $providerDeliveryEvents,
    ) {}

    public function handle(ProviderDeliveryEventData $data): ProviderDeliveryEvent
    {
        return DB::transaction(function () use ($data): ProviderDeliveryEvent {
            $event = $this->providerDeliveryEvents->findForUpdate($data);

            if ($event instanceof ProviderDeliveryEvent) {
                return $event;
            }

            $event = $this->providerDeliveryEvents->create($data);

            $notification = $this->notifications->findByProviderMessageOrFail($data->provider, $data->providerMessageId);

            if ($data->eventType === NotificationStatus::Delivered->value
                && $notification->status === NotificationStatus::Sent) {
                $this->recordNotificationStatus->transition(
                    $notification,
                    new NotificationStatusTransitionData(
                        toStatus: NotificationStatus::Delivered,
                        reason: 'provider_delivered',
                        context: $data->payload,
                    ),
                );
            }

            if ($data->eventType === NotificationStatus::Dropped->value
                && in_array($notification->status, [NotificationStatus::Queued, NotificationStatus::Sent], true)) {
                $this->recordNotificationStatus->transition(
                    $notification,
                    new NotificationStatusTransitionData(
                        toStatus: NotificationStatus::Dropped,
                        reason: 'provider_dropped',
                        context: $data->payload,
                    ),
                );
            }

            return $this->providerDeliveryEvents->markProcessed($event);
        });
    }
}
