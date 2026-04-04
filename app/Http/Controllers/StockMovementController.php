<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\ProductStock;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function __construct(
        private BarcodeService $barcodeService
    ) {
    }

    /**
     * Show stock movement form with barcode scanning
     */
    public function create(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $warehouses = Warehouse::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('inventory.movements.create', compact('warehouses'));
    }

    /**
     * Store stock movement with barcode support
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment,transfer',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $tenantId = auth()->user()->tenant_id;
            $productId = $request->product_id;
            $warehouseId = $request->warehouse_id;
            $movementType = $request->type;
            $quantity = $request->quantity;

            // Get current stock
            $productStock = ProductStock::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $quantityBefore = $productStock->quantity;

            // Calculate new quantity based on movement type
            if ($movementType === 'in') {
                $productStock->quantity += $quantity;
            } elseif ($movementType === 'out') {
                if ($productStock->quantity < $quantity) {
                    return back()->withErrors(['quantity' => 'Stok tidak mencukupi'])->withInput();
                }
                $productStock->quantity -= $quantity;
            } elseif ($movementType === 'adjustment') {
                $productStock->quantity = $quantity; // Set to specific value
            }

            $productStock->save();

            $quantityAfter = $productStock->quantity;

            // Create stock movement record
            $movement = StockMovement::create([
                'tenant_id' => $tenantId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'type' => $movementType,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'notes' => $request->notes,
                'reference' => $request->reference,
            ]);

            DB::commit();

            return redirect()->route('inventory.movements.index')
                ->with('success', 'Stock movement berhasil dicatat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Lookup product by barcode
     */
    public function lookupByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
            ],
        ]);
    }

    /**
     * Get product stock at warehouse
     */
    public function getProductStock(Request $request)
    {
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');

        $stock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'quantity' => $stock?->quantity ?? 0,
                'warehouse_name' => $stock?->warehouse?->name ?? '',
            ],
        ]);
    }

    /**
     * Index of all stock movements
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = StockMovement::where('tenant_id', $tenantId)
            ->with(['product', 'warehouse', 'user']);

        // Filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
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

        $movements = $query->orderBy('created_at', 'desc')->paginate(50);
        $warehouses = Warehouse::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $products = Product::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('inventory.movements.index', compact('movements', 'warehouses', 'products'));
    }
}
