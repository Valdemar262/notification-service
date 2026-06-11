<?php

namespace App\DTO\Notifications;

readonly class ProviderDeliveryEventData
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $provider,
        public string $providerMessageId,
        public string $eventType,
        public array $payload,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromValidated(string $provider, array $payload): self
    {
        return new self(
            provider: $provider,
            providerMessageId: (string) $payload['provider_message_id'],
            eventType: (string) $payload['event_type'],
            payload: $payload,
        );
    }
}
