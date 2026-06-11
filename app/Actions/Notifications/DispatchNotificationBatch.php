<?php

namespace App\Actions\Notifications;

use App\Enums\NotificationPriority;
use App\Jobs\SendNotificationJob;
use App\Models\NotificationBatch;

class DispatchNotificationBatch
{
    public function handle(NotificationBatch $batch): void
    {
        $queue = $this->queueName($batch->priority);

        $batch->notifications()
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($notifications) use ($queue): void {
                foreach ($notifications as $notification) {
                    SendNotificationJob::dispatch($notification->id)->onQueue($queue);
                }
            });
    }

    public function queueName(NotificationPriority $priority): string
    {
        $queues = config('notifications.queues');

        return match ($priority) {
            NotificationPriority::Transactional => $queues[NotificationPriority::Transactional->value],
            NotificationPriority::Marketing => $queues[NotificationPriority::Marketing->value],
        };
    }
}
