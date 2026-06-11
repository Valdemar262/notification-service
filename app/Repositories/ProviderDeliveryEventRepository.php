<?php

namespace App\Repositories;

use App\DTO\Notifications\ProviderDeliveryEventData;
use App\Models\ProviderDeliveryEvent;

class ProviderDeliveryEventRepository
{
    public function findForUpdate(ProviderDeliveryEventData $data): ?ProviderDeliveryEvent
    {
        return ProviderDeliveryEvent::query()
            ->where('provider', $data->provider)
            ->where('provider_message_id', $data->providerMessageId)
            ->where('event_type', $data->eventType)
            ->lockForUpdate()
            ->first();
    }

    public function create(ProviderDeliveryEventData $data): ProviderDeliveryEvent
    {
        return ProviderDeliveryEvent::query()->create([
            'provider' => $data->provider,
            'provider_message_id' => $data->providerMessageId,
            'event_type' => $data->eventType,
            'payload' => $data->payload,
        ]);
    }

    public function markProcessed(ProviderDeliveryEvent $event): ProviderDeliveryEvent
    {
        $event->forceFill(['processed_at' => now()])->save();

        return $event->refresh();
    }
}
