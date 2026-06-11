<?php

use App\Enums\NotificationPriority;

return [
    'max_recipients_per_batch' => (int) env('NOTIFICATIONS_MAX_RECIPIENTS_PER_BATCH', 5000),

    'idempotency_ttl_seconds' => (int) env('NOTIFICATIONS_IDEMPOTENCY_TTL_SECONDS', 86400),

    'message_limits' => [
        'sms' => (int) env('NOTIFICATIONS_SMS_MESSAGE_MAX_LENGTH', 480),
        'email' => (int) env('NOTIFICATIONS_EMAIL_MESSAGE_MAX_LENGTH', 5000),
    ],

    'queues' => [
        NotificationPriority::Transactional->value => env('NOTIFICATIONS_CRITICAL_QUEUE', 'notifications-critical'),
        'default' => env('NOTIFICATIONS_DEFAULT_QUEUE', 'notifications-default'),
        NotificationPriority::Marketing->value => env('NOTIFICATIONS_MARKETING_QUEUE', 'notifications-marketing'),
    ],

    'providers' => [
        'sms' => env('NOTIFICATIONS_SMS_PROVIDER', 'fake-sms'),
        'email' => env('NOTIFICATIONS_EMAIL_PROVIDER', 'fake-email'),
    ],

    'rate_limits' => [
        'sms' => [
            'transactional' => [
                'max_attempts' => (int) env('NOTIFICATIONS_SMS_TRANSACTIONAL_RATE_MAX', 120),
                'decay_seconds' => (int) env('NOTIFICATIONS_SMS_TRANSACTIONAL_RATE_DECAY', 60),
            ],
            'marketing' => [
                'max_attempts' => (int) env('NOTIFICATIONS_SMS_MARKETING_RATE_MAX', 30),
                'decay_seconds' => (int) env('NOTIFICATIONS_SMS_MARKETING_RATE_DECAY', 60),
            ],
        ],
        'email' => [
            'transactional' => [
                'max_attempts' => (int) env('NOTIFICATIONS_EMAIL_TRANSACTIONAL_RATE_MAX', 240),
                'decay_seconds' => (int) env('NOTIFICATIONS_EMAIL_TRANSACTIONAL_RATE_DECAY', 60),
            ],
            'marketing' => [
                'max_attempts' => (int) env('NOTIFICATIONS_EMAIL_MARKETING_RATE_MAX', 60),
                'decay_seconds' => (int) env('NOTIFICATIONS_EMAIL_MARKETING_RATE_DECAY', 60),
            ],
        ],
    ],
];
