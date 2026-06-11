<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Notifications\ApplyProviderDeliveryEvent;
use App\DTO\Notifications\ProviderDeliveryEventData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderEventRequest;
use Illuminate\Http\JsonResponse;

class ProviderEventController extends Controller
{
    public function store(
        StoreProviderEventRequest $request,
        string $provider,
        ApplyProviderDeliveryEvent $applyProviderDeliveryEvent,
    ): JsonResponse {
        $event = $applyProviderDeliveryEvent->handle(
            ProviderDeliveryEventData::fromValidated($provider, $request->validated()),
        );

        return response()->json([
            'data' => [
                'id' => $event->id,
                'provider' => $event->provider,
                'provider_message_id' => $event->provider_message_id,
                'event_type' => $event->event_type,
                'processed_at' => $event->processed_at?->toISOString(),
            ],
        ], 202);
    }
}
