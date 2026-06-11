<?php

namespace App\DTO\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Support\Arr;

readonly class SubscriberNotificationHistoryFilters
{
    public function __construct(
        public ?NotificationChannel $channel,
        public ?NotificationStatus $status,
        public ?string $from,
        public ?string $to,
        public int $perPage,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromValidated(array $payload): self
    {
        $channel = Arr::get($payload, 'channel');
        $status = Arr::get($payload, 'status');

        return new self(
            channel: $channel !== null ? NotificationChannel::from((string) $channel) : null,
            status: $status !== null ? NotificationStatus::from((string) $status) : null,
            from: Arr::get($payload, 'from'),
            to: Arr::get($payload, 'to'),
            perPage: (int) Arr::get($payload, 'per_page', 15),
        );
    }
}
