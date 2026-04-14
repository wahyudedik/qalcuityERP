<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'platform_id',
        'external_order_id',
        'internal_order_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'payment_status',
        'fulfillment_status',
        'line_items',
        'raw_data',
        'ordered_at',
        'synced_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'line_items' => 'array',
        'raw_data' => 'array',
        'ordered_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function platform()
    {
        return $this->belongsTo(EcommercePlatform::class);
    }
}