<?php

namespace App\Repositories;

use App\DTO\Notifications\NotificationStatusTransitionData;
use App\DTO\Notifications\SubscriberNotificationHistoryFilters;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationBatch;
use App\Models\Subscriber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationRepository
{
    public function createQueuedForSubscriber(
        NotificationBatch $batch,
        Subscriber $subscriber,
        string $recipientAddress,
        string $businessIdempotencyKey,
    ): Notification {
        return $batch->notifications()->create([
            'subscriber_id' => $subscriber->id,
            'channel' => $batch->channel,
            'priority' => $batch->priority,
            'status' => NotificationStatus::Queued,
            'recipient_address' => $recipientAddress,
            'business_idempotency_key' => $businessIdempotencyKey,
            'attempts' => 0,
        ]);
    }

    public function findWithBatchOrFail(int $notificationId): Notification
    {
        return Notification::query()
            ->with('batch')
            ->findOrFail($notificationId);
    }

    public function findById(int $notificationId): ?Notification
    {
        return Notification::query()->find($notificationId);
    }

    public function lockByIdOrFail(int $notificationId): Notification
    {
        return Notification::query()
            ->lockForUpdate()
            ->findOrFail($notificationId);
    }

    public function findByProviderMessageOrFail(string $provider, string $providerMessageId): Notification
    {
        return Notification::query()
            ->where('provider', $provider)
            ->where('provider_message_id', $providerMessageId)
            ->firstOrFail();
    }

    public function recordProviderAttempt(Notification $notification, string $provider): Notification
    {
        $notification->forceFill([
            'attempts' => $notification->attempts + 1,
            'provider' => $provider,
        ])->save();

        return $notification->refresh();
    }

    public function recordProviderMessageId(Notification $notification, string $providerMessageId): Notification
    {
        $notification->forceFill([
            'provider_message_id' => $providerMessageId,
        ])->save();

        return $notification->refresh();
    }

    public function recordStatusEvent(
        Notification $notification,
        ?NotificationStatus $fromStatus,
        NotificationStatusTransitionData $transition,
    ): void {
        $notification->statusEvents()->create([
            'from_status' => $fromStatus,
            'to_status' => $transition->toStatus,
            'reason' => $transition->reason,
            'context' => $transition->context,
        ]);
    }

    /**
     * @param  array<string, mixed>  $updates
     */
    public function updateStatus(Notification $notification, array $updates): Notification
    {
        $notification->forceFill($updates)->save();

        return $notification->refresh();
    }

    /**
     * @return Collection<string, int>
     */
    public function statusCountsByBatch(int $batchId): Collection
    {
        return Notification::query()
            ->where('notification_batch_id', $batchId)
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');
    }

    public function paginateForSubscriber(
        Subscriber $subscriber,
        SubscriberNotificationHistoryFilters $filters,
    ): LengthAwarePaginator {
        return $subscriber->notifications()
            ->with([
                'batch:id,uuid',
                'subscriber:id,external_id',
                'statusEvents' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->when($filters->channel, fn ($query) => $query->where('channel', $filters->channel->value))
            ->when($filters->status, fn ($query) => $query->where('status', $filters->status->value))
            ->when($filters->from, fn ($query) => $query->where('created_at', '>=', $filters->from))
            ->when($filters->to, fn ($query) => $query->where('created_at', '<=', $filters->to))
            ->latest()
            ->paginate($filters->perPage);
    }
}
