<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'channel_id', 'external_order_id', 'customer_name',
        'customer_phone', 'items', 'subtotal', 'shipping_cost', 'total',
        'status', 'payment_method', 'shipping_address', 'courier',
        'tracking_number', 'ordered_at', 'synced_at',
    ];

    protected $casts = [
        'items'           => 'array',
        'shipping_address'=> 'array',
        'subtotal'        => 'float',
        'shipping_cost'   => 'float',
        'total'           => 'float',
        'ordered_at'      => 'datetime',
        'synced_at'       => 'datetime',
    ];

    public function channel() { return $this->belongsTo(EcommerceChannel::class, 'channel_id'); }
}
