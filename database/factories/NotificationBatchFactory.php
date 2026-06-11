<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use App\Models\NotificationBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationBatch>
 */
class NotificationBatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idempotency_key' => $this->faker->unique()->uuid(),
            'initiator' => 'test-suite',
            'channel' => NotificationChannel::Sms,
            'priority' => NotificationPriority::Transactional,
            'message' => 'Your verification code is 1234.',
            'requested_recipients_count' => 1,
            'accepted_notifications_count' => 1,
            'queued_count' => 1,
            'sent_count' => 0,
            'delivered_count' => 0,
            'dropped_count' => 0,
        ];
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes): array => [
            'channel' => NotificationChannel::Email,
        ]);
    }

    public function marketing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => NotificationPriority::Marketing,
        ]);
    }
}
