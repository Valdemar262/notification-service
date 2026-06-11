<?php

namespace App\DTO\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use Illuminate\Support\Arr;

readonly class CreateNotificationBatchData
{
    /**
     * @param  array<int, string>  $recipientIds
     */
    public function __construct(
        public string $idempotencyKey,
        public NotificationChannel $channel,
        public NotificationPriority $priority,
        public string $message,
        public array $recipientIds,
        public ?string $initiator,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromValidated(array $payload): self
    {
        return new self(
            idempotencyKey: (string) $payload['idempotency_key'],
            channel: NotificationChannel::from((string) $payload['channel']),
            priority: NotificationPriority::from((string) $payload['priority']),
            message: (string) $payload['message'],
            recipientIds: array_values(array_unique(Arr::wrap($payload['recipient_ids']))),
            initiator: Arr::get($payload, 'initiator'),
        );
    }
}
