<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialReservation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'product_id',
        'warehouse_id',
        'quantity_required',
        'quantity_reserved',
        'quantity_consumed',
        'status',
        'reserved_at',
        'consumed_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_required' => 'decimal:3',
            'quantity_reserved' => 'decimal:3',
            'quantity_consumed' => 'decimal:3',
            'reserved_at' => 'datetime',
            'consumed_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
