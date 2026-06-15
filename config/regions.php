<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Languages & Region
    |--------------------------------------------------------------------------
    |
    | The storefront can switch language (with RTL for Arabic) and display
    | currency. Orders are still CHARGED in OMR by Thawani — the currency switch
    | is a browsing convenience, so a note is shown at checkout.
    |
    */

    'default_locale' => 'en',

    'locales' => [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl'],
    ],

    'default_currency' => 'OMR',

    /*
    | rate = units of this currency per 1 OMR (approximate — edit to taste, or
    | wire a live FX feed later). decimals = display precision. OMR is the base.
    */
    'currencies' => [
        'OMR' => ['name' => 'Omani Rial', 'symbol' => 'OMR', 'rate' => 1, 'decimals' => 3],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'AED ', 'rate' => 9.55, 'decimals' => 2],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SAR ', 'rate' => 9.75, 'decimals' => 2],
        'QAR' => ['name' => 'Qatari Riyal', 'symbol' => 'QAR ', 'rate' => 9.46, 'decimals' => 2],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'rate' => 2.60, 'decimals' => 2],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'rate' => 2.05, 'decimals' => 2],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'rate' => 2.40, 'decimals' => 2],
    ],
];
