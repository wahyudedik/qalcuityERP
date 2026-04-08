<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class EcommerceProductMapping extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'channel_id',
        'external_id',
        'external_sku',
        'external_variant_id',
        'is_active',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function channel()
    {
        return $this->belongsTo(Integration::class, 'channel_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByChannel($query, int $channelId)
    {
        return $query->where('channel_id', $channelId);
    }

    /**
     * Mark as synced
     */
    public function markAsSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }

    /**
     * Deactivate mapping
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Update external ID
     */
    public function updateExternalId(string $externalId): void
    {
        $this->update([
            'external_id' => $externalId,
            'last_synced_at' => now(),
        ]);
    }
}
