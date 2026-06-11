<?php

namespace App\DTO\Notifications;

use App\Enums\NotificationStatus;

readonly class NotificationStatusTransitionData
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public NotificationStatus $toStatus,
        public ?string $reason = null,
        public array $context = [],
    ) {}
}
