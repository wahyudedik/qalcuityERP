<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommercePlatform extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'platform',
        'store_name',
        'store_url',
        'api_key',
        'api_secret',
        'access_token',
        'configuration',
        'is_active',
        'auto_sync_inventory',
        'auto_sync_orders',
        'sync_interval_minutes',
        'last_sync_at',
        'last_order_sync_at',
        'last_inventory_sync_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'auto_sync_inventory' => 'boolean',
        'auto_sync_orders' => 'boolean',
        'sync_interval_minutes' => 'integer',
        'last_sync_at' => 'datetime',
        'last_order_sync_at' => 'datetime',
        'last_inventory_sync_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function orders()
    {
        return $this->hasMany(EcommerceOrder::class);
    }

    public function getPlatformNameAttribute(): string
    {
        return match ($this->platform) {
            'shopify' => 'Shopify',
            'woocommerce' => 'WooCommerce',
            'tokopedia' => 'Tokopedia',
            'shopee' => 'Shopee',
            'lazada' => 'Lazada',
            default => ucfirst($this->platform)
        };
    }
}