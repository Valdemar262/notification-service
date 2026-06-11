<?php

namespace App\Actions\Notifications;

use App\DTO\Notifications\CreateNotificationBatchData;
use App\DTO\Notifications\NotificationStatusTransitionData;
use App\Enums\NotificationStatus;
use App\Models\NotificationBatch;
use App\Models\Subscriber;
use App\Repositories\NotificationBatchRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\SubscriberRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class CreateNotificationBatch
{
    public function __construct(
        private DispatchNotificationBatch $dispatchNotificationBatch,
        private NotificationBatchRepository $notificationBatches,
        private NotificationRepository $notifications,
        private SubscriberRepository $subscribers,
    ) {}

    public function handle(CreateNotificationBatchData $data): NotificationBatch
    {
        $existingBatch = $this->notificationBatches->findByIdempotencyKey($data->idempotencyKey);

        if ($existingBatch instanceof NotificationBatch) {
            return $this->notificationBatches->withNotificationsCount($existingBatch);
        }

        $reservationKey = 'idempotency:notification-batches:'.$data->idempotencyKey;
        Cache::add($reservationKey, true, (int) config('notifications.idempotency_ttl_seconds'));

        try {
            $batch = DB::transaction(function () use ($data): NotificationBatch {
                $batch = $this->notificationBatches->findByIdempotencyKeyForUpdate($data->idempotencyKey);

                if ($batch instanceof NotificationBatch) {
                    return $batch;
                }

                $subscribers = $this->subscribers->activeReceiversByExternalIds($data->recipientIds, $data->channel);

                $batch = $this->notificationBatches->create($data, count($data->recipientIds), $subscribers->count());

                foreach ($subscribers as $subscriber) {
                    $notification = $this->notifications->createQueuedForSubscriber(
                        $batch,
                        $subscriber,
                        $this->subscribers->recipientAddress($subscriber, $data->channel),
                        $this->businessIdempotencyKey($batch, $subscriber),
                    );

                    $this->notifications->recordStatusEvent(
                        $notification,
                        null,
                        new NotificationStatusTransitionData(
                            toStatus: NotificationStatus::Queued,
                            reason: 'accepted',
                        ),
                    );
                }

                DB::afterCommit(fn () => $this->dispatchNotificationBatch->handle($batch));

                return $batch;
            });

            return $this->notificationBatches->withNotificationsCount($batch);
        } catch (QueryException $exception) {
            $batch = $this->notificationBatches->findByIdempotencyKey($data->idempotencyKey);

            if ($batch instanceof NotificationBatch) {
                return $this->notificationBatches->withNotificationsCount($batch);
            }

            throw $exception;
        }
    }

    private function businessIdempotencyKey(NotificationBatch $batch, Subscriber $subscriber): string
    {
        return sha1($batch->uuid.'|'.$subscriber->external_id.'|'.$batch->channel->value);
    }
}
