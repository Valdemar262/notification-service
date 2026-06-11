<?php

namespace App\Repositories;

use App\DTO\Notifications\CreateNotificationBatchData;
use App\Enums\NotificationStatus;
use App\Models\NotificationBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationBatchRepository
{
    public function findByIdempotencyKey(string $idempotencyKey): ?NotificationBatch
    {
        return NotificationBatch::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    public function findByIdempotencyKeyForUpdate(string $idempotencyKey): ?NotificationBatch
    {
        return NotificationBatch::query()
            ->where('idempotency_key', $idempotencyKey)
            ->lockForUpdate()
            ->first();
    }

    public function create(
        CreateNotificationBatchData $data,
        int $requestedRecipientsCount,
        int $acceptedNotificationsCount,
    ): NotificationBatch {
        return NotificationBatch::query()->create([
            'idempotency_key' => $data->idempotencyKey,
            'initiator' => $data->initiator,
            'channel' => $data->channel,
            'priority' => $data->priority,
            'message' => $data->message,
            'requested_recipients_count' => $requestedRecipientsCount,
            'accepted_notifications_count' => $acceptedNotificationsCount,
            'queued_count' => $acceptedNotificationsCount,
            'sent_count' => 0,
            'delivered_count' => 0,
            'dropped_count' => 0,
        ]);
    }

    public function withNotificationsCount(NotificationBatch $batch): NotificationBatch
    {
        return $batch->loadCount('notifications');
    }

    /**
     * @param  Collection<string, int>  $counts
     */
    public function updateStatusCounters(int $batchId, Collection $counts): void
    {
        DB::table('notification_batches')
            ->where('id', $batchId)
            ->update([
                'queued_count' => (int) ($counts[NotificationStatus::Queued->value] ?? 0),
                'sent_count' => (int) ($counts[NotificationStatus::Sent->value] ?? 0),
                'delivered_count' => (int) ($counts[NotificationStatus::Delivered->value] ?? 0),
                'dropped_count' => (int) ($counts[NotificationStatus::Dropped->value] ?? 0),
                'updated_at' => now(),
            ]);
    }
}
