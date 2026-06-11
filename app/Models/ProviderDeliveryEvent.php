<?php

namespace App\Models;

use Database\Factories\ProviderDeliveryEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderDeliveryEvent extends Model
{
    /** @use HasFactory<ProviderDeliveryEventFactory> */
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_message_id',
        'event_type',
        'payload',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
