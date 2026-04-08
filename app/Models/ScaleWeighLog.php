<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScaleWeighLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'scale_id',
        'product_id',
        'warehouse_id',
        'weight',
        'unit',
        'tare_weight',
        'net_weight',
        'reference_type', // goods_receipt, stock_opname, production, etc
        'reference_id',
        'weighed_by',
        'weigh_time',
        'raw_data',
        'status', // pending, processed, error
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'tare_weight' => 'decimal:4',
            'net_weight' => 'decimal:4',
            'weigh_time' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scale(): BelongsTo
    {
        return $this->belongsTo(SmartScale::class, 'scale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function weighedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'weighed_by');
    }
}
