<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when all models in the Gemini fallback chain are exhausted.
 * Triggers alerting via NotifyAllModelsUnavailable listener.
 * Requirements: 10.1
 */
class AllModelsUnavailable
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array  $unavailableModels  List of model names that are currently unavailable
     * @param  int|null  $triggeredByTenantId  Tenant whose request triggered the exhaustion (null for system-level)
     */
    public function __construct(
        public readonly array $unavailableModels,
        public readonly ?int $triggeredByTenantId = null,
    ) {}
}
