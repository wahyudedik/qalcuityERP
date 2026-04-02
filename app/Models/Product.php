<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'barcode',
        'category',
        'unit',
        'price_buy',
        'price_sell',
        'stock_min',
        'description',
        'image',
        'is_active',
        'has_expiry',
        'expiry_alert_days',
    ];

    protected function casts(): array
    {
        return [
            'price_buy'         => 'decimal:2',
            'price_sell'        => 'decimal:2',
            'is_active'         => 'boolean',
            'has_expiry'        => 'boolean',
            'expiry_alert_days' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function stockInWarehouse(int $warehouseId): int
    {
        return $this->productStocks()->where('warehouse_id', $warehouseId)->value('quantity') ?? 0;
    }

    public function totalStock(): int
    {
        return $this->productStocks()->sum('quantity');
    }
}
