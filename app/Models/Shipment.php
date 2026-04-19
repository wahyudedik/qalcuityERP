<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'logistics_provider_id',
        'order_id',
        'tracking_number',
        'service_type',
        'status',
        'origin_city',
        'destination_city',
        'weight_kg',
        'shipping_cost',
        'tracking_history',
        'shipped_at',
        'delivered_at',
        'estimated_delivery',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tracking_history' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_delivery' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function provider()
    {
        return $this->belongsTo(LogisticsProvider::class, 'logistics_provider_id');
    }
}