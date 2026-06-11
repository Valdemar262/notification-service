<?php

namespace Tests\Feature;

use App\Actions\Notifications\SendNotification;
use App\Enums\NotificationStatus;
use App\Exceptions\TransientProviderException;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\NotificationBatch;
use App\Models\NotificationStatusEvent;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_moves_queued_notification_to_sent(): void
    {
        $notification = $this->queuedNotification();

        (new SendNotificationJob($notification->id))->handle(app(SendNotification::class));

        $notification->refresh();
        $this->assertSame(NotificationStatus::Sent, $notification->status);
        $this->assertSame('fake-sms', $notification->provider);
        $this->assertNotNull($notification->provider_message_id);
        $this->assertDatabaseHas(NotificationStatusEvent::class, [
            'notification_id' => $notification->id,
            'to_status' => NotificationStatus::Sent->value,
        ]);
    }

    public function test_duplicate_job_does_not_send_again_after_status_changed(): void
    {
        $notification = $this->queuedNotification();
        $job = new SendNotificationJob($notification->id);

        $job->handle(app(SendNotification::class));
        $job->handle(app(SendNotification::class));

        $notification->refresh();
        $this->assertSame(1, $notification->attempts);
        $this->assertSame(1, $notification->statusEvents()->where('to_status', NotificationStatus::Sent->value)->count());
    }

    public function test_permanent_provider_failure_drops_notification(): void
    {
        $notification = $this->queuedNotification('permanent-failure');

        (new SendNotificationJob($notification->id))->handle(app(SendNotification::class));

        $notification->refresh();
        $this->assertSame(NotificationStatus::Dropped, $notification->status);
        $this->assertSame('provider_permanent_failure', $notification->last_error);
    }

    public function test_transient_provider_failure_retries_without_dropping_immediately(): void
    {
        $notification = $this->queuedNotification('transient-failure');

        $this->expectException(TransientProviderException::class);

        try {
            (new SendNotificationJob($notification->id))->handle(app(SendNotification::class));
        } finally {
            $notification->refresh();
            $this->assertSame(NotificationStatus::Queued, $notification->status);
            $this->assertSame(1, $notification->attempts);
        }
    }

    private function queuedNotification(string $message = 'Your code is 1234'): Notification
    {
        $subscriber = Subscriber::factory()->create();
        $batch = NotificationBatch::factory()->create([
            'message' => $message,
        ]);

        return Notification::factory()
            ->for($subscriber)
            ->for($batch, 'batch')
            ->create([
                'recipient_address' => $subscriber->phone,
            ]);
    }
}
