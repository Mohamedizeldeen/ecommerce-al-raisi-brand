<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A virtual try-on failure with a safe, user-facing message. `kind` distinguishes
 * a quota/billing issue (503, retryable) from a bad-input/other failure (422).
 */
class TryOnException extends RuntimeException
{
    public function __construct(
        public readonly string $kind,
        public readonly string $userMessage,
    ) {
        parent::__construct($userMessage);
    }
}
