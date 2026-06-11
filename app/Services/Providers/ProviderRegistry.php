<?php

namespace App\Services\Providers;

use App\Enums\NotificationChannel;
use InvalidArgumentException;

readonly class ProviderRegistry
{
    public function __construct(
        private FakeSmsProvider $smsProvider,
        private FakeEmailProvider $emailProvider,
    ) {}

    public function forChannel(NotificationChannel $channel): NotificationProvider
    {
        return match ($channel) {
            NotificationChannel::Sms => $this->smsProvider,
            NotificationChannel::Email => $this->emailProvider,
        };
    }

    public function providerName(NotificationChannel $channel): string
    {
        return $this->forChannel($channel)->name();
    }

    public function channelForProvider(string $provider): NotificationChannel
    {
        return match ($provider) {
            $this->smsProvider->name() => NotificationChannel::Sms,
            $this->emailProvider->name() => NotificationChannel::Email,
            default => throw new InvalidArgumentException("Unsupported provider [{$provider}]."),
        };
    }
}
