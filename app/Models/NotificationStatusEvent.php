<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Database\Factories\NotificationStatusEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationStatusEvent extends Model
{
    /** @use HasFactory<NotificationStatusEventFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'from_status',
        'to_status',
        'reason',
        'context',
    ];

    /**
     * @return BelongsTo<Notification, $this>
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => NotificationStatus::class,
            'to_status' => NotificationStatus::class,
            'context' => 'array',
        ];
    }
}
