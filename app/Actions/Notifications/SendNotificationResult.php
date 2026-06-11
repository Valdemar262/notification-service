<?php

namespace App\Actions\Notifications;

final readonly class SendNotificationResult
{
    private function __construct(
        public string $status,
    ) {}

    public static function sent(): self
    {
        return new self('sent');
    }

    public static function dropped(): self
    {
        return new self('dropped');
    }

    public static function skipped(): self
    {
        return new self('skipped');
    }

    public static function rateLimited(): self
    {
        return new self('rate_limited');
    }

    public function shouldRelease(): bool
    {
        return $this->status === 'rate_limited';
    }
}
