<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'menu_id',
        'category_id',
        'name',
        'description',
        'price',
        'cost',
        'image_path',
        'allergens',
        'dietary_info',
        'preparation_time',
        'is_available',
        'daily_limit',
        'sold_today',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'allergens' => 'array',
            'dietary_info' => 'array',
            'preparation_time' => 'integer',
            'is_available' => 'boolean',
            'daily_limit' => 'integer',
            'sold_today' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenu::class, 'menu_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(FbOrderItem::class);
    }

    public function minibarInventories(): HasMany
    {
        return $this->hasMany(MinibarInventory::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Get recipe ingredients through recipes
     * Note: RecipeIngredient no longer has menu_item_id; use recipes()->ingredients instead
     */
    public function recipeIngredients(): HasManyThrough
    {
        return $this->hasManyThrough(RecipeIngredient::class, Recipe::class);
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->price == 0) {
            return 0;
        }

        return (($this->price - $this->cost) / $this->price) * 100;
    }

    /**
     * Check if item can still be ordered today
     */
    public function canBeOrderedToday(): bool
    {
        if (! $this->is_available) {
            return false;
        }

        if ($this->daily_limit && $this->sold_today >= $this->daily_limit) {
            return false;
        }

        return true;
    }

    /**
     * Increment sold count
     */
    public function incrementSold(int $quantity = 1): void
    {
        $this->increment('sold_today', $quantity);
    }

    /**
     * Reset daily sales counter
     */
    public static function resetDailyCounters(int $tenantId): void
    {
        static::where('tenant_id', $tenantId)->update(['sold_today' => 0]);
    }

    /**
     * Calculate recipe cost from ingredients
     */
    public function calculateRecipeCost(): float
    {
        $ingredients = $this->recipeIngredients()->with('inventoryItem')->get();

        if ($ingredients->isEmpty()) {
            return $this->cost; // Return manual cost if no recipe
        }

        $totalCost = 0;
        foreach ($ingredients as $ingredient) {
            $costPerUnit = $ingredient->inventoryItem?->unit_cost ?? $ingredient->cost_per_unit ?? 0;
            $totalCost += ($ingredient->quantity ?? 0) * $costPerUnit;
        }

        return round($totalCost, 2);
    }

    /**
     * Update cost based on recipe ingredients
     */
    public function updateCostFromRecipe(): void
    {
        $calculatedCost = $this->calculateRecipeCost();
        $this->update(['cost' => $calculatedCost]);
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginPercentAttribute(): float
    {
        if ($this->price == 0) {
            return 0;
        }

        return (($this->price - $this->cost) / $this->price) * 100;
    }

    /**
     * Check if recipe is complete (has all ingredients)
     */
    public function hasCompleteRecipe(): bool
    {
        return $this->recipeIngredients()->count() > 0;
    }

    /**
     * Get recipe ingredients with availability check
     */
    public function getRecipeAvailability(): array
    {
        $ingredients = $this->recipeIngredients()->with('inventoryItem')->get();
        $available = true;
        $unavailableItems = [];

        foreach ($ingredients as $ingredient) {
            $inventoryItem = $ingredient->inventoryItem;
            if ($inventoryItem && $inventoryItem->current_stock < ($ingredient->quantity ?? 0)) {
                $available = false;
                $unavailableItems[] = [
                    'supply_name' => $inventoryItem->name,
                    'required' => $ingredient->quantity,
                    'available' => $inventoryItem->current_stock,
                    'unit' => $inventoryItem->unit ?? $ingredient->unit,
                ];
            }
        }

        return [
            'available' => $available,
            'unavailable_items' => $unavailableItems,
        ];
    }
}
