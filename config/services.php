<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'sender_id' => env('FCM_SENDER_ID'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
    ],

    'payment' => [
        'default_gateway' => env('PAYMENT_GATEWAY', 'billplz'),
        'gateways' => [
            'billplz' => [
                'base_url' => env('BILLPLZ_BASE_URL', 'https://www.billplz.com'),
                'api_key' => env('BILLPLZ_API_KEY'),
                'collection_id' => env('BILLPLZ_COLLECTION_ID'),
                'x_signature' => env('BILLPLZ_X_SIGNATURE'),
            ],
            'toyyibpay' => [
                'base_url' => env('TOYYIBPAY_BASE_URL', 'https://toyyibpay.com'),
                'secret_key' => env('TOYYIBPAY_SECRET_KEY'),
                'category_code' => env('TOYYIBPAY_CATEGORY_CODE'),
                'callback_token' => env('TOYYIBPAY_CALLBACK_TOKEN'),
                'verify_ssl' => env('TOYYIBPAY_VERIFY_SSL', true),
            ],
        ],
    ],

    'whatsapp' => [
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'fallback_to' => env('WHATSAPP_FALLBACK_TO'),
    ],

];
