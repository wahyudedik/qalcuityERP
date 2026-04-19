<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\RecipeIngredient;

class RecipeCostCalculatorService
{
    /**
     * Calculate real-time recipe cost
     */
    public function calculateRecipeCost(Recipe $recipe): array
    {
        $ingredients = $recipe->ingredients()->with('inventoryItem')->get();

        $ingredientCosts = [];
        $totalCost = 0;

        foreach ($ingredients as $ingredient) {
            // Update cost from current inventory price if available
            if ($ingredient->inventoryItem) {
                $currentCost = $ingredient->inventoryItem->unit_cost ?? $ingredient->cost_per_unit;
            } else {
                $currentCost = $ingredient->cost_per_unit;
            }

            $lineTotal = $ingredient->quantity * $currentCost;
            $totalCost += $lineTotal;

            $ingredientCosts[] = [
                'name' => $ingredient->ingredient_name,
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit,
                'cost_per_unit' => $currentCost,
                'line_total' => round($lineTotal, 2),
                'percentage' => 0, // Will calculate below
            ];
        }

        // Calculate percentages
        if ($totalCost > 0) {
            foreach ($ingredientCosts as &$item) {
                $item['percentage'] = round(($item['line_total'] / $totalCost) * 100, 1);
            }
        }

        $costPerServing = $recipe->yield_quantity > 0
            ? $totalCost / $recipe->yield_quantity
            : 0;

        return [
            'recipe_id' => $recipe->id,
            'recipe_name' => $recipe->name,
            'yield_quantity' => $recipe->yield_quantity,
            'yield_unit' => $recipe->yield_unit,
            'total_cost' => round($totalCost, 2),
            'cost_per_serving' => round($costPerServing, 2),
            'ingredients' => $ingredientCosts,
            'profit_margin' => $this->calculateProfitMargin($recipe, $costPerServing),
            'last_updated' => now(),
        ];
    }

    /**
     * Calculate profit margin
     */
    private function calculateProfitMargin(Recipe $recipe, float $costPerServing): array
    {
        if (!$recipe->menuItem || $recipe->menuItem->price <= 0) {
            return [
                'selling_price' => 0,
                'cost_per_serving' => round($costPerServing, 2),
                'profit_per_serving' => 0,
                'margin_percentage' => 0,
                'is_profitable' => false,
            ];
        }

        $sellingPrice = $recipe->menuItem->price;
        $profitPerServing = $sellingPrice - $costPerServing;
        $marginPercentage = ($profitPerServing / $sellingPrice) * 100;

        return [
            'selling_price' => $sellingPrice,
            'cost_per_serving' => round($costPerServing, 2),
            'profit_per_serving' => round($profitPerServing, 2),
            'margin_percentage' => round($marginPercentage, 1),
            'is_profitable' => $profitPerServing > 0,
        ];
    }

    /**
     * Compare costs over time (if prices changed)
     */
    public function compareCostsOverTime(Recipe $recipe, int $daysBack = 30): array
    {
        $currentCost = $this->calculateRecipeCost($recipe);

        // For now, return current cost only
        // In production, you'd store historical cost snapshots
        return [
            'current' => $currentCost,
            'historical' => null, // Would need cost history table
            'change_percentage' => 0,
        ];
    }

    /**
     * Get recipes with low profit margins
     */
    public function getLowMarginRecipes(int $tenantId, float $threshold = 30.0)
    {
        $recipes = Recipe::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['menuItem', 'ingredients'])
            ->get()
            ->filter(function ($recipe) use ($threshold) {
                $margin = $recipe->getProfitMargin();
                return $margin < $threshold;
            })
            ->map(function ($recipe) {
                return [
                    'recipe' => $recipe,
                    'recipe_id' => $recipe->id,
                    'recipe_name' => $recipe->name,
                    'menu_item_name' => $recipe->menuItem?->name,
                    'selling_price' => $recipe->menuItem?->price ?? 0,
                    'cost_per_serving' => $recipe->calculateCostPerServing(),
                    'margin_percentage' => $recipe->getProfitMargin(),
                    'profit_margin' => $recipe->getProfitMargin(),
                    'recommendation' => $this->generateRecommendation($recipe),
                ];
            });

        return $recipes->sortBy('margin_percentage')->values();
    }

    /**
     * Generate recommendation for improving margin
     */
    private function generateRecommendation(Recipe $recipe): string
    {
        $margin = $recipe->getProfitMargin();

        if ($margin < 0) {
            return 'URGENT: Recipe is losing money. Increase price or reduce ingredient costs.';
        }

        if ($margin < 20) {
            return 'Consider increasing selling price or finding cheaper ingredient alternatives.';
        }

        if ($margin < 30) {
            return 'Margin is acceptable but could be improved. Review ingredient costs.';
        }

        return 'Healthy profit margin. Maintain current pricing.';
    }

    /**
     * Bulk update recipe costs from inventory
     */
    public function bulkUpdateCostsFromInventory(int $tenantId): int
    {
        $updated = 0;

        RecipeIngredient::where('tenant_id', $tenantId)
            ->with('inventoryItem')
            ->chunk(100, function ($ingredients) use (&$updated) {
                foreach ($ingredients as $ingredient) {
                    $ingredient->updateCostFromInventory();
                    $updated++;
                }
            });

        return $updated;
    }
}
