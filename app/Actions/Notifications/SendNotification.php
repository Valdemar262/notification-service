<?php

namespace App\Actions\Notifications;

use App\DTO\Notifications\NotificationStatusTransitionData;
use App\Enums\NotificationStatus;
use App\Enums\ProviderFailureType;
use App\Exceptions\TransientProviderException;
use App\Repositories\NotificationRepository;
use App\Services\Providers\ProviderRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

readonly class SendNotification
{
    public function __construct(
        private ProviderRegistry $providerRegistry,
        private RecordNotificationStatus $recordNotificationStatus,
        private NotificationRepository $notifications,
    ) {}

    public function handle(int $notificationId): SendNotificationResult
    {
        $lock = Cache::lock('notification:'.$notificationId.':send', 90);

        return $lock->block(1, function () use ($notificationId): SendNotificationResult {
            $notification = $this->notifications->findWithBatchOrFail($notificationId);

            if ($notification->status !== NotificationStatus::Queued) {
                return SendNotificationResult::skipped();
            }

            $provider = $this->providerRegistry->forChannel($notification->channel);
            $rateLimitKey = implode(':', [
                'provider',
                $provider->name(),
                $notification->channel->value,
                $notification->priority->value,
            ]);
            $rateLimit = config("notifications.rate_limits.{$notification->channel->value}.{$notification->priority->value}");

            if (RateLimiter::tooManyAttempts($rateLimitKey, (int) $rateLimit['max_attempts'])) {
                return SendNotificationResult::rateLimited();
            }

            RateLimiter::hit($rateLimitKey, (int) $rateLimit['decay_seconds']);

            $notification = $this->notifications->recordProviderAttempt($notification, $provider->name());

            $result = $provider->send($notification->load('batch'));

            if ($result->isSuccessful()) {
                $notification = $this->notifications->recordProviderMessageId(
                    $notification,
                    (string) $result->providerMessageId,
                );

                $this->recordNotificationStatus->transition(
                    $notification,
                    new NotificationStatusTransitionData(
                        toStatus: NotificationStatus::Sent,
                        reason: 'provider_accepted',
                        context: $result->rawResponse,
                    ),
                );

                return SendNotificationResult::sent();
            }

            if ($result->failureType === ProviderFailureType::Permanent) {
                $this->recordNotificationStatus->transition(
                    $notification,
                    new NotificationStatusTransitionData(
                        toStatus: NotificationStatus::Dropped,
                        reason: 'provider_permanent_failure',
                        context: $result->rawResponse,
                    ),
                );

                return SendNotificationResult::dropped();
            }

            if ($result->failureType === ProviderFailureType::RateLimited) {
                return SendNotificationResult::rateLimited();
            }

            throw new TransientProviderException('Provider temporary failure.');
        });
    }
}
