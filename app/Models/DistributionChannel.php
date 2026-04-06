<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionChannel extends Model
{
    use HasFactory, SoftDeletes;

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

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->channel_type) {
            'retail' => 'Retail Store',
            'online_marketplace' => 'Online Marketplace',
            'distributor' => 'Distributor',
            'reseller_mlm' => 'Reseller/MLM',
            default => ucfirst(str_replace('_', ' ', $this->channel_type))
        };
    }

    // Generate next channel code
    public static function getNextChannelCode(): string
    {
        $count = self::count() + 1;
        return 'CHN-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Calculate commission for amount
    public function calculateCommission(float $amount): float
    {
        return round($amount * ($this->commission_rate / 100), 2);
    }

    // Calculate discounted price
    public function calculateDiscountedPrice(float $basePrice): float
    {
        return round($basePrice * (1 - ($this->discount_rate / 100)), 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('channel_type', $type);
    }

    // Relationships
    public function pricing()
    {
        return $this->hasMany(ChannelPricing::class);
    }

    public function inventory()
    {
        return $this->hasMany(ChannelInventory::class);
    }

    public function salesPerformance()
    {
        return $this->hasMany(ChannelSalesPerformance::class);
    }
}
