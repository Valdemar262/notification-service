<?php

namespace App\Jobs;

use App\Actions\Notifications\RecordNotificationStatus;
use App\Actions\Notifications\SendNotification;
use App\DTO\Notifications\NotificationStatusTransitionData;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    public function __construct(
        public readonly int $notificationId,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 15, 60];
    }

    public function handle(SendNotification $sendNotification): void
    {
        $result = $sendNotification->handle($this->notificationId);

        if ($result->shouldRelease()) {
            $this->release(10);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $notification = app(NotificationRepository::class)->findById($this->notificationId);

        if (! $notification instanceof Notification || $notification->status !== NotificationStatus::Queued) {
            return;
        }

        app(RecordNotificationStatus::class)->transition(
            $notification,
            new NotificationStatusTransitionData(
                toStatus: NotificationStatus::Dropped,
                reason: 'max_attempts_exceeded',
                context: ['error' => $exception?->getMessage()],
            ),
        );
    }
}
