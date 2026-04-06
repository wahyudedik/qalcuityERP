<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'shipment_number',
        'customs_declaration_id',
        'transport_id',
        'shipment_date',
        'estimated_arrival',
        'actual_arrival',
        'origin_port',
        'destination_port',
        'shipping_method',
        'carrier_name',
        'tracking_number',
        'total_value',
        'incoterm',
        'status',
        'shipping_documents',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'estimated_arrival' => 'date',
        'actual_arrival' => 'date',
        'total_value' => 'decimal:2',
        'shipping_documents' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customsDeclaration()
    {
        return $this->belongsTo(CustomsDeclaration::class, 'customs_declaration_id');
    }

    public function transport()
    {
        return $this->belongsTo(RefrigeratedTransport::class, 'transport_id');
    }

    public function isInTransit(): bool
    {
        return $this->status === 'in_transit';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }
}
