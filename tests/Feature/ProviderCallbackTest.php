<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationStatusEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_callback_moves_sent_notification_to_delivered(): void
    {
        $notification = Notification::factory()->sent()->create();

        $response = $this->postJson('/api/v1/provider-events/fake-sms', [
            'provider_message_id' => $notification->provider_message_id,
            'event_type' => 'delivered',
        ]);

        $response->assertAccepted();
        $this->assertSame(NotificationStatus::Delivered, $notification->refresh()->status);
    }

    public function test_duplicate_callback_is_idempotent(): void
    {
        $notification = Notification::factory()->sent()->create();
        $payload = [
            'provider_message_id' => $notification->provider_message_id,
            'event_type' => 'delivered',
        ];

        $this->postJson('/api/v1/provider-events/fake-sms', $payload)->assertAccepted();
        $this->postJson('/api/v1/provider-events/fake-sms', $payload)->assertAccepted();

        $this->assertSame(1, NotificationStatusEvent::query()
            ->where('notification_id', $notification->id)
            ->where('to_status', NotificationStatus::Delivered->value)
            ->count());
    }
}
