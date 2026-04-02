<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;

class EcommerceProductMapping extends Model
{
    use AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'product_id',
        'external_sku',
        'external_product_id',
        'external_url',
        'price_override',
        'is_active',
        'last_stock_sync_at',
        'last_price_sync_at',
    ];

    protected $casts = [
        'price_override'      => 'decimal:2',
        'is_active'           => 'boolean',
        'last_stock_sync_at'  => 'datetime',
        'last_price_sync_at'  => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class, 'channel_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
