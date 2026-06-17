<?php

use App\Support\Money;

if (! function_exists('format_omr')) {
    /**
     * Format integer baisa as an OMR display string (e.g. "OMR195.000").
     * Use for cart/checkout/order totals — these are charged in OMR.
     */
    function format_omr(int $baisa, bool $withCurrency = true): string
    {
        return Money::format($baisa, $withCurrency);
    }
}

if (! function_exists('money')) {
    /**
     * Format integer baisa in the visitor's chosen browsing currency
     * (e.g. "$507.00"). For storefront product displays only.
     */
    function money(int $baisa): string
    {
        return Money::display($baisa);
    }
}

if (! function_exists('asset_version')) {
    /**
     * A cache-busted URL for a file in /public. Appends "?v=<mtime>" so browsers
     * and CDNs (e.g. Cloudways Varnish) fetch the new copy whenever the file
     * changes — e.g. after replacing the hero video or a static image.
     */
    function asset_version(string $path): string
    {
        $mtime = @filemtime(public_path($path));

        return asset($path).($mtime ? '?v='.$mtime : '');
    }
}
