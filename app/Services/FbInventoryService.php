<?php

namespace App\Services;

use App\Models\FbOrder;
use App\Models\FbSupply;
use App\Models\FbSupplyTransaction;
use App\Models\MenuItem;
use App\Models\RecipeIngredient;
use Illuminate\Support\Facades\DB;

class FbInventoryService
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Automatically deduct stock when order is completed
     */
    public function deductStockForOrder(FbOrder $order): void
    {
        if ($order->status !== 'completed') {
            throw new \Exception('Order must be completed before deducting stock');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (! $item->menuItem) {
                    continue;
                }

                // Get recipe ingredients for this menu item
                $ingredients = RecipeIngredient::where('menu_item_id', $item->menu_item_id)
                    ->with('supply')
                    ->get();

                if ($ingredients->isEmpty()) {
                    continue; // Skip if no recipe defined
                }

                // Deduct stock for each ingredient based on quantity ordered
                foreach ($ingredients as $ingredient) {
                    $totalQuantityNeeded = $ingredient->quantity_required * $item->quantity;

                    $this->deductSupplyStock(
                        $ingredient->supply,
                        $totalQuantityNeeded,
                        "Order #{$order->order_number} - {$item->menuItem->name} (x{$item->quantity})"
                    );
                }
            }
        });
    }

    /**
     * Deduct supply stock with transaction recording
     */
    private function deductSupplyStock(FbSupply $supply, float $quantity, string $reference): void
    {
        if ($supply->current_stock < $quantity) {
            throw new \Exception("Insufficient stock for {$supply->name}. Available: {$supply->current_stock}, Required: {$quantity}");
        }

        $supply->decrement('current_stock', $quantity);

        // Record transaction
        FbSupplyTransaction::create([
            'tenant_id' => $this->tenantId,
            'supply_id' => $supply->id,
            'transaction_type' => 'usage',
            'quantity' => -$quantity,
            'unit_cost' => $supply->cost_per_unit,
            'total_cost' => -$quantity * $supply->cost_per_unit,
            'reference' => $reference,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Generate purchase orders for low stock items
     */
    public function generatePurchaseOrdersForLowStock(): array
    {
        $lowStockSupplies = FbSupply::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->get();

        if ($lowStockSupplies->isEmpty()) {
            return ['message' => 'No low stock items found'];
        }

        // Group by supplier for efficient ordering
        $suppliers = $lowStockSupplies->groupBy('supplier_name');
        $purchaseOrders = [];

        foreach ($suppliers as $supplierName => $supplies) {
            if (empty($supplierName)) {
                continue;
            }

            $poItems = [];
            $totalEstimatedCost = 0;

            foreach ($supplies as $supply) {
                // Calculate reorder quantity (3x minimum stock or based on usage patterns)
                $reorderQty = max($supply->minimum_stock * 2, 10);
                $estimatedCost = $reorderQty * $supply->cost_per_unit;

                $poItems[] = [
                    'supply_id' => $supply->id,
                    'supply_name' => $supply->name,
                    'current_stock' => $supply->current_stock,
                    'minimum_stock' => $supply->minimum_stock,
                    'reorder_quantity' => $reorderQty,
                    'unit' => $supply->unit,
                    'unit_cost' => $supply->cost_per_unit,
                    'estimated_cost' => round($estimatedCost, 2),
                ];

                $totalEstimatedCost += $estimatedCost;
            }

            $purchaseOrders[] = [
                'supplier_name' => $supplierName,
                'items' => $poItems,
                'total_items' => count($poItems),
                'total_estimated_cost' => round($totalEstimatedCost, 2),
                'generated_at' => now(),
            ];
        }

        return [
            'purchase_orders' => $purchaseOrders,
            'total_suppliers' => count($purchaseOrders),
            'total_low_stock_items' => $lowStockSupplies->count(),
        ];
    }

    /**
     * Check ingredient availability for menu items
     */
    public function checkMenuAvailability(): array
    {
        $menuItems = MenuItem::where('tenant_id', $this->tenantId)
            ->where('is_available', true)
            ->with('recipeIngredients.supply')
            ->get();

        $availability = [];

        foreach ($menuItems as $item) {
            $canMake = true;
            $limitingIngredient = null;
            $maxServings = PHP_INT_MAX;

            foreach ($item->recipeIngredients as $ingredient) {
                if ($ingredient->supply->current_stock <= 0) {
                    $canMake = false;
                    $limitingIngredient = $ingredient->supply->name;
                    break;
                }

                $possibleServings = floor($ingredient->supply->current_stock / $ingredient->quantity_required);

                if ($possibleServings < $maxServings) {
                    $maxServings = $possibleServings;
                    $limitingIngredient = $ingredient->supply->name;
                }
            }

            $availability[] = [
                'menu_item_id' => $item->id,
                'menu_item_name' => $item->name,
                'can_make' => $canMake && $maxServings > 0,
                'max_servings_possible' => $maxServings === PHP_INT_MAX ? 0 : $maxServings,
                'limiting_ingredient' => $limitingIngredient,
                'current_price' => $item->price,
                'calculated_cost' => $item->calculateRecipeCost(),
                'profit_margin' => $item->profit_margin_percent,
            ];
        }

        return $availability;
    }

    /**
     * Get inventory valuation report
     */
    public function getInventoryValuation(): array
    {
        $supplies = FbSupply::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        $totalValue = 0;
        $byCategory = [];

        foreach ($supplies as $supply) {
            $value = $supply->inventory_value;
            $totalValue += $value;

            $category = $supply->category_id ?? 'Uncategorized';
            if (! isset($byCategory[$category])) {
                $byCategory[$category] = [
                    'category' => $category,
                    'total_value' => 0,
                    'item_count' => 0,
                ];
            }

            $byCategory[$category]['total_value'] += $value;
            $byCategory[$category]['item_count']++;
        }

        return [
            'total_inventory_value' => round($totalValue, 2),
            'total_items' => $supplies->count(),
            'by_category' => array_values($byCategory),
            'low_stock_value' => round(
                FbSupply::where('tenant_id', $this->tenantId)
                    ->where('is_active', true)
                    ->whereColumn('current_stock', '<=', 'minimum_stock')
                    ->get()
                    ->sum(fn ($s) => $s->inventory_value),
                2
            ),
        ];
    }

    /**
     * Track batch and expiry dates for perishables
     */
    public function trackBatchExpiry(): array
    {
        // This would require additional batch tracking table
        // For now, return structure for future implementation
        return [
            'message' => 'Batch/expiry tracking requires additional database tables',
            'implementation_needed' => [
                'fb_supply_batches table',
                'batch_number',
                'expiry_date',
                'quantity_in_batch',
            ],
        ];
    }
}
