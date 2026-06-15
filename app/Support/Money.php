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
