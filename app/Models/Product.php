<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'sku', 'barcode', 'category', 'unit',
        'price_buy', 'price_sell', 'stock_min', 'description', 'image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_buy'  => 'decimal:2',
            'price_sell' => 'decimal:2',
            'is_active'  => 'boolean',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function stockMovements(): HasMany { return $this->hasMany(StockMovement::class); }
    public function productStocks(): HasMany { return $this->hasMany(ProductStock::class); }

    public function stockInWarehouse(int $warehouseId): int
    {
        return $this->productStocks()->where('warehouse_id', $warehouseId)->value('quantity') ?? 0;
    }

    public function totalStock(): int
    {
        return $this->productStocks()->sum('quantity');
    }
}
