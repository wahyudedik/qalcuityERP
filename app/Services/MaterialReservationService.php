<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\Bom;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MaterialReservationService - Prevent material double-allocation across Work Orders
 * 
 * BUG-MFG-002 FIX: Atomic material reservation with conflict detection
 * 
 * Problems Fixed:
 * 1. No reservation when WO created - materials can be consumed by other WOs
 * 2. No check if materials already reserved by another WO
 * 3. Race condition during material consumption
 * 4. No release of reserved materials when WO cancelled
 */
class MaterialReservationService
{
    /**
     * BUG-MFG-002 FIX: Reserve materials for Work Order
     * 
     * Checks availability considering already reserved quantities
     * 
     * @param WorkOrder $workOrder
     * @return array
     */
    public function reserveMaterials(WorkOrder $workOrder): array
    {
        if (!$workOrder->bom_id && !$workOrder->recipe_id) {
            return [
                'success' => false,
                'message' => 'Work Order must have BOM or Recipe to reserve materials.',
            ];
        }

        return DB::transaction(function () use ($workOrder) {
            // Get required materials from BOM or Recipe
            $requiredMaterials = $this->getRequiredMaterials($workOrder);

            if (empty($requiredMaterials)) {
                return [
                    'success' => false,
                    'message' => 'No materials required for this Work Order.',
                ];
            }

            $reservations = [];
            $shortages = [];

            // Get active warehouse
            $warehouse = \App\Models\Warehouse::where('tenant_id', $workOrder->tenant_id)
                ->where('is_active', true)
                ->first();

            if (!$warehouse) {
                return [
                    'success' => false,
                    'message' => 'No active warehouse found.',
                ];
            }

            foreach ($requiredMaterials as $material) {
                $productId = $material['product_id'];
                $requiredQty = $material['quantity'];

                // BUG-MFG-002 FIX: Lock stock row for update
                $stock = ProductStock::where('product_id', $productId)
                    ->where('warehouse_id', $warehouse->id)
                    ->lockForUpdate() // Prevent concurrent modifications
                    ->first();

                if (!$stock) {
                    $shortages[] = [
                        'product_id' => $productId,
                        'product_name' => $material['product_name'],
                        'required' => $requiredQty,
                        'available' => 0,
                        'reserved' => 0,
                        'shortage' => $requiredQty,
                    ];
                    continue;
                }

                // Calculate already reserved quantity for this product
                $alreadyReserved = $this->getReservedQuantity($productId, $warehouse->id, $workOrder->id);

                // Available = Physical stock - Reserved by other WOs
                $availableForReservation = $stock->quantity - $alreadyReserved;

                if ($availableForReservation < $requiredQty) {
                    $shortages[] = [
                        'product_id' => $productId,
                        'product_name' => $material['product_name'],
                        'required' => $requiredQty,
                        'available' => $stock->quantity,
                        'reserved_by_others' => $alreadyReserved,
                        'available_for_reservation' => $availableForReservation,
                        'shortage' => $requiredQty - $availableForReservation,
                    ];
                    continue;
                }

                // BUG-MFG-002 FIX: Create reservation record
                $reservation = \App\Models\MaterialReservation::updateOrCreate(
                    [
                        'tenant_id' => $workOrder->tenant_id,
                        'work_order_id' => $workOrder->id,
                        'product_id' => $productId,
                        'warehouse_id' => $warehouse->id,
                    ],
                    [
                        'quantity_required' => $requiredQty,
                        'quantity_reserved' => $requiredQty,
                        'quantity_consumed' => 0,
                        'status' => 'reserved',
                        'reserved_at' => now(),
                    ]
                );

                $reservations[] = [
                    'product_id' => $productId,
                    'product_name' => $material['product_name'],
                    'quantity' => $requiredQty,
                    'reservation_id' => $reservation->id,
                ];
            }

            // If any shortages, rollback and return error
            if (!empty($shortages)) {
                return [
                    'success' => false,
                    'message' => 'Insufficient materials. Some items are already reserved by other Work Orders.',
                    'shortages' => $shortages,
                    'reservations' => $reservations,
                ];
            }

            // Update WO status
            $workOrder->update(['materials_reserved' => true]);

            Log::info('MFG: Materials reserved for WO', [
                'wo_id' => $workOrder->id,
                'wo_number' => $workOrder->number,
                'materials_count' => count($reservations),
            ]);

            return [
                'success' => true,
                'message' => 'Materials successfully reserved.',
                'reservations' => $reservations,
                'shortages' => [],
            ];
        });
    }

    /**
     * BUG-MFG-002 FIX: Release reserved materials (when WO cancelled)
     * 
     * @param WorkOrder $workOrder
     * @return array
     */
    public function releaseMaterials(WorkOrder $workOrder): array
    {
        return DB::transaction(function () use ($workOrder) {
            $reservations = \App\Models\MaterialReservation::where('work_order_id', $workOrder->id)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                $reservation->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);
            }

            $workOrder->update(['materials_reserved' => false]);

            Log::info('MFG: Materials released for WO', [
                'wo_id' => $workOrder->id,
                'wo_number' => $workOrder->number,
                'materials_count' => $reservations->count(),
            ]);

            return [
                'success' => true,
                'message' => 'Materials released successfully.',
                'released_count' => $reservations->count(),
            ];
        });
    }

    /**
     * BUG-MFG-002 FIX: Consume reserved materials (atomic)
     * 
     * @param WorkOrder $workOrder
     * @return array
     */
    public function consumeMaterials(WorkOrder $workOrder): array
    {
        if (!$workOrder->materials_reserved) {
            return [
                'success' => false,
                'message' => 'Materials not reserved. Please reserve materials first.',
            ];
        }

        return DB::transaction(function () use ($workOrder) {
            $reservations = \App\Models\MaterialReservation::where('work_order_id', $workOrder->id)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->get();

            $warehouse = \App\Models\Warehouse::where('tenant_id', $workOrder->tenant_id)
                ->where('is_active', true)
                ->first();

            $consumed = [];
            $totalCost = 0;

            foreach ($reservations as $reservation) {
                // Lock stock for update
                $stock = ProductStock::where('product_id', $reservation->product_id)
                    ->where('warehouse_id', $reservation->warehouse_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $consumeQty = $reservation->quantity_reserved;

                if ($stock->quantity < $consumeQty) {
                    throw new \Exception(
                        "Insufficient stock for {$reservation->product->name}. " .
                        "Required: {$consumeQty}, Available: {$stock->quantity}"
                    );
                }

                // Atomic decrement
                $before = $stock->quantity;
                $stock->decrement('quantity', $consumeQty);

                // Get product cost
                $product = $reservation->product;
                $unitCost = $product->cost_price ?? 0;
                $lineCost = $unitCost * $consumeQty;
                $totalCost += $lineCost;

                // Create stock movement
                StockMovement::create([
                    'tenant_id' => $workOrder->tenant_id,
                    'product_id' => $reservation->product_id,
                    'warehouse_id' => $reservation->warehouse_id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $consumeQty,
                    'quantity_before' => $before,
                    'quantity_after' => $before - $consumeQty,
                    'reference' => $workOrder->number,
                    'notes' => "Material consumption for WO {$workOrder->number}",
                ]);

                // Update reservation
                $reservation->update([
                    'status' => 'consumed',
                    'quantity_consumed' => $consumeQty,
                    'consumed_at' => now(),
                ]);

                $consumed[] = [
                    'product_id' => $reservation->product_id,
                    'product_name' => $reservation->product->name,
                    'quantity' => $consumeQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineCost,
                ];
            }

            // Update WO
            $workOrder->update([
                'materials_consumed' => true,
                'material_cost' => $totalCost,
            ]);

            Log::info('MFG: Materials consumed for WO', [
                'wo_id' => $workOrder->id,
                'wo_number' => $workOrder->number,
                'total_cost' => $totalCost,
            ]);

            return [
                'success' => true,
                'consumed' => $consumed,
                'material_cost' => $totalCost,
            ];
        });
    }

    /**
     * Get reserved quantity for a product (excluding specific WO)
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int|null $excludeWoId
     * @return float
     */
    public function getReservedQuantity(int $productId, int $warehouseId, ?int $excludeWoId = null): float
    {
        $query = \App\Models\MaterialReservation::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'reserved');

        if ($excludeWoId) {
            $query->where('work_order_id', '!=', $excludeWoId);
        }

        return $query->sum('quantity_reserved');
    }

    /**
     * Get available quantity (physical - reserved)
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int|null $excludeWoId
     * @return array
     */
    public function getAvailableQuantity(int $productId, int $warehouseId, ?int $excludeWoId = null): array
    {
        $stock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $physicalQty = $stock ? $stock->quantity : 0;
        $reservedQty = $this->getReservedQuantity($productId, $warehouseId, $excludeWoId);
        $availableQty = max(0, $physicalQty - $reservedQty);

        return [
            'product_id' => $productId,
            'physical_quantity' => $physicalQty,
            'reserved_quantity' => $reservedQty,
            'available_quantity' => $availableQty,
        ];
    }

    /**
     * Get required materials from BOM or Recipe
     */
    protected function getRequiredMaterials(WorkOrder $workOrder): array
    {
        $materials = [];

        if ($workOrder->bom_id) {
            $bom = $workOrder->bom()->with('items.product')->first();
            if ($bom) {
                $multiplier = $workOrder->target_quantity / $bom->batch_size;

                foreach ($bom->items as $item) {
                    $materials[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'Product #' . $item->product_id,
                        'quantity' => $item->quantity * $multiplier,
                    ];
                }
            }
        } elseif ($workOrder->recipe_id) {
            $recipe = $workOrder->recipe()->with('ingredients.product')->first();
            if ($recipe) {
                $multiplier = $workOrder->target_quantity / $recipe->batch_size;

                foreach ($recipe->ingredients as $ingredient) {
                    $materials[] = [
                        'product_id' => $ingredient->product_id,
                        'product_name' => $ingredient->product->name ?? 'Product #' . $ingredient->product_id,
                        'quantity' => $ingredient->quantity * $multiplier,
                    ];
                }
            }
        }

        return $materials;
    }
}
