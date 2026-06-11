<?php

namespace Tests\Feature;

use App\Actions\Notifications\DispatchNotificationBatch;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\NotificationBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PriorityDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_batch_dispatches_to_marketing_queue(): void
    {
        Queue::fake();

        $batch = NotificationBatch::factory()->marketing()->create();
        Notification::factory()->for($batch, 'batch')->create();

        app(DispatchNotificationBatch::class)->handle($batch);

        Queue::assertPushedOn('notifications-marketing', SendNotificationJob::class);
    }
}
