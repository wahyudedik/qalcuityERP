<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'tenant_id', 'product_id', 'name', 'batch_size', 'batch_unit', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'batch_size' => 'decimal:3',
            'is_active'  => 'boolean',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function ingredients(): HasMany { return $this->hasMany(RecipeIngredient::class); }

    /**
     * Hitung HPP per unit produk jadi.
     * HPP = SUM(ingredient.product.price_buy * qty_per_batch) / batch_size
     */
    public function calculateHpp(): float
    {
        $totalCost = 0;

        foreach ($this->ingredients()->with('product')->get() as $ingredient) {
            $totalCost += ($ingredient->product->price_buy ?? 0) * $ingredient->quantity_per_batch;
        }

        return $this->batch_size > 0 ? $totalCost / $this->batch_size : 0;
    }

    /**
     * Qty bahan baku yang dibutuhkan per unit produk jadi.
     */
    public function qtyPerUnit(RecipeIngredient $ingredient): float
    {
        return $this->batch_size > 0
            ? $ingredient->quantity_per_batch / $this->batch_size
            : $ingredient->quantity_per_batch;
    }
}
