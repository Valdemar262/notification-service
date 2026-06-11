<?php

namespace App\Repositories;

use App\Enums\NotificationChannel;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Collection;

class SubscriberRepository
{
    /**
     * @param  array<int, string>  $externalIds
     * @return Collection<int, Subscriber>
     */
    public function activeReceiversByExternalIds(array $externalIds, NotificationChannel $channel): Collection
    {
        return Subscriber::query()
            ->whereIn('external_id', $externalIds)
            ->where('is_active', true)
            ->whereNotNull($this->addressColumn($channel))
            ->get();
    }

    public function findByExternalIdOrFail(string $externalId): Subscriber
    {
        return Subscriber::query()
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    public function recipientAddress(Subscriber $subscriber, NotificationChannel $channel): string
    {
        return (string) $subscriber->{$this->addressColumn($channel)};
    }

    private function addressColumn(NotificationChannel $channel): string
    {
        return match ($channel) {
            NotificationChannel::Sms => 'phone',
            NotificationChannel::Email => 'email',
        };
    }
}
