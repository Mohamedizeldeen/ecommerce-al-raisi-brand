<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront AI Assistant
    |--------------------------------------------------------------------------
    |
    | Configuration for the customer-facing chat assistant. The assistant is
    | grounded ONLY in the store's own data (products, categories, collections
    | and support pages) via tools — it is instructed never to invent answers.
    |
    */

    // Master switch — set ASSISTANT_ENABLED=false to hide the widget entirely.
    'enabled' => env('ASSISTANT_ENABLED', true),

    // The Google Gemini model used for replies. Must be available to your key.
    'model' => env('ASSISTANT_MODEL', 'gemini-2.5-flash'),

    // Where to send customers when a question is outside the store's scope.
    'fallback_phone' => env('ASSISTANT_FALLBACK_PHONE', '98084952'),

    // How many previous turns (user + assistant messages) to keep as context.
    'max_history' => (int) env('ASSISTANT_MAX_HISTORY', 8),

    // Per-request timeout (seconds) for the model call.
    'timeout' => (int) env('ASSISTANT_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Virtual Try-On ("Nano Banana" — Gemini image model)
    |--------------------------------------------------------------------------
    | Lets a shopper upload a photo of themselves and see the product on them.
    | Requires a Gemini API key with IMAGE quota (a paid/billing-enabled plan —
    | the image model is NOT available on the free tier).
    */
    'try_on' => [
        'enabled' => env('TRY_ON_ENABLED', true),
        // gemini-2.5-flash-image (Nano Banana). Alternatives: nano-banana-pro-preview, gemini-3-pro-image.
        'model' => env('TRY_ON_MODEL', 'gemini-2.5-flash-image'),
        'timeout' => (int) env('TRY_ON_TIMEOUT', 90),
        // Global cap on AI try-on generations per calendar day (cost guard).
        'daily_limit' => (int) env('TRY_ON_DAILY_LIMIT', 200),
        // Per-client (per-IP) cap on AI try-on generations per calendar day.
        'per_client_daily' => (int) env('TRY_ON_PER_CLIENT_DAILY', 10),
    ],
];
