<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when all models in the Gemini fallback chain are simultaneously unavailable.
 * Requirements: 2.5, 6.4
 */
class AllModelsUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Layanan AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.',
            503
        );
    }
}
