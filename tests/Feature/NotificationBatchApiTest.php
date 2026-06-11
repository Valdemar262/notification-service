<?php

namespace Tests\Feature;

use App\Actions\Notifications\DispatchNotificationBatch;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\NotificationBatch;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationBatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_batch_notifications_and_status_events(): void
    {
        $subscribers = Subscriber::factory()->count(2)->create();

        $response = $this
            ->withHeader('Idempotency-Key', 'batch-key-1')
            ->postJson('/api/v1/notification-batches', [
                'channel' => 'sms',
                'priority' => 'transactional',
                'message' => 'Your code is 1234',
                'recipient_ids' => $subscribers->pluck('external_id')->all(),
                'initiator' => 'auth-service',
            ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('data.idempotency_key', 'batch-key-1')
            ->assertJsonPath('data.counters.requested_recipients', 2)
            ->assertJsonPath('data.counters.accepted_notifications', 2);

        $this->assertDatabaseCount('notification_batches', 1);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseCount('notification_status_events', 4);
    }

    public function test_repeated_idempotency_key_returns_same_batch(): void
    {
        $subscriber = Subscriber::factory()->create();
        $payload = [
            'channel' => 'email',
            'priority' => 'marketing',
            'message' => 'Campaign message',
            'recipient_ids' => [$subscriber->external_id],
        ];

        $first = $this->withHeader('Idempotency-Key', 'same-key')->postJson('/api/v1/notification-batches', $payload);
        $second = $this->withHeader('Idempotency-Key', 'same-key')->postJson('/api/v1/notification-batches', $payload);

        $first->assertAccepted();
        $second->assertAccepted();
        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('notification_batches', 1);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_rejects_more_than_5000_recipients(): void
    {
        $response = $this
            ->withHeader('Idempotency-Key', 'too-many')
            ->postJson('/api/v1/notification-batches', [
                'channel' => 'sms',
                'priority' => 'transactional',
                'message' => 'Your code is 1234',
                'recipient_ids' => array_map(fn (int $id): string => 'subscriber-'.$id, range(1, 5001)),
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('recipient_ids');
    }

    public function test_transactional_batch_dispatches_to_critical_queue(): void
    {
        Queue::fake();

        $batch = NotificationBatch::factory()->create([
            'priority' => 'transactional',
        ]);
        Notification::factory()->for($batch, 'batch')->create();

        app(DispatchNotificationBatch::class)->handle($batch);

        Queue::assertPushedOn('notifications-critical', SendNotificationJob::class);
    }
}
