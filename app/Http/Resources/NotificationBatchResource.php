<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationBatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'idempotency_key' => $this->idempotency_key,
            'initiator' => $this->initiator,
            'channel' => $this->channel->value,
            'priority' => $this->priority->value,
            'message' => $this->message,
            'counters' => [
                'requested_recipients' => $this->requested_recipients_count,
                'accepted_notifications' => $this->accepted_notifications_count,
                'queued' => $this->queued_count,
                'sent' => $this->sent_count,
                'delivered' => $this->delivered_count,
                'dropped' => $this->dropped_count,
            ],
            'notifications_count' => $this->whenCounted('notifications'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
