<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\ActivityLog;
use App\Services\QueryCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use \App\Traits\DispatchesWebhooks;

    public function __construct(
        protected QueryCacheService $cacheService
    ) {
    }

    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        // Use cache for dropdown data (changes infrequently)
        $categories = $this->cacheService->remember("product_categories:{$tid}", function () use ($tid) {
            return Product::where('tenant_id', $tid)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category');
        }, 7200); // 2 hours

        $warehouses = $this->cacheService->remember("warehouses_list:{$tid}", function () use ($tid) {
            return Warehouse::where('tenant_id', $tid)
                ->where('is_active', true)
                ->get();
        }, 7200); // 2 hours

        // Products list - cache for 5 minutes (frequently changing)
        $filters = $request->only(['search', 'category', 'status']);
        $cacheKey = "products_index:{$tid}:" . md5(json_encode($filters));

        if ($request->has('no_cache')) {
            // Bypass cache for fresh data
            $products = $this->getProductsQuery($tid, $request)->orderBy('name')->paginate(20)->withQueryString();
        } else {
            $products = $this->cacheService->remember($cacheKey, function () use ($tid, $request) {
                return $this->getProductsQuery($tid, $request)->orderBy('name')->paginate(20)->withQueryString();
            }, 300); // 5 minutes
        }

        $lowCount = $this->cacheService->remember("products_low_count:{$tid}", function () use ($tid) {
            return Product::where('tenant_id', $tid)
                ->whereHas('productStocks', fn($q) => $q->whereColumn('quantity', '<=', 'products.stock_min'))
                ->count();
        }, 120); // 2 minutes

        return view('products.index', compact('products', 'categories', 'warehouses', 'lowCount'));
    }

    /**
     * Build products query (extracted for reuse)
     */
    protected function getProductsQuery(int $tid, Request $request)
    {
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

        return $query;
    }

    /**
     * Bulk operations for products
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate,update_price',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'nullable|in:fixed,percentage,increase_percentage,decrease_percentage',
        ]);

        $tenantId = $this->tenantId();
        $productIds = $request->product_ids;
        $action = $request->action;
        $affected = 0;

        // Verify all products belong to tenant
        $products = Product::where('tenant_id', $tenantId)
            ->whereIn('id', $productIds)
            ->get();

        if ($products->count() !== count($productIds)) {
            return back()->withErrors(['error' => 'Beberapa produk tidak valid atau bukan milik tenant Anda.']);
        }

        try {
            switch ($action) {
                case 'delete':
                    // Soft delete products
                    $affected = Product::where('tenant_id', $tenantId)
                        ->whereIn('id', $productIds)
                        ->delete();
                    break;

                case 'activate':
                    $affected = Product::where('tenant_id', $tenantId)
                        ->whereIn('id', $productIds)
                        ->update(['is_active' => true]);
                    break;

                case 'deactivate':
                    $affected = Product::where('tenant_id', $tenantId)
                        ->whereIn('id', $productIds)
                        ->update(['is_active' => false]);
                    break;

                case 'update_price':
                    $price = $request->price;
                    $priceType = $request->price_type ?? 'fixed';

                    // Update prices in bulk for better performance
                    foreach ($products as $product) {
                        $newPrice = $this->calculateNewPrice($product->selling_price, $price, $priceType);
                        Product::where('id', $product->id)->update(['selling_price' => $newPrice]);
                        $affected++;
                    }
                    break;
            }

            // Log activity
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => Auth::id(),
                'action' => "bulk_{$action}",
                'description' => "Bulk {$action} performed on {$affected} products",
                'metadata' => [
                    'product_ids' => $productIds,
                    'count' => $affected,
                ],
            ]);

            $actionLabels = [
                'delete' => 'dihapus',
                'activate' => 'diaktifkan',
                'deactivate' => 'dinonaktifkan',
                'update_price' => 'diperbarui harganya',
            ];

            return back()->with('success', "Berhasil: {$affected} produk {$actionLabels[$action]}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal melakukan bulk action: ' . $e->getMessage()]);
        }
    }

    /**
     * Calculate new price based on type
     */
    protected function calculateNewPrice(float $currentPrice, float $value, string $type): float
    {
        switch ($type) {
            case 'fixed':
                return $value;
            case 'percentage':
                return $currentPrice * ($value / 100);
            case 'increase_percentage':
                return $currentPrice * (1 + ($value / 100));
            case 'decrease_percentage':
                return $currentPrice * (1 - ($value / 100));
            default:
                return $currentPrice;
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'price_sell' => 'required|numeric|min:0',
            'price_buy' => 'nullable|numeric|min:0',
            'stock_min' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'has_expiry' => 'boolean',
            'expiry_alert_days' => 'nullable|integer|min:1|max:365',
            'initial_stock' => 'nullable|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date|after:today',
            'manufacture_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'tenant_id' => $tid,
            'name' => $data['name'],
            'sku' => $sku,
            'category' => $data['category'] ?? null,
            'unit' => $data['unit'],
            'price_sell' => $data['price_sell'],
            'price_buy' => $data['price_buy'] ?? 0,
            'stock_min' => $data['stock_min'] ?? 5,
            'description' => $data['description'] ?? null,
            'image' => $imagePath ? Storage::url($imagePath) : null,
            'is_active' => true,
            'has_expiry' => $request->boolean('has_expiry'),
            'expiry_alert_days' => $data['expiry_alert_days'] ?? 2,
        ]);

        ActivityLog::record('product_created', "Produk baru: {$product->name} (SKU: {$product->sku})", $product, [], $product->toArray());

        $this->fireWebhook('product.created', $product->toArray());

        if (!empty($data['initial_stock']) && $data['initial_stock'] > 0 && !empty($data['warehouse_id'])) {
            ProductStock::create([
                'product_id' => $product->id,
                'warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['initial_stock'],
            ]);
            StockMovement::create([
                'tenant_id' => $tid,
                'product_id' => $product->id,
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $data['initial_stock'],
                'quantity_before' => 0,
                'quantity_after' => $data['initial_stock'],
                'notes' => 'Stok awal produk baru',
            ]);

            if ($product->has_expiry && !empty($data['expiry_date'])) {
                ProductBatch::create([
                    'tenant_id' => $tid,
                    'product_id' => $product->id,
                    'warehouse_id' => $data['warehouse_id'],
                    'batch_number' => $data['batch_number'] ?? 'BATCH-' . strtoupper(substr($sku, 0, 4)) . '-' . now()->format('ymd'),
                    'quantity' => $data['initial_stock'],
                    'manufacture_date' => $data['manufacture_date'] ?? null,
                    'expiry_date' => $data['expiry_date'],
                    'status' => 'active',
                ]);
            }
        }

        return back()->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'price_sell' => 'required|numeric|min:0',
            'price_buy' => 'nullable|numeric|min:0',
            'stock_min' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
