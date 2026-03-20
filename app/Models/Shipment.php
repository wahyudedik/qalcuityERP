<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tenant_id', 'model_type', 'model_id', 'courier', 'service',
        'tracking_number', 'origin', 'destination', 'weight',
        'cost', 'status', 'estimated_delivery', 'delivered_at', 'notes',
    ];

    protected $casts = [
        'estimated_delivery' => 'date',
        'delivered_at'       => 'datetime',
        'cost'               => 'float',
        'weight'             => 'float',
    ];

    public function subject() { return $this->morphTo('model'); }
}
