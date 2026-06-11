<?php

namespace App\DTO\Providers;

use App\Enums\ProviderFailureType;

final readonly class ProviderSendResult
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public ?string $providerMessageId,
        public ProviderFailureType $failureType,
        public array $rawResponse = [],
    ) {}

    public static function success(string $providerMessageId, array $rawResponse = []): self
    {
        return new self($providerMessageId, ProviderFailureType::None, $rawResponse);
    }

    public static function failure(ProviderFailureType $failureType, array $rawResponse = []): self
    {
        return new self(null, $failureType, $rawResponse);
    }

    public function isSuccessful(): bool
    {
        return $this->failureType === ProviderFailureType::None;
    }
}
