<?php

use App\Support\Money;

if (! function_exists('format_omr')) {
    /**
     * Format integer baisa as an OMR display string (e.g. "OMR195.000").
     */
    function format_omr(int $baisa, bool $withCurrency = true): string
    {
        return Money::format($baisa, $withCurrency);
    }
}
