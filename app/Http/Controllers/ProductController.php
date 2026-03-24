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

class ProductController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
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

        return view('products.index', compact('products', 'categories', 'warehouses', 'lowCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'sku'               => 'nullable|string|max:100',
            'category'          => 'nullable|string|max:100',
            'unit'              => 'required|string|max:50',
            'price_sell'        => 'required|numeric|min:0',
            'price_buy'         => 'nullable|numeric|min:0',
            'stock_min'         => 'nullable|integer|min:0',
            'description'       => 'nullable|string',
            'has_expiry'        => 'boolean',
            'expiry_alert_days' => 'nullable|integer|min:1|max:365',
            'initial_stock'     => 'nullable|integer|min:0',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
            'batch_number'      => 'nullable|string|max:100',
            'expiry_date'       => 'nullable|date|after:today',
            'manufacture_date'  => 'nullable|date',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            if ($product->image && str_starts_with($product->image, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
            }
            $data['image'] = Storage::url($request->file('image')->store('products', 'public'));
        } else {
            unset($data['image']);
        }

        $old = $product->getOriginal();
        $product->update($data);
        ActivityLog::record('product_updated', "Produk diperbarui: {$product->name}", $product, $old, $product->fresh()->toArray());

        return back()->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function toggleActive(Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Produk {$product->name} berhasil {$status}.");
    }

    public function destroy(Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $hasSales = \App\Models\SalesOrderItem::where('product_id', $product->id)->exists();
        if ($hasSales) {
            $product->update(['is_active' => false]);
            ActivityLog::record('product_deactivated', "Produk dinonaktifkan (sudah pernah terjual): {$product->name}", $product);
            return back()->with('success', "Produk dinonaktifkan karena sudah pernah terjual.");
        }

        ActivityLog::record('product_deleted', "Produk dihapus: {$product->name} (SKU: {$product->sku})", $product, $product->toArray());
        $product->productStocks()->delete();
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }
}
