<?php

namespace App\Enums;

enum ProviderFailureType: string
{
    case None = 'none';
    case Transient = 'transient';
    case Permanent = 'permanent';
    case RateLimited = 'rate_limited';
}
