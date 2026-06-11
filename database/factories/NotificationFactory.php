<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationBatch;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_batch_id' => NotificationBatch::factory(),
            'subscriber_id' => Subscriber::factory(),
            'channel' => NotificationChannel::Sms,
            'priority' => NotificationPriority::Transactional,
            'status' => NotificationStatus::Queued,
            'recipient_address' => '+15555550123',
            'business_idempotency_key' => $this->faker->unique()->uuid(),
            'attempts' => 0,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NotificationStatus::Sent,
            'provider' => 'fake-sms',
            'provider_message_id' => 'fake-sms-'.$this->faker->uuid(),
            'sent_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->sent()->state(fn (array $attributes): array => [
            'status' => NotificationStatus::Delivered,
            'delivered_at' => now(),
        ]);
    }

    public function dropped(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NotificationStatus::Dropped,
            'dropped_at' => now(),
        ]);
    }
}
