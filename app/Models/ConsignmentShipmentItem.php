<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentShipmentItem extends Model
{
    protected $fillable = [
        'consignment_shipment_id', 'product_id', 'quantity_sent',
        'quantity_sold', 'quantity_returned', 'cost_price', 'retail_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity_sent'     => 'decimal:3',
            'quantity_sold'     => 'decimal:3',
            'quantity_returned' => 'decimal:3',
            'cost_price'        => 'decimal:2',
            'retail_price'      => 'decimal:2',
        ];
    }

    public function shipment(): BelongsTo { return $this->belongsTo(ConsignmentShipment::class, 'consignment_shipment_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function remainingQty(): float
    {
        return (float) $this->quantity_sent - (float) $this->quantity_sold - (float) $this->quantity_returned;
    }
}
