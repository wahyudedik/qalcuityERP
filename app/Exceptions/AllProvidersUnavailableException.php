<?php

namespace App\Exceptions;

/**
 * Thrown when all AI providers in the fallback chain are simultaneously unavailable.
 * This is the cross-provider equivalent of AllModelsUnavailableException.
 *
 * Requirements: 3.3, 9.1
 */
class AllProvidersUnavailableException extends \RuntimeException
{
    /**
     * @param array $unavailableProviders List of provider names that are currently unavailable
     */
    public function __construct(array $unavailableProviders = [])
    {
        parent::__construct(
            'Layanan AI sedang tidak tersedia. Silakan coba beberapa saat lagi.',
            503
        );
    }
}
