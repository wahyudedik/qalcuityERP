<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Http\Request;

class InventoryApiController extends ApiBaseController
{
    /**
     * Get stock levels
     */
    public function stockLevels(Request $request)
    {
        $query = Product::where('tenant_id', $this->getTenantId())
            ->with(['warehouse', 'category']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('stock', '<=', 'reorder_point');
        }

        $products = $query->paginate($request->get('per_page', 20));

        return $this->success($products);
    }

    /**
     * Get stock detail for product
     */
    public function stockDetail($productId)
    {
        $product = Product::where('tenant_id', $this->getTenantId())
            ->with([
                'warehouse',
                'category',
                'stockMovements' => function ($q) {
                    $q->latest()->limit(10);
                },
            ])
            ->findOrFail($productId);

        return $this->success($product);
    }

    /**
     * Get stock movements
     */
    public function movements(Request $request)
    {
        $query = StockMovement::where('tenant_id', $this->getTenantId())
            ->with(['product', 'warehouse', 'createdBy']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($movements);
    }

    /**
     * Record stock movement
     */
    public function recordMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,transfer_in,transfer_out,adjustment',
            'quantity' => 'required|numeric',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $movement = StockMovement::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'created_by' => auth()->id(),
        ]));

        // Update product stock
        $product = Product::where('tenant_id', $this->getTenantId())->findOrFail($validated['product_id']);
        if (in_array($validated['type'], ['in', 'transfer_in'])) {
            $product->increment('stock', $validated['quantity']);
        } else {
            $product->decrement('stock', $validated['quantity']);
        }

        return $this->success($movement, 'Stock movement recorded successfully', 201);
    }

    /**
     * Get stock adjustments
     */
    public function adjustments(Request $request)
    {
        $query = StockAdjustment::where('tenant_id', $this->getTenantId())
            ->with(['product', 'warehouse', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $adjustments = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($adjustments);
    }

    /**
     * Create stock adjustment
     */
    public function createAdjustment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $adjustment = StockAdjustment::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'created_by' => auth()->id(),
            'status' => 'approved',
        ]));

        // Update product stock
        $product = Product::where('tenant_id', $this->getTenantId())->findOrFail($validated['product_id']);
        if ($validated['adjustment_type'] === 'increase') {
            $product->increment('stock', $validated['quantity']);
        } else {
            $product->decrement('stock', $validated['quantity']);
        }

        return $this->success($adjustment, 'Stock adjustment created successfully', 201);
    }

    /**
     * Get stock transfers
     */
    public function transfers(Request $request)
    {
        $query = StockTransfer::where('tenant_id', $this->getTenantId())
            ->with(['product', 'sourceWarehouse', 'destinationWarehouse', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($transfers);
    }

    /**
     * Create stock transfer
     */
    public function createTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $transfer = StockTransfer::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'created_by' => auth()->id(),
            'status' => 'pending',
        ]));

        return $this->success($transfer, 'Stock transfer created successfully', 201);
    }

    /**
     * Update transfer status
     */
    public function updateTransferStatus(Request $request, $id)
    {
        $transfer = StockTransfer::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_transit,completed,cancelled',
        ]);

        $transfer->update($validated);

        // If completed, update stock
        if ($validated['status'] === 'completed') {
            $sourceProduct = Product::where('tenant_id', $this->getTenantId())
                ->where('id', $transfer->product_id)
                ->where('warehouse_id', $transfer->source_warehouse_id)
                ->first();

            $destProduct = Product::where('tenant_id', $this->getTenantId())
                ->where('id', $transfer->product_id)
                ->where('warehouse_id', $transfer->destination_warehouse_id)
                ->first();

            if ($sourceProduct) {
                $sourceProduct->decrement('stock', $transfer->quantity);
            }
            if ($destProduct) {
                $destProduct->increment('stock', $transfer->quantity);
            }
        }

        return $this->success($transfer, 'Transfer status updated successfully');
    }

    /**
     * Get inventory valuation
     */
    public function valuation(Request $request)
    {
        $products = Product::where('tenant_id', $this->getTenantId())
            ->selectRaw('SUM(stock * cost_price) as total_value')
            ->first();

        return $this->success([
            'total_value' => $products->total_value ?? 0,
            'currency' => 'IDR',
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts(Request $request)
    {
        $products = Product::where('tenant_id', $this->getTenantId())
            ->whereColumn('stock', '<=', 'reorder_point')
            ->with(['warehouse', 'category'])
            ->get();

        return $this->success($products);
    }

    /**
     * Record stock count
     */
    public function recordStockCount(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'actual_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::where('tenant_id', $this->getTenantId())
            ->where('id', $validated['product_id'])
            ->where('warehouse_id', $validated['warehouse_id'])
            ->firstOrFail();

        $difference = $validated['actual_quantity'] - $product->stock;

        $product->update(['stock' => $validated['actual_quantity']]);

        // Record movement
        StockMovement::create([
            'tenant_id' => $this->getTenantId(),
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'type' => 'adjustment',
            'quantity' => abs($difference),
            'notes' => 'Stock count adjustment: '.($validated['notes'] ?? ''),
            'created_by' => auth()->id(),
        ]);

        return $this->success([
            'product_id' => $product->id,
            'previous_stock' => $product->stock - $difference,
            'actual_stock' => $validated['actual_quantity'],
            'difference' => $difference,
        ], 'Stock count recorded successfully', 201);
    }

    /**
     * Get stock count history
     */
    public function stockCountHistory(Request $request)
    {
        $query = StockMovement::where('tenant_id', $this->getTenantId())
            ->where('type', 'adjustment')
            ->with(['product', 'warehouse', 'createdBy']);

        $history = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($history);
    }
}
