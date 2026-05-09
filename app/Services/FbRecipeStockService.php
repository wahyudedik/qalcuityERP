<?php

namespace App\Services;

use App\Models\FbOrder;
use App\Models\FbSupply;
use App\Models\FbSupplyTransaction;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Log;

/**
 * FbRecipeStockService - Validate recipe ingredient stock before allowing orders
 *
 * BUG-FB-001 FIX: Prevent selling menu items when ingredients are insufficient
 *
 * This service ensures that:
 * 1. Menu items cannot be ordered if ingredients are out of stock
 * 2. Real-time stock validation before order creation
 * 3. Automatic menu item availability update based on stock
 * 4. Clear error messages showing which ingredients are missing
 */
class FbRecipeStockService
{
    /**
     * BUG-FB-001 FIX: Check if menu item can be made with current stock
     *
     * @param  int  $quantity  Number of servings requested
     * @return array Validation result with details
     */
    public function canMakeMenuItem(MenuItem $menuItem, int $quantity = 1): array
    {
        // If no recipe defined, assume it can be made
        if (! $menuItem->hasCompleteRecipe()) {
            return [
                'can_make' => true,
                'message' => 'Menu item ini tidak memiliki recipe (stock check skipped).',
                'limiting_quantity' => 999, // Unlimited
            ];
        }

        // Get all recipe ingredients
        $ingredients = $menuItem->recipeIngredients()->with('supply')->get();

        if ($ingredients->isEmpty()) {
            return [
                'can_make' => true,
                'message' => 'Tidak ada ingredients yang perlu dicek.',
                'limiting_quantity' => 999,
            ];
        }

        $issues = [];
        $limitingQuantity = PHP_INT_MAX;
        $limitingIngredient = null;

        foreach ($ingredients as $ingredient) {
            $supply = $ingredient->supply;

            // Calculate required quantity for requested servings
            $requiredQty = $ingredient->quantity_required * $quantity;

            // Check if sufficient stock
            if ($supply->current_stock < $requiredQty) {
                $issues[] = [
                    'supply_id' => $supply->id,
                    'supply_name' => $supply->name,
                    'required' => $requiredQty,
                    'available' => $supply->current_stock,
                    'shortage' => $requiredQty - $supply->current_stock,
                    'unit' => $ingredient->unit,
                ];

                // Calculate max quantity possible with this ingredient
                $maxPossible = floor($supply->current_stock / $ingredient->quantity_required);

                if ($maxPossible < $limitingQuantity) {
                    $limitingQuantity = $maxPossible;
                    $limitingIngredient = $supply->name;
                }
            }
        }

        $canMake = empty($issues);

        return [
            'can_make' => $canMake,
            'menu_item_id' => $menuItem->id,
            'menu_item_name' => $menuItem->name,
            'requested_quantity' => $quantity,
            'limiting_quantity' => $limitingQuantity === PHP_INT_MAX ? 999 : $limitingQuantity,
            'limiting_ingredient' => $limitingIngredient,
            'issues' => $issues,
            'message' => $canMake
                ? "Stock mencukupi untuk {$quantity} porsi."
                : "Stock tidak mencukupi. Max: {$limitingQuantity} porsi (limiting: {$limitingIngredient}).",
        ];
    }

    /**
     * BUG-FB-001 FIX: Validate all items in order before creation
     *
     * @param  array  $items  Array of ['menu_item_id' => X, 'quantity' => Y]
     * @return array Validation result
     */
    public function validateOrderItems(array $items): array
    {
        $validationResults = [];
        $allValid = true;
        $errors = [];

        foreach ($items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);

            if (! $menuItem) {
                $allValid = false;
                $errors[] = "Menu item ID {$item['menu_item_id']} tidak ditemukan.";

                continue;
            }

            // Check if menu item is available
            if (! $menuItem->is_available) {
                $allValid = false;
                $errors[] = "{$menuItem->name} tidak tersedia saat ini.";

                continue;
            }

            // Check recipe stock
            $stockCheck = $this->canMakeMenuItem($menuItem, $item['quantity']);

            $validationResults[] = [
                'menu_item_id' => $menuItem->id,
                'menu_item_name' => $menuItem->name,
                'requested_quantity' => $item['quantity'],
                'validation' => $stockCheck,
            ];

            if (! $stockCheck['can_make']) {
                $allValid = false;
                $errors[] = $stockCheck['message'];
            }
        }

        return [
            'valid' => $allValid,
            'items' => $validationResults,
            'errors' => $errors,
            'message' => $allValid
                ? 'Semua item tersedia dan stock mencukupi.'
                : 'Beberapa item tidak tersedia: '.implode(', ', $errors),
        ];
    }

    /**
     * BUG-FB-001 FIX: Deduct ingredient stock when order is completed
     *
     * @param  FbOrder  $order
     * @return array Result with details
     */
    public function deductIngredientStock($order): array
    {
        $deductions = [];
        $errors = [];

        foreach ($order->items as $orderItem) {
            $menuItem = $orderItem->menuItem;

            if (! $menuItem || ! $menuItem->hasCompleteRecipe()) {
                continue; // Skip if no recipe
            }

            $ingredients = $menuItem->recipeIngredients()->with('supply')->get();

            foreach ($ingredients as $ingredient) {
                $supply = $ingredient->supply;
                $requiredQty = $ingredient->quantity_required * $orderItem->quantity;

                // Double-check stock before deduction
                if ($supply->current_stock < $requiredQty) {
                    $errors[] = "Stock {$supply->name} tidak mencukupi. Required: {$requiredQty}, Available: {$supply->current_stock}";

                    continue;
                }

                // Deduct stock
                $oldStock = $supply->current_stock;
                $supply->decrement('current_stock', $requiredQty);

                // Log transaction
                FbSupplyTransaction::create([
                    'tenant_id' => $supply->tenant_id,
                    'supply_id' => $supply->id,
                    'type' => 'out',
                    'quantity' => $requiredQty,
                    'unit' => $ingredient->unit,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number} - {$menuItem->name} x{$orderItem->quantity}",
                    'created_by' => $order->created_by,
                ]);

                $deductions[] = [
                    'supply_id' => $supply->id,
                    'supply_name' => $supply->name,
                    'quantity_deducted' => $requiredQty,
                    'old_stock' => $oldStock,
                    'new_stock' => $supply->fresh()->current_stock,
                    'unit' => $ingredient->unit,
                ];

                // Check if stock is now below minimum
                if ($supply->current_stock <= $supply->minimum_stock) {
                    Log::warning('F&B Supply low stock alert', [
                        'supply_id' => $supply->id,
                        'supply_name' => $supply->name,
                        'current_stock' => $supply->current_stock,
                        'minimum_stock' => $supply->minimum_stock,
                    ]);
                }
            }
        }

        return [
            'success' => empty($errors),
            'deductions' => $deductions,
            'errors' => $errors,
            'message' => empty($errors)
                ? 'Stock berhasil dikurangi untuk '.count($deductions).' ingredients.'
                : 'Beberapa error: '.implode(', ', $errors),
        ];
    }

    /**
     * BUG-FB-001 FIX: Update menu item availability based on stock
     *
     * Should be run periodically or after stock changes
     *
     * @return array Summary of changes
     */
    public function updateMenuAvailability(int $tenantId): array
    {
        $menuItems = MenuItem::where('tenant_id', $tenantId)
            ->where('is_available', true)
            ->get();

        $updated = [];
        $stillAvailable = 0;
        $nowUnavailable = 0;

        foreach ($menuItems as $menuItem) {
            if (! $menuItem->hasCompleteRecipe()) {
                continue; // Skip items without recipes
            }

            $stockCheck = $this->canMakeMenuItem($menuItem, 1);

            if (! $stockCheck['can_make'] && $menuItem->is_available) {
                // Mark as unavailable
                $menuItem->update(['is_available' => false]);
                $nowUnavailable++;
                $updated[] = [
                    'menu_item_id' => $menuItem->id,
                    'menu_item_name' => $menuItem->name,
                    'action' => 'marked_unavailable',
                    'reason' => $stockCheck['message'],
                ];

                Log::info('F&B Menu item marked unavailable', [
                    'menu_item_id' => $menuItem->id,
                    'menu_item_name' => $menuItem->name,
                    'reason' => $stockCheck['message'],
                ]);
            } elseif ($stockCheck['can_make'] && ! $menuItem->is_available) {
                // Mark as available again
                $menuItem->update(['is_available' => true]);
                $updated[] = [
                    'menu_item_id' => $menuItem->id,
                    'menu_item_name' => $menuItem->name,
                    'action' => 'marked_available',
                ];

                Log::info('F&B Menu item marked available', [
                    'menu_item_id' => $menuItem->id,
                    'menu_item_name' => $menuItem->name,
                ]);
            } else {
                $stillAvailable++;
            }
        }

        return [
            'total_checked' => $menuItems->count(),
            'still_available' => $stillAvailable,
            'now_unavailable' => $nowUnavailable,
            'now_available' => count($updated) - $nowUnavailable,
            'updated_items' => $updated,
        ];
    }

    /**
     * Get stock availability report for all menu items
     */
    public function getStockAvailabilityReport(int $tenantId): array
    {
        $menuItems = MenuItem::where('tenant_id', $tenantId)
            ->with('recipeIngredients.supply')
            ->get();

        $report = [];

        foreach ($menuItems as $menuItem) {
            $stockCheck = $this->canMakeMenuItem($menuItem, 1);

            $report[] = [
                'menu_item_id' => $menuItem->id,
                'menu_item_name' => $menuItem->name,
                'is_available' => $menuItem->is_available,
                'has_recipe' => $menuItem->hasCompleteRecipe(),
                'can_make' => $stockCheck['can_make'],
                'max_servings' => $stockCheck['limiting_quantity'],
                'limiting_ingredient' => $stockCheck['limiting_ingredient'] ?? null,
                'issues' => $stockCheck['issues'] ?? [],
            ];
        }

        return $report;
    }

    /**
     * Get low stock ingredients that will affect menu items
     */
    public function getLowStockImpact(int $tenantId): array
    {
        $lowStockSupplies = FbSupply::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->get();

        $impact = [];

        foreach ($lowStockSupplies as $supply) {
            // Find menu items that use this ingredient
            $affectedMenuItems = MenuItem::whereHas('recipeIngredients', function ($query) use ($supply) {
                $query->where('supply_id', $supply->id);
            })->get();

            if ($affectedMenuItems->isNotEmpty()) {
                $impact[] = [
                    'supply_id' => $supply->id,
                    'supply_name' => $supply->name,
                    'current_stock' => $supply->current_stock,
                    'minimum_stock' => $supply->minimum_stock,
                    'unit' => $supply->unit,
                    'affected_menu_items' => $affectedMenuItems->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'is_available' => $item->is_available,
                    ]),
                ];
            }
        }

        return $impact;
    }
}
