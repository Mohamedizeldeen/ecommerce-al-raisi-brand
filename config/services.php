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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'thawani' => [
        'mode' => env('THAWANI_MODE', 'uat'),
        'base_url' => env('THAWANI_BASE_URL', 'https://uatcheckout.thawani.om/api/v1'),
        'pay_url' => env('THAWANI_PAY_URL', 'https://uatcheckout.thawani.om/pay'),
        'secret_key' => env('THAWANI_SECRET_KEY'),
        'publishable_key' => env('THAWANI_PUBLISHABLE_KEY'),
        // Optional shared secret appended to the webhook URL in the Thawani dashboard
        // (e.g. /thawani/webhook?secret=...). When set, the webhook rejects mismatches.
        'webhook_secret' => env('THAWANI_WEBHOOK_SECRET'),
    ],

    'hcaptcha' => [
        'sitekey' => env('HCAPTCHA_SITEKEY'),
        'secret' => env('HCAPTCHA_SECRET'),
    ],

];
