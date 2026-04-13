<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'sku',
        'variant_name',
        'attributes',
        'size',
        'unit',
        'price_adjustment',
        'cost_adjustment',
        'barcode',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attributes' => 'array',
        'size' => 'decimal:2',
        'price_adjustment' => 'decimal:2',
        'cost_adjustment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function attributes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VariantAttribute::class, 'variant_id');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getFullSkuAttribute(): string
    {
        return $this->sku . ' (' . $this->variant_name . ')';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFormula($query, int $formulaId)
    {
        return $query->where('formula_id', $formulaId);
    }
}
