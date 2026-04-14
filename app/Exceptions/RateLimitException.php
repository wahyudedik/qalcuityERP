<?php

namespace App\Exceptions;

use RuntimeException;

class RateLimitException extends RuntimeException
{
    public function __construct(string $message = 'Rate limit exceeded', int $code = 429, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
