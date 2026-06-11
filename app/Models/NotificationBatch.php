<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use Database\Factories\NotificationBatchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationBatch extends Model
{
    /** @use HasFactory<NotificationBatchFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'idempotency_key',
        'initiator',
        'channel',
        'priority',
        'message',
        'requested_recipients_count',
        'accepted_notifications_count',
        'queued_count',
        'sent_count',
        'delivered_count',
        'dropped_count',
        'subscriber_id',
        'recipient_address',
        'status',
        'business_idempotency_key',
        'attempts',
    ];

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'priority' => NotificationPriority::class,
            'requested_recipients_count' => 'integer',
            'accepted_notifications_count' => 'integer',
            'queued_count' => 'integer',
            'sent_count' => 'integer',
            'delivered_count' => 'integer',
            'dropped_count' => 'integer',
        ];
    }
}
