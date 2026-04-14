<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WarehouseStock — alias model untuk product_stocks yang merepresentasikan
 * stok produk per gudang. Digunakan oleh InventoryService untuk operasi
 * multi-warehouse dengan pessimistic locking.
 */
class WarehouseStock extends Model
{
    protected $table = 'product_stocks';

    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
