<?php

namespace App\Services;

use App\Models\CogsEntry;
use App\Models\ProductAvgCost;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Inventory Costing Service
 *
 * Supports three methods per tenant:
 *   simple — uses product.price_buy as static cost (default, zero-config)
 *   avco   — weighted average cost, recalculated on every stock-in
 *   fifo   — first-in first-out, consumes oldest batches first
 *
 * All methods are non-destructive to existing data.
 * Tenants on 'simple' are completely unaffected.
 */
class InventoryCostingService
{
    // ── Public API ────────────────────────────────────────────────────

    /**
     * Call this when stock comes IN (goods receipt, manual adjustment+).
     * Records cost_price on the movement and updates AVCO/FIFO layers.
     */
    public function recordStockIn(
        StockMovement $movement,
        float $costPrice,
        ?string $batchNumber = null,
    ): void {
        $method = $this->method($movement->tenant_id);
        if ($method === 'simple') return;

        $qty = abs($movement->quantity);

        // Persist cost on the movement
        $movement->update([
            'cost_price' => $costPrice,
            'cost_total' => $costPrice * $qty,
        ]);

        if ($method === 'avco') {
            $this->updateAvgCost($movement->tenant_id, $movement->product_id, $movement->warehouse_id, $qty, $costPrice);
        }

        if ($method === 'fifo') {
            $this->recordFifoBatch($movement, $qty, $costPrice, $batchNumber);
        }
    }

    /**
     * Call this when stock goes OUT (sale, manual adjustment-).
     * Calculates COGS and writes a CogsEntry. Returns unit cost used.
     */
    public function recordStockOut(
        StockMovement $movement,
        ?string $reference = null,
    ): float {
        $method   = $this->method($movement->tenant_id);
        $qty      = abs($movement->quantity);
        $unitCost = 0.0;

        if ($method === 'simple') {
            $unitCost = (float) $movement->product->price_buy;
        } elseif ($method === 'avco') {
            $unitCost = $this->getAvgCost($movement->tenant_id, $movement->product_id, $movement->warehouse_id);
            $this->consumeAvgCost($movement->tenant_id, $movement->product_id, $movement->warehouse_id, $qty);
        } elseif ($method === 'fifo') {
            $unitCost = $this->consumeFifoBatches($movement, $qty);
        }

        $totalCost = $unitCost * $qty;

        $movement->update([
            'cost_price' => $unitCost,
            'cost_total' => $totalCost,
        ]);

        CogsEntry::create([
            'tenant_id'         => $movement->tenant_id,
            'product_id'        => $movement->product_id,
            'warehouse_id'      => $movement->warehouse_id,
            'stock_movement_id' => $movement->id,
            'costing_method'    => $method,
            'quantity'          => $qty,
            'unit_cost'         => $unitCost,
            'total_cost'        => $totalCost,
            'reference'         => $reference,
            'date'              => $movement->created_at?->toDateString() ?? today()->toDateString(),
        ]);

        return $unitCost;
    }

    /**
     * Get current unit cost for a product (used for valuation/display).
     */
    public function getCurrentCost(int $tenantId, int $productId, int $warehouseId): float
    {
        $method = $this->method($tenantId);

        if ($method === 'simple') {
            return (float) \App\Models\Product::find($productId)?->price_buy ?? 0;
        }

        if ($method === 'avco') {
            return $this->getAvgCost($tenantId, $productId, $warehouseId);
        }

        // FIFO: cost of oldest remaining batch
        $oldest = ProductBatch::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->first();

        return $oldest ? (float) $oldest->cost_price : (float) \App\Models\Product::find($productId)?->price_buy ?? 0;
    }

    /**
     * Inventory valuation report for a tenant.
     * Returns per-product cost, qty, and total value.
     */
    public function valuationReport(int $tenantId): array
    {
        $method = $this->method($tenantId);

        $stocks = DB::table('product_stocks as ps')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->join('warehouses as w', 'w.id', '=', 'ps.warehouse_id')
            ->where('p.tenant_id', $tenantId)
            ->where('ps.quantity', '>', 0)
            ->select('ps.product_id', 'ps.warehouse_id', 'ps.quantity', 'p.name as product_name', 'p.sku', 'p.price_buy', 'w.name as warehouse_name')
            ->get();

        $rows = [];
        foreach ($stocks as $s) {
            $unitCost = match ($method) {
                'avco'  => $this->getAvgCost($tenantId, $s->product_id, $s->warehouse_id),
                'fifo'  => $this->getFifoLayerCost($tenantId, $s->product_id, $s->warehouse_id),
                default => (float) $s->price_buy,
            };

            $rows[] = [
                'product_id'     => $s->product_id,
                'product_name'   => $s->product_name,
                'sku'            => $s->sku,
                'warehouse_name' => $s->warehouse_name,
                'quantity'       => (int) $s->quantity,
                'unit_cost'      => $unitCost,
                'total_value'    => $unitCost * $s->quantity,
                'price_buy'      => (float) $s->price_buy,
                'costing_method' => $method,
            ];
        }

        return [
            'method' => $method,
            'rows'   => $rows,
            'total'  => array_sum(array_column($rows, 'total_value')),
        ];
    }

    /**
     * COGS summary for a date range.
     */
    public function cogsReport(int $tenantId, string $from, string $to): array
    {
        $entries = CogsEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->with('product')
            ->get();

        $byProduct = $entries->groupBy('product_id')->map(function ($group) {
            $first = $group->first();
            return [
                'product_id'   => $first->product_id,
                'product_name' => $first->product?->name ?? '—',
                'sku'          => $first->product?->sku ?? '',
                'qty_sold'     => $group->sum('quantity'),
                'total_cogs'   => $group->sum('total_cost'),
                'avg_unit_cost'=> $group->sum('quantity') > 0
                    ? $group->sum('total_cost') / $group->sum('quantity')
                    : 0,
            ];
        })->values()->toArray();

        return [
            'from'       => $from,
            'to'         => $to,
            'method'     => $this->method($tenantId),
            'rows'       => $byProduct,
            'total_cogs' => array_sum(array_column($byProduct, 'total_cogs')),
        ];
    }

    // ── AVCO helpers ──────────────────────────────────────────────────

    private function updateAvgCost(int $tenantId, int $productId, int $warehouseId, float $qty, float $costPrice): void
    {
        $record = ProductAvgCost::firstOrNew([
            'product_id'  => $productId,
            'warehouse_id'=> $warehouseId,
        ]);

        $newTotalQty   = $record->total_qty + $qty;
        $newTotalValue = $record->total_value + ($qty * $costPrice);
        $newAvg        = $newTotalQty > 0 ? $newTotalValue / $newTotalQty : $costPrice;

        $record->fill([
            'tenant_id'   => $tenantId,
            'avg_cost'    => $newAvg,
            'total_qty'   => $newTotalQty,
            'total_value' => $newTotalValue,
        ])->save();
    }

    private function consumeAvgCost(int $tenantId, int $productId, int $warehouseId, float $qty): void
    {
        $record = ProductAvgCost::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$record || $record->total_qty <= 0) return;

        $newTotalQty   = max(0, $record->total_qty - $qty);
        $newTotalValue = $record->avg_cost * $newTotalQty;

        $record->update([
            'total_qty'   => $newTotalQty,
            'total_value' => $newTotalValue,
        ]);
    }

    private function getAvgCost(int $tenantId, int $productId, int $warehouseId): float
    {
        $record = ProductAvgCost::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($record && $record->avg_cost > 0) return (float) $record->avg_cost;

        // Fallback: compute from existing movements
        $movements = StockMovement::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('type', 'in')
            ->where('cost_price', '>', 0)
            ->get();

        if ($movements->isEmpty()) {
            return (float) \App\Models\Product::find($productId)?->price_buy ?? 0;
        }

        $totalQty   = $movements->sum('quantity');
        $totalValue = $movements->sum('cost_total');
        return $totalQty > 0 ? $totalValue / $totalQty : 0;
    }

    // ── FIFO helpers ──────────────────────────────────────────────────

    private function recordFifoBatch(StockMovement $movement, float $qty, float $costPrice, ?string $batchNumber): void
    {
        // If a ProductBatch already exists (from batch tracking), update its cost
        if ($batchNumber) {
            ProductBatch::where('tenant_id', $movement->tenant_id)
                ->where('product_id', $movement->product_id)
                ->where('warehouse_id', $movement->warehouse_id)
                ->where('batch_number', $batchNumber)
                ->update([
                    'cost_price'         => $costPrice,
                    'quantity_remaining' => DB::raw('quantity'),
                ]);
            return;
        }

        // Create a synthetic FIFO layer batch
        ProductBatch::create([
            'tenant_id'          => $movement->tenant_id,
            'product_id'         => $movement->product_id,
            'warehouse_id'       => $movement->warehouse_id,
            'batch_number'       => 'FIFO-' . $movement->id,
            'quantity'           => $qty,
            'quantity_remaining' => $qty,
            'cost_price'         => $costPrice,
            'status'             => 'active',
            'expiry_date'        => now()->addYears(10)->toDateString(), // synthetic batch, far future
        ]);
    }

    private function consumeFifoBatches(StockMovement $movement, float $qty): float
    {
        $remaining = $qty;
        $totalCost = 0.0;

        // Oldest batches first (FIFO)
        $batches = ProductBatch::where('tenant_id', $movement->tenant_id)
            ->where('product_id', $movement->product_id)
            ->where('warehouse_id', $movement->warehouse_id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $take       = min($remaining, (float) $batch->quantity_remaining);
            $totalCost += $take * (float) $batch->cost_price;
            $remaining -= $take;

            $newRemaining = (float) $batch->quantity_remaining - $take;
            $batch->update([
                'quantity_remaining' => $newRemaining,
                'status'             => $newRemaining <= 0 ? 'consumed' : 'active',
            ]);
        }

        // If batches ran out (shouldn't happen in normal flow), fallback to price_buy
        if ($remaining > 0) {
            $fallback   = (float) $movement->product->price_buy ?? 0;
            $totalCost += $remaining * $fallback;
        }

        return $qty > 0 ? $totalCost / $qty : 0;
    }

    private function getFifoLayerCost(int $tenantId, int $productId, int $warehouseId): float
    {
        // Weighted average of remaining FIFO layers (for valuation display)
        $batches = ProductBatch::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->where('cost_price', '>', 0)
            ->get();

        if ($batches->isEmpty()) {
            return (float) \App\Models\Product::find($productId)?->price_buy ?? 0;
        }

        $totalQty   = $batches->sum('quantity_remaining');
        $totalValue = $batches->sum(fn($b) => $b->quantity_remaining * $b->cost_price);
        return $totalQty > 0 ? $totalValue / $totalQty : 0;
    }

    // ── Utility ───────────────────────────────────────────────────────

    private function method(int $tenantId): string
    {
        return Tenant::find($tenantId)?->costing_method ?? 'simple';
    }
}
