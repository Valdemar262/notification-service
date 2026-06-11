<?php

namespace App\Services\Providers;

use App\DTO\Providers\ProviderSendResult;
use App\Enums\ProviderFailureType;
use App\Models\Notification;

class FakeSmsProvider implements NotificationProvider
{
    public function send(Notification $notification): ProviderSendResult
    {
        $message = mb_strtolower($notification->batch->message);

        if (str_contains($message, 'permanent-failure')) {
            return ProviderSendResult::failure(ProviderFailureType::Permanent, ['reason' => 'invalid phone']);
        }

        if (str_contains($message, 'transient-failure')) {
            return ProviderSendResult::failure(ProviderFailureType::Transient, ['reason' => 'sms gateway unavailable']);
        }

        if (str_contains($message, 'rate-limit')) {
            return ProviderSendResult::failure(ProviderFailureType::RateLimited, ['reason' => 'sms gateway throttled']);
        }

        return ProviderSendResult::success($this->messageId($notification), ['accepted' => true]);
    }

    public function name(): string
    {
        return 'fake-sms';
    }

    private function messageId(Notification $notification): string
    {
        return $this->name().'-'.substr(sha1($notification->business_idempotency_key), 0, 24);
    }
}
