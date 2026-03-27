<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when requested quantity exceeds available stock.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(string $message = 'Insufficient stock.', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
