<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseTransferController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid = $this->tid();

        // Ambil transfer dari stock movements dengan type 'transfer'
        $query = StockMovement::with(['product', 'warehouse', 'toWarehouse', 'user'])
            ->where('tenant_id', $tid)
            ->where('type', 'transfer');

        if ($request->filled('warehouse_id')) {
            $query->where(fn($q) => $q->where('warehouse_id', $request->warehouse_id)
                ->orWhere('to_warehouse_id', $request->warehouse_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transfers = $query->latest()->paginate(20)->withQueryString();
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();
        $products = Product::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        return view('inventory.transfers', compact('transfers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $tid = $this->tid();

        // Validasi kepemilikan gudang
        $fromWarehouse = Warehouse::where('id', $data['from_warehouse_id'])->where('tenant_id', $tid)->firstOrFail();
        $toWarehouse = Warehouse::where('id', $data['to_warehouse_id'])->where('tenant_id', $tid)->firstOrFail();

        $refNumber = 'TRF-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // BUG-INV-003 FIX: Wrap entire operation in transaction with locking
        try {
            DB::transaction(function () use ($data, $tid, $fromWarehouse, $toWarehouse, $refNumber) {
                foreach ($data['items'] as $item) {
                    $product = Product::find($item['product_id']);

                    // BUG-INV-003 FIX: Lock source stock row with pessimistic locking
                    $fromStock = ProductStock::where('product_id', $item['product_id'])
                        ->where('warehouse_id', $data['from_warehouse_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$fromStock || $fromStock->quantity < $item['quantity']) {
                        $available = $fromStock ? $fromStock->quantity : 0;
                        throw new \Exception(
                            "Stok {$product->name} di gudang {$fromWarehouse->name} tidak cukup. " .
                            "Tersedia: {$available} {$product->unit}, Dibutuhkan: {$item['quantity']}."
                        );
                    }

                    // Lock destination stock row
                    $toStock = ProductStock::where('product_id', $item['product_id'])
                        ->where('warehouse_id', $data['to_warehouse_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$toStock) {
                        // Create destination stock if doesn't exist
                        $toStock = ProductStock::create([
                            'product_id' => $item['product_id'],
                            'warehouse_id' => $data['to_warehouse_id'],
                            'quantity' => 0,
                        ]);
                    }

                    $beforeFrom = $fromStock->quantity;
                    $beforeTo = $toStock->quantity;

                    // BUG-INV-003 FIX: Atomic decrement with condition
                    $decremented = ProductStock::where('id', $fromStock->id)
                        ->where('quantity', '>=', $item['quantity'])
                        ->decrement('quantity', $item['quantity']);

                    if (!$decremented) {
                        throw new \Exception("Gagal mengurangi stok di gudang {$fromWarehouse->name}. Silakan coba lagi.");
                    }

                    // BUG-INV-003 FIX: Atomic increment with condition
                    $incremented = ProductStock::where('id', $toStock->id)
                        ->where('quantity', '=', $beforeTo)  // Ensure no concurrent modification
                        ->increment('quantity', $item['quantity']);

                    if (!$incremented) {
                        // CRITICAL: Rollback source decrement if destination increment fails
                        throw new \Exception(
                            "Gagal menambah stok di gudang {$toWarehouse->name}. " .
                            "Transfer dibatalkan untuk menghindari kehilangan stok."
                        );
                    }

                    // Catat movement (satu record dengan type transfer)
                    StockMovement::create([
                        'tenant_id' => $tid,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $data['from_warehouse_id'],
                        'to_warehouse_id' => $data['to_warehouse_id'],
                        'user_id' => auth()->id(),
                        'type' => 'transfer',
                        'quantity' => $item['quantity'],
                        'quantity_before' => $beforeFrom,
                        'quantity_after' => $beforeFrom - $item['quantity'],
                        'reference' => $refNumber,
                        'notes' => $data['notes'] ?? "Transfer {$fromWarehouse->name} → {$toWarehouse->name}",
                    ]);
                }

                ActivityLog::record(
                    'warehouse_transfer',
                    "Transfer stok {$refNumber}: {$fromWarehouse->name} → {$toWarehouse->name} (" . count($data['items']) . " produk)",
                    null
                );
            });

            return back()->with('success', "Transfer {$refNumber} berhasil. Stok dipindahkan dari {$fromWarehouse->name} ke {$toWarehouse->name}.");
        } catch (\Illuminate\Database\QueryException $e) {
            // Database-level errors (lock timeout, deadlock, etc.)
            \Log::error('Warehouse transfer database error', [
                'error' => $e->getMessage(),
                'from_warehouse' => $fromWarehouse->name,
                'to_warehouse' => $toWarehouse->name,
                'ref_number' => $refNumber,
            ]);

            return back()->withErrors([
                'error' => 'Gagal memproses transfer karena error database. Silakan coba lagi.'
            ])->withInput();
        } catch (\Exception $e) {
            // Business logic errors (insufficient stock, etc.)
            return back()->withErrors([
                'error' => $e->getMessage()
            ])->withInput();
        }
    }
}
