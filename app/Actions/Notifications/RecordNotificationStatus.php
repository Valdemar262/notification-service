<?php

namespace App\Actions\Notifications;

use App\DTO\Notifications\NotificationStatusTransitionData;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Repositories\NotificationBatchRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\DB;

readonly class RecordNotificationStatus
{
    public function __construct(
        private NotificationRepository      $notifications,
        private NotificationBatchRepository $notificationBatches,
    ) {}

    public function transition(
        Notification $notification,
        NotificationStatusTransitionData $transition,
    ): bool {
        return DB::transaction(function () use ($notification, $transition): bool {
            $current = $this->notifications->lockByIdOrFail($notification->id);
            $fromStatus = $current->status;

            if ($fromStatus === $transition->toStatus) {
                return false;
            }

            $updates = [
                'status' => $transition->toStatus,
                'last_error' => $transition->toStatus === NotificationStatus::Dropped ? $transition->reason : $current->last_error,
            ];

            if ($transition->toStatus === NotificationStatus::Sent) {
                $updates['sent_at'] = now();
            }

            if ($transition->toStatus === NotificationStatus::Delivered) {
                $updates['delivered_at'] = now();
            }

            if ($transition->toStatus === NotificationStatus::Dropped) {
                $updates['dropped_at'] = now();
            }

            $current = $this->notifications->updateStatus($current, $updates);
            $this->notifications->recordStatusEvent($current, $fromStatus, $transition);

            $this->refreshBatchCounters($current->notification_batch_id);

            return true;
        });
    }

    public function refreshBatchCounters(int $batchId): void
    {
        $this->notificationBatches->updateStatusCounters(
            $batchId,
            $this->notifications->statusCountsByBatch($batchId),
        );
    }
}
