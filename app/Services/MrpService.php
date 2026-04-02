<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class MrpService
{
    /**
     * Run MRP calculation for a single BOM + quantity.
     * Returns array of material requirements with stock status.
     */
    public function calculate(Bom $bom, float $quantity, int $tenantId): array
    {
        $exploded = $bom->explode($quantity);

        // Aggregate same product_id
        $aggregated = [];
        foreach ($exploded as $item) {
            $pid = $item['product_id'];
            if (isset($aggregated[$pid])) {
                $aggregated[$pid]['quantity'] += $item['quantity'];
            } else {
                $aggregated[$pid] = $item;
            }
        }

        // Get current stock for all products
        $productIds = array_keys($aggregated);
        $stocks = ProductStock::whereIn('product_id', $productIds)
            ->whereHas('warehouse', fn($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->selectRaw('product_id, SUM(quantity) as total_stock')
            ->groupBy('product_id')
            ->pluck('total_stock', 'product_id');

        // Get pending PO quantities (ordered but not fully received)
        $pendingPo = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.tenant_id', $tenantId)
            ->whereIn('purchase_orders.status', ['confirmed', 'partial'])
            ->whereIn('purchase_order_items.product_id', $productIds)
            ->selectRaw('purchase_order_items.product_id, SUM(purchase_order_items.quantity_ordered - purchase_order_items.quantity_received) as pending_qty')
            ->groupBy('purchase_order_items.product_id')
            ->pluck('pending_qty', 'purchase_order_items.product_id');

        // Get pending WO demand (other WOs that need same materials)
        $pendingWoDemand = $this->getPendingWoDemand($tenantId, $productIds);

        // Load product names
        $products = Product::whereIn('id', $productIds)->pluck('name', 'id');

        $results = [];
        foreach ($aggregated as $pid => $item) {
            $onHand    = (float) ($stocks[$pid] ?? 0);
            $onOrder   = (float) ($pendingPo[$pid] ?? 0);
            $otherDemand = (float) ($pendingWoDemand[$pid] ?? 0);
            $available = $onHand + $onOrder - $otherDemand;
            $shortage  = max(0, $item['quantity'] - $available);

            $results[] = [
                'product_id'   => $pid,
                'product_name' => $products[$pid] ?? "Product #{$pid}",
                'unit'         => $item['unit'],
                'required'     => round($item['quantity'], 3),
                'on_hand'      => round($onHand, 3),
                'on_order'     => round($onOrder, 3),
                'other_demand' => round($otherDemand, 3),
                'available'    => round($available, 3),
                'shortage'     => round($shortage, 3),
                'level'        => $item['level'],
            ];
        }

        return $results;
    }

    /**
     * Run MRP for all pending/in-progress WOs.
     */
    public function runFullMrp(int $tenantId): array
    {
        $workOrders = WorkOrder::with('bom')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('materials_consumed', false)
            ->whereNotNull('bom_id')
            ->get();

        $allRequirements = [];

        foreach ($workOrders as $wo) {
            if (!$wo->bom) continue;
            $exploded = $wo->bom->explode($wo->target_quantity);
            foreach ($exploded as $item) {
                $pid = $item['product_id'];
                if (isset($allRequirements[$pid])) {
                    $allRequirements[$pid]['quantity'] += $item['quantity'];
                    $allRequirements[$pid]['wo_refs'][] = $wo->number;
                } else {
                    $allRequirements[$pid] = $item;
                    $allRequirements[$pid]['wo_refs'] = [$wo->number];
                }
            }
        }

        // Same stock/PO check as single calculate
        $productIds = array_keys($allRequirements);
        if (empty($productIds)) return [];

        $stocks = ProductStock::whereIn('product_id', $productIds)
            ->whereHas('warehouse', fn($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->selectRaw('product_id, SUM(quantity) as total_stock')
            ->groupBy('product_id')
            ->pluck('total_stock', 'product_id');

        $pendingPo = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.tenant_id', $tenantId)
            ->whereIn('purchase_orders.status', ['confirmed', 'partial'])
            ->whereIn('purchase_order_items.product_id', $productIds)
            ->selectRaw('purchase_order_items.product_id, SUM(purchase_order_items.quantity_ordered - purchase_order_items.quantity_received) as pending_qty')
            ->groupBy('purchase_order_items.product_id')
            ->pluck('pending_qty', 'purchase_order_items.product_id');

        $products = Product::whereIn('id', $productIds)->pluck('name', 'id');

        $results = [];
        foreach ($allRequirements as $pid => $item) {
            $onHand   = (float) ($stocks[$pid] ?? 0);
            $onOrder  = (float) ($pendingPo[$pid] ?? 0);
            $available = $onHand + $onOrder;
            $shortage  = max(0, $item['quantity'] - $available);

            $results[] = [
                'product_id'   => $pid,
                'product_name' => $products[$pid] ?? "Product #{$pid}",
                'unit'         => $item['unit'],
                'required'     => round($item['quantity'], 3),
                'on_hand'      => round($onHand, 3),
                'on_order'     => round($onOrder, 3),
                'available'    => round($available, 3),
                'shortage'     => round($shortage, 3),
                'wo_refs'      => $item['wo_refs'],
            ];
        }

        // Sort shortages first
        usort($results, fn($a, $b) => $b['shortage'] <=> $a['shortage']);

        return $results;
    }

    /**
     * Consume materials from stock when WO starts production.
     * Deducts raw materials based on BOM explosion.
     */
    public function consumeMaterials(WorkOrder $wo): array
    {
        if ($wo->materials_consumed) {
            return ['success' => false, 'message' => 'Material sudah dikonsumsi sebelumnya.'];
        }

        $bom = $wo->bom;
        if (!$bom) {
            return ['success' => false, 'message' => 'Work Order tidak memiliki BOM.'];
        }

        $tenantId = $wo->tenant_id;
        $exploded = $bom->explode($wo->target_quantity);

        // Aggregate
        $aggregated = [];
        foreach ($exploded as $item) {
            $pid = $item['product_id'];
            if (isset($aggregated[$pid])) {
                $aggregated[$pid]['quantity'] += $item['quantity'];
            } else {
                $aggregated[$pid] = $item;
            }
        }

        $warehouse = Warehouse::where('tenant_id', $tenantId)->where('is_active', true)->first();
        if (!$warehouse) {
            return ['success' => false, 'message' => 'Tidak ada gudang aktif.'];
        }

        $totalMaterialCost = 0;
        $consumed = [];
        $shortages = [];

        DB::transaction(function () use ($aggregated, $warehouse, $wo, $tenantId, &$totalMaterialCost, &$consumed, &$shortages) {
            foreach ($aggregated as $pid => $item) {
                $stock = ProductStock::where('product_id', $pid)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                $currentQty = $stock ? (float) $stock->quantity : 0;

                if ($currentQty < $item['quantity']) {
                    $product = Product::find($pid);
                    $shortages[] = ($product->name ?? "#{$pid}") . " (butuh {$item['quantity']}, stok {$currentQty})";
                    // Still consume what's available
                }

                $consumeQty = min($item['quantity'], $currentQty);
                if ($consumeQty <= 0) continue;

                $before = $currentQty;
                if ($stock) {
                    $stock->decrement('quantity', $consumeQty);
                }

                StockMovement::create([
                    'tenant_id'       => $tenantId,
                    'product_id'      => $pid,
                    'warehouse_id'    => $warehouse->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'out',
                    'quantity'        => $consumeQty,
                    'quantity_before' => $before,
                    'quantity_after'  => $before - $consumeQty,
                    'reference'       => $wo->number,
                    'notes'           => "Konsumsi material produksi {$wo->number}",
                ]);

                $product = Product::find($pid);
                $unitCost = $product->price_buy ?? 0;
                $totalMaterialCost += $unitCost * $consumeQty;

                $consumed[] = [
                    'product_id' => $pid,
                    'name'       => $product->name ?? "#{$pid}",
                    'quantity'   => $consumeQty,
                    'cost'       => $unitCost * $consumeQty,
                ];
            }

            $wo->update([
                'materials_consumed' => true,
                'material_cost'      => $totalMaterialCost,
            ]);
        });

        return [
            'success'       => true,
            'consumed'      => $consumed,
            'shortages'     => $shortages,
            'material_cost' => $totalMaterialCost,
        ];
    }

    /**
     * Get pending WO demand for given product IDs.
     */
    private function getPendingWoDemand(int $tenantId, array $productIds): array
    {
        // This is a simplified version — in production you'd cache BOM explosions
        $demand = [];
        $wos = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('materials_consumed', false)
            ->whereNotNull('bom_id')
            ->with('bom')
            ->get();

        foreach ($wos as $wo) {
            if (!$wo->bom) continue;
            $exploded = $wo->bom->explode($wo->target_quantity);
            foreach ($exploded as $item) {
                if (in_array($item['product_id'], $productIds)) {
                    $demand[$item['product_id']] = ($demand[$item['product_id']] ?? 0) + $item['quantity'];
                }
            }
        }

        return $demand;
    }
}
