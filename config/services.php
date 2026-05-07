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

    'youtube' => [
        'key' => env('YOUTUBE_API_KEY'),
    ],

    'wikipedia' => [
        'user_agent' => env('WIKIPEDIA_USER_AGENT', 'GIBLE/1.0 (educational project)'),
        'timeout_seconds' => env('WIKIPEDIA_TIMEOUT_SECONDS', 12),
        'cache_ttl_seconds' => env('WIKIPEDIA_CACHE_TTL_SECONDS', 1800),
        'max_retries' => env('WIKIPEDIA_MAX_RETRIES', 2),
    ],

    'discord' => [
        'webhook_url' => env('DISCORD_WEBHOOK_URL'),
        'max_attempts' => env('DISCORD_WEBHOOK_MAX_ATTEMPTS', 2),
    ],

];
