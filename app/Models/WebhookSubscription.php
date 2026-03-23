<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookSubscription extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'url', 'secret', 'events', 'is_active',
        'retry_count', 'last_triggered_at',
    ];

    protected $casts = [
        'events'             => 'array',
        'is_active'          => 'boolean',
        'last_triggered_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? []) || in_array('*', $this->events ?? []);
    }
}
