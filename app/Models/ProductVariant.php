<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'sku',
        'variant_name',
        'barcode',
        'variant_attributes',
        'price',
        'cost_price',
        'stock_quantity',
        'reorder_level',
        'status',
        'notes',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(VariantAttribute::class, 'variant_id');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'variant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getFullSkuAttribute(): string
    {
        return $this->sku . ' (' . $this->variant_name . ')';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity > 0 AND stock_quantity <= reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeByFormula($query, int $formulaId)
    {
        return $query->where('formula_id', $formulaId);
    }
}