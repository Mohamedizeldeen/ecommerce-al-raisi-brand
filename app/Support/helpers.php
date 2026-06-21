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
     * A cache-busted URL for a file in /public. The "?v=" token is derived from
     * the file's CONTENT (a short md5 of the bytes), so it changes if and only if
     * the file's contents change — guaranteeing browsers/CDNs (e.g. Cloudways
     * Varnish) fetch the new copy after a logo/image swap, even when a deploy
     * happens to reuse the file's modification time.
     *
     * Hashing is memoised per request; very large files (e.g. the hero video)
     * fall back to mtime+size so we never md5 megabytes on every page load.
     */
    function asset_version(string $path): string
    {
        static $memo = [];

        if (! array_key_exists($path, $memo)) {
            $full = public_path($path);

            if (! is_file($full)) {
                $memo[$path] = null;
            } else {
                $size = @filesize($full);

                // Content hash for normal assets (bulletproof); mtime+size for big
                // files so we don't hash megabytes on every request.
                if ($size !== false && $size <= 2_000_000) {
                    $memo[$path] = substr(md5_file($full) ?: '', 0, 12)
                        ?: (string) @filemtime($full);
                } else {
                    $memo[$path] = ((string) (@filemtime($full) ?: 0)).'-'.($size ?: 0);
                }
            }
        }

        return asset($path).($memo[$path] !== null ? '?v='.$memo[$path] : '');
    }
}
