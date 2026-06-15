<?php

namespace App\Support;

class Money
{
    /**
     * Baisa per Omani Rial. OMR is a 3-decimal currency, and Thawani's API
     * expects integer baisa, so all money is stored internally as baisa.
     */
    public const SUBUNITS = 1000;

    /**
     * Format integer baisa as a display string, e.g. 195000 => "OMR195.000".
     */
    public static function format(int $baisa, bool $withCurrency = true): string
    {
        $amount = number_format($baisa / self::SUBUNITS, 3, '.', ',');

        return $withCurrency ? 'OMR'.$amount : $amount;
    }

    /**
     * The visitor's chosen browsing currency (session), falling back to OMR.
     */
    public static function currentCurrency(): string
    {
        $currency = session('currency', config('regions.default_currency', 'OMR'));

        return array_key_exists($currency, config('regions.currencies', []))
            ? $currency
            : config('regions.default_currency', 'OMR');
    }

    /**
     * Format baisa in the visitor's chosen browsing currency, e.g. "$507.00".
     * Orders are still charged in OMR — use format() for cart/checkout totals.
     */
    public static function display(int $baisa, ?string $currency = null): string
    {
        $currency ??= self::currentCurrency();
        $config = config("regions.currencies.{$currency}") ?? config('regions.currencies.OMR');

        $amount = ($baisa / self::SUBUNITS) * ($config['rate'] ?? 1);

        return ($config['symbol'] ?? $currency).number_format($amount, $config['decimals'] ?? 2, '.', ',');
    }

    /**
     * Convert integer baisa to a decimal OMR value (for display/reporting only).
     */
    public static function toOmr(int $baisa): float
    {
        return $baisa / self::SUBUNITS;
    }

    /**
     * Convert a decimal OMR amount to integer baisa.
     */
    public static function toBaisa(int|float|string $omr): int
    {
        return (int) round(((float) $omr) * self::SUBUNITS);
    }
}
