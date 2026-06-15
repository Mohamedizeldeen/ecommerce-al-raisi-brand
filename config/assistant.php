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
];
