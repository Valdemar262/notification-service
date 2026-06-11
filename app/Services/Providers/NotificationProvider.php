<?php

namespace App\Services\Providers;

use App\DTO\Providers\ProviderSendResult;
use App\Models\Notification;

interface NotificationProvider
{
    public function send(Notification $notification): ProviderSendResult;

    public function name(): string;
}
