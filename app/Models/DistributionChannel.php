<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionChannel extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'channel_name',
        'channel_type',
        'channel_code',
        'description',
        'contact_person',
        'contact_email',
        'contact_phone',
        'commission_rate',
        'discount_rate',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(ChannelSale::class, 'channel_id');
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ChannelSale::class, 'channel_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(ChannelSale::class, 'channel_id');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('channel_type', $type);
    }

    public function getTotalSalesAttribute(): float
    {
        return $this->sales()->where('status', 'completed')->sum('total_amount');
    }

    public function getTotalQuantitySoldAttribute(): int
    {
        return $this->sales()->where('status', 'completed')->sum('quantity_sold');
    }
}