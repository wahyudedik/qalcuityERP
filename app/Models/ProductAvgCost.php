<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ProductAvgCost extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'product_id', 'warehouse_id',
        'avg_cost', 'total_qty', 'total_value',
    ];

    protected $casts = [
        'avg_cost'    => 'float',
        'total_qty'   => 'float',
        'total_value' => 'float',
    ];

    public function product()   { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
