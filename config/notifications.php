<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Service Configuration
    |--------------------------------------------------------------------------
    */

    'enabled_channels' => ['database', 'email', 'telegram', 'fcm'],

    'retry_failed_notifications' => true,
    'max_retry_attempts' => 3,

    'encryption' => [
        'enabled' => false,
        'algorithm' => 'AES-256-CBC',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Channel Configuration
    |--------------------------------------------------------------------------
    */

    'email' => [
        'queue' => true,
        'queue_name' => 'notifications',
        'retry_after' => 60,
        'timeout' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram Configuration
    |--------------------------------------------------------------------------
    */

    'telegram' => [
        'enabled' => true,
        'rate_limit' => true,
        'rate_limit_per_minute' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM) Configuration
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'enabled' => true,
        'priority' => 'high',
        'time_to_live' => 2419200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Notifications
    |--------------------------------------------------------------------------
    */

    'database' => [
        'retention_days' => 30,
        'auto_cleanup' => true,
        'cleanup_schedule' => 'daily',
    ],
];
