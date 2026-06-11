<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationStatusEvent;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberNotificationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_history_for_specific_subscriber_only(): void
    {
        $subscriber = Subscriber::factory()->create();
        $anotherSubscriber = Subscriber::factory()->create();
        $notification = Notification::factory()->for($subscriber)->sent()->create();
        Notification::factory()->for($anotherSubscriber)->sent()->create();
        NotificationStatusEvent::factory()->for($notification)->create([
            'to_status' => NotificationStatus::Sent,
            'reason' => 'provider_accepted',
        ]);

        $response = $this->getJson("/api/v1/subscribers/{$subscriber->external_id}/notifications?status=sent");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $notification->uuid)
            ->assertJsonPath('data.0.status', 'sent')
            ->assertJsonCount(1, 'data.0.status_events');
    }
}
