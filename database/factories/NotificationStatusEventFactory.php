<?php

namespace Database\Factories;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationStatusEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationStatusEvent>
 */
class NotificationStatusEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_id' => Notification::factory(),
            'from_status' => null,
            'to_status' => NotificationStatus::Queued,
            'reason' => 'created',
            'context' => [],
        ];
    }
}
