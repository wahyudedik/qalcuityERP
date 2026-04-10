<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportShipment extends Model
{
    use BelongsToTenant;

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

    protected function casts(): array
    {
        return [
            'shipment_date' => 'date',
            'estimated_arrival' => 'date',
            'actual_arrival' => 'date',
            'total_value' => 'decimal:2',
            'shipping_documents' => 'array',
        ];
    }

    public const STATUSES = [
        'preparing' => 'Preparing',
        'in_transit' => 'In Transit',
        'arrived' => 'Arrived',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customsDeclaration(): BelongsTo
    {
        return $this->belongsTo(CustomsDeclaration::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
