<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Recipe Ingredient - Bahan dalam resep
 */
class RecipeIngredient extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'recipe_id',
        'inventory_item_id',
        'ingredient_name',
        'quantity',
        'unit',
        'cost_per_unit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Calculate ingredient cost
     */
    public function calculateCost(): float
    {
        return $this->quantity * $this->cost_per_unit;
    }

    /**
     * Update cost from current inventory price
     */
    public function updateCostFromInventory(): void
    {
        if ($this->inventoryItem) {
            $this->update([
                'cost_per_unit' => $this->inventoryItem->unit_cost ?? 0,
            ]);
        }
    }
}
