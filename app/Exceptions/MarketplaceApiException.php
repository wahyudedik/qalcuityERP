<?php

namespace App\Exceptions;

use RuntimeException;

class MarketplaceApiException extends RuntimeException
{
    public function __construct(string $message = 'Marketplace API error', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
