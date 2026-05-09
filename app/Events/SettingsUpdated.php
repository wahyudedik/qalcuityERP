<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SettingsUpdated Event
 *
 * Dispatched when any settings are updated (tenant, module, system, API).
 * Triggers cache invalidation via listeners.
 */
class SettingsUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Settings type: tenant, module, system, api
     */
    public string $type;

    /**
     * Tenant ID (if applicable)
     */
    public ?int $tenantId;

    /**
     * Module name (if applicable)
     */
    public ?string $module;

    /**
     * Additional metadata
     */
    public array $metadata;

    /**
     * Create a new event instance.
     *
     * @param  string  $type  Settings type (tenant, module, system, api)
     * @param  int|null  $tenantId  Tenant ID
     * @param  string|null  $module  Module name
     * @param  array  $metadata  Additional metadata
     */
    public function __construct(
        string $type,
        ?int $tenantId = null,
        ?string $module = null,
        array $metadata = []
    ) {
        $this->type = $type;
        $this->tenantId = $tenantId;
        $this->module = $module;
        $this->metadata = $metadata;
    }
}
