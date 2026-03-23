<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid = $this->tenantId();
        $query = Product::where('tenant_id', $tid)->with('productStocks');

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"));
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->status === 'low') {
            $query->whereHas('productStocks', fn($q) => $q->whereColumn('quantity', '<=', 'products.stock_min'));
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Product::where('tenant_id', $tid)->whereNotNull('category')->distinct()->pluck('category');
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();
        $lowCount   = Product::where('tenant_id', $tid)
            ->whereHas('productStocks', fn($q) => $q->whereColumn('quantity', '<=', 'products.stock_min'))
            ->count();

        return view('inventory.index', compact('products', 'categories', 'warehouses', 'lowCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'nullable|string|max:100',
            'category'      => 'nullable|string|max:100',
            'unit'          => 'required|string|max:50',
            'price_sell'    => 'required|numeric|min:0',
            'price_buy'     => 'nullable|numeric|min:0',
            'stock_min'     => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'has_expiry'    => 'boolean',
            'expiry_alert_days' => 'nullable|integer|min:1|max:365',
            'initial_stock' => 'nullable|integer|min:0',
            'warehouse_id'  => 'nullable|exists:warehouses,id',
            'batch_number'  => 'nullable|string|max:100',
            'expiry_date'   => 'nullable|date|after:today',
            'manufacture_date' => 'nullable|date',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $tid = $this->tenantId();

        if (Product::where('tenant_id', $tid)->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => 'Produk dengan nama ini sudah ada.'])->withInput();
        }

        $sku = $data['sku'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']), 0, 6)) . '-' . rand(100, 999);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'tenant_id'         => $tid,
            'name'              => $data['name'],
            'sku'               => $sku,
            'category'          => $data['category'] ?? null,
            'unit'              => $data['unit'],
            'price_sell'        => $data['price_sell'],
            'price_buy'         => $data['price_buy'] ?? 0,
            'stock_min'         => $data['stock_min'] ?? 5,
            'description'       => $data['description'] ?? null,
            'image'             => $imagePath ? Storage::url($imagePath) : null,
            'is_active'         => true,
            'has_expiry'        => $request->boolean('has_expiry'),
            'expiry_alert_days' => $data['expiry_alert_days'] ?? 2,
        ]);

        ActivityLog::record('product_created', "Produk baru: {$product->name} (SKU: {$product->sku})", $product, [], $product->toArray());

        if (!empty($data['initial_stock']) && $data['initial_stock'] > 0 && !empty($data['warehouse_id'])) {
            ProductStock::create([
                'product_id'   => $product->id,
                'warehouse_id' => $data['warehouse_id'],
                'quantity'     => $data['initial_stock'],
            ]);
            StockMovement::create([
                'tenant_id'       => $tid,
                'product_id'      => $product->id,
                'warehouse_id'    => $data['warehouse_id'],
                'user_id'         => auth()->id(),
                'type'            => 'in',
                'quantity'        => $data['initial_stock'],
                'quantity_before' => 0,
                'quantity_after'  => $data['initial_stock'],
                'notes'           => 'Stok awal produk baru',
            ]);

            // Buat batch jika produk has_expiry dan expiry_date diisi
            if ($product->has_expiry && !empty($data['expiry_date'])) {
                ProductBatch::create([
                    'tenant_id'        => $tid,
                    'product_id'       => $product->id,
                    'warehouse_id'     => $data['warehouse_id'],
                    'batch_number'     => $data['batch_number'] ?? 'BATCH-' . strtoupper(substr($sku, 0, 4)) . '-' . now()->format('ymd'),
                    'quantity'         => $data['initial_stock'],
                    'manufacture_date' => $data['manufacture_date'] ?? null,
                    'expiry_date'      => $data['expiry_date'],
                    'status'           => 'active',
                ]);
            }
        }

        return back()->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'nullable|string|max:100',
            'category'    => 'nullable|string|max:100',
            'unit'        => 'required|string|max:50',
            'price_sell'  => 'required|numeric|min:0',
            'price_buy'   => 'nullable|numeric|min:0',
            'stock_min'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada dan tersimpan di storage lokal
            if ($product->image && str_starts_with($product->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = Storage::url($imagePath);
        } else {
            unset($data['image']);
        }

        $old = $product->getOriginal();
        $product->update($data);
        ActivityLog::record('product_updated', "Produk diperbarui: {$product->name}", $product, $old, $product->fresh()->toArray());

        return back()->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $hasSales = \App\Models\SalesOrderItem::where('product_id', $product->id)->exists();
        if ($hasSales) {
            $product->update(['is_active' => false]);
            ActivityLog::record('product_deactivated', "Produk dinonaktifkan (sudah pernah terjual): {$product->name}", $product);
            return back()->with('success', "Produk dinonaktifkan (sudah pernah terjual).");
        }

        ActivityLog::record('product_deleted', "Produk dihapus: {$product->name} (SKU: {$product->sku})", $product, $product->toArray());
        $product->productStocks()->delete();
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function addStock(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'warehouse_id'     => 'required|exists:warehouses,id',
            'quantity'         => 'required|integer|min:1',
            'notes'            => 'nullable|string',
            'batch_number'     => 'nullable|string|max:100',
            'expiry_date'      => 'nullable|date|after:today',
            'manufacture_date' => 'nullable|date',
        ]);

        // Validasi: jika produk has_expiry, expiry_date wajib
        if ($product->has_expiry && empty($data['expiry_date'])) {
            return back()->withErrors(['expiry_date' => 'Produk ini memerlukan tanggal expired.'])->withInput();
        }

        $stock = ProductStock::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $data['warehouse_id']],
            ['quantity' => 0]
        );

        $before = $stock->quantity;
        $stock->increment('quantity', $data['quantity']);

        StockMovement::create([
            'tenant_id'       => $this->tenantId(),
            'product_id'      => $product->id,
            'warehouse_id'    => $data['warehouse_id'],
            'user_id'         => auth()->id(),
            'type'            => 'in',
            'quantity'        => $data['quantity'],
            'quantity_before' => $before,
            'quantity_after'  => $before + $data['quantity'],
            'notes'           => $data['notes'] ?? null,
        ]);

        // Buat batch jika produk has_expiry
        if ($product->has_expiry && !empty($data['expiry_date'])) {
            ProductBatch::create([
                'tenant_id'        => $this->tenantId(),
                'product_id'       => $product->id,
                'warehouse_id'     => $data['warehouse_id'],
                'batch_number'     => $data['batch_number'] ?? 'BATCH-' . strtoupper(substr($product->sku, 0, 4)) . '-' . now()->format('ymd') . '-' . rand(10, 99),
                'quantity'         => $data['quantity'],
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'expiry_date'      => $data['expiry_date'],
                'status'           => 'active',
            ]);
        }

        ActivityLog::record('stock_added', "Stok ditambah: {$product->name} +{$data['quantity']} {$product->unit} (dari {$before} → " . ($before + $data['quantity']) . ")", $product);

        return back()->with('success', "Stok berhasil ditambah {$data['quantity']} {$product->unit}.");
    }

    public function batches(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $batches = ProductBatch::with('warehouse')
            ->where('product_id', $product->id)
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('inventory.batches', compact('product', 'batches'));
    }

    public function updateBatchStatus(Request $request, ProductBatch $batch)
    {
        abort_unless($batch->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'status' => 'required|in:active,expired,recalled,consumed',
        ]);

        $batch->update($data);

        return back()->with('success', "Status batch {$batch->batch_number} diperbarui.");
    }

    public function warehouses(Request $request)
    {
        $tid = $this->tenantId();
        $warehouses = Warehouse::where('tenant_id', $tid)->get();

        return view('inventory.warehouses', compact('warehouses'));
    }

    public function storeWarehouse(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $tid  = $this->tenantId();
        $code = $data['code'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']), 0, 4)) . '-' . rand(10, 99);

        Warehouse::create([
            'tenant_id' => $tid,
            'name'      => $data['name'],
            'code'      => $code,
            'address'   => $data['address'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function movements(Request $request)
    {
        $tid = $this->tenantId();
        $movements = StockMovement::where('tenant_id', $tid)
            ->with(['product', 'warehouse', 'user'])
            ->latest()
            ->paginate(30);

        return view('inventory.movements', compact('movements'));
    }
}
