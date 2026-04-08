<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recipe - Untuk cost calculation
 */
class Recipe extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'menu_item_id',
        'name',
        'description',
        'yield_quantity', // berapa porsi yang dihasilkan
        'yield_unit', // pcs, liters, kg
        'preparation_time_minutes',
        'cooking_time_minutes',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'yield_quantity' => 'decimal:2',
            'preparation_time_minutes' => 'integer',
            'cooking_time_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Calculate total recipe cost
     */
    public function calculateTotalCost(): float
    {
        return $this->ingredients->sum(function ($ingredient) {
            return $ingredient->calculateCost();
        });
    }

    /**
     * Calculate cost per serving
     */
    public function calculateCostPerServing(): float
    {
        if ($this->yield_quantity <= 0) {
            return 0;
        }

        return $this->calculateTotalCost() / $this->yield_quantity;
    }

    /**
     * Get profit margin based on menu item price
     */
    public function getProfitMargin(): float
    {
        if (!$this->menuItem || $this->menuItem->price <= 0) {
            return 0;
        }

        $costPerServing = $this->calculateCostPerServing();
        return (($this->menuItem->price - $costPerServing) / $this->menuItem->price) * 100;
    }
}
