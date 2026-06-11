<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'batch_id' => $this->whenLoaded('batch', fn () => $this->batch->uuid),
            'subscriber_external_id' => $this->whenLoaded('subscriber', fn () => $this->subscriber->external_id),
            'channel' => $this->channel->value,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'recipient_address' => $this->recipient_address,
            'provider' => $this->provider,
            'provider_message_id' => $this->provider_message_id,
            'attempts' => $this->attempts,
            'sent_at' => $this->sent_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'dropped_at' => $this->dropped_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'status_events' => NotificationStatusEventResource::collection($this->whenLoaded('statusEvents')),
        ];
    }
}
