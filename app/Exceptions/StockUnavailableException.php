<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown during checkout when a cart item can no longer be fulfilled (the variant
 * went inactive or stock dropped below the requested quantity between cart & pay).
 */
class StockUnavailableException extends RuntimeException
{
    public function __construct(public readonly string $itemName = '')
    {
        parent::__construct('stock unavailable');
    }
}
