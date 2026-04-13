<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionChannel extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'channel_name',
        'channel_type',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'region',
        'status',
        'commission_rate',
        'priority',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(ChannelSale::class, 'channel_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
