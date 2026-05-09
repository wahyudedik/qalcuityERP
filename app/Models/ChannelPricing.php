<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelPricing extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'formula_id',
        'base_price',
        'channel_price',
        'minimum_order_quantity',
        'bulk_discount_threshold',
        'bulk_discount_rate',
        'effective_date',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'channel_price' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:2',
        'bulk_discount_threshold' => 'decimal:2',
        'bulk_discount_rate' => 'decimal:2',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Calculate bulk price
    public function calculateBulkPrice(float $quantity): float
    {
        if ($this->bulk_discount_threshold && $quantity >= $this->bulk_discount_threshold) {
            return round($this->channel_price * (1 - ($this->bulk_discount_rate / 100)), 2);
        }

        return $this->channel_price;
    }

    // Check if pricing is currently active
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->effective_date && $this->effective_date->isFuture()) {
            return false;
        }
        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    // Relationships
    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }
}
