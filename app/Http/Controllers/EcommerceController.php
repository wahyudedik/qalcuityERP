<?php

namespace App\Http\Controllers;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;
use App\Models\EcommerceProductMapping;
use App\Models\Product;
use App\Services\EcommerceService;
use App\Services\MarketplaceSyncService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EcommerceController extends Controller
{
    public function __construct(private EcommerceService $ecommerce) {}

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $channels = EcommerceChannel::where('tenant_id', $tenantId)->get();
        $orders   = EcommerceOrder::where('tenant_id', $tenantId)
            ->with('channel')
            ->latest('ordered_at')
            ->paginate(30);

        return view('ecommerce.index', compact('channels', 'orders'));
    }

    public function dashboard()
    {
        $tid = auth()->user()->tenant_id;

        $channels     = EcommerceChannel::where('tenant_id', $tid)->withCount('orders')->get();
        $totalOrders  = EcommerceOrder::where('tenant_id', $tid)->count();
        $todayOrders  = EcommerceOrder::where('tenant_id', $tid)->whereDate('created_at', today())->count();
        $weekOrders   = EcommerceOrder::where('tenant_id', $tid)->where('created_at', '>=', now()->subWeek())->count();
        $totalRevenue = EcommerceOrder::where('tenant_id', $tid)->sum('total');

        // Collect sync_errors from all channels, flatten and sort by time, take last 20
        $recentErrors = $channels->flatMap(function ($channel) {
            $errors = $channel->sync_errors ?? [];
            return collect($errors)->map(fn($e) => array_merge($e, ['channel' => $channel->shop_name ?? $channel->platform]));
        })->sortByDesc('time')->take(20)->values();

        $syncLogs = \App\Models\MarketplaceSyncLog::where('tenant_id', $tid)
            ->orderByDesc('created_at')
            ->limit(20)
            ->with(['channel', 'mapping.product'])
            ->get();

        $failedCount = \App\Models\MarketplaceSyncLog::where('tenant_id', $tid)
            ->where('status', 'failed')
            ->count();

        return view('ecommerce.dashboard', compact(
            'channels',
            'totalOrders',
            'todayOrders',
            'weekOrders',
            'totalRevenue',
            'recentErrors',
            'syncLogs',
            'failedCount'
        ));
    }

    public function mappings(EcommerceChannel $channel)
    {
        $tid = auth()->user()->tenant_id;
        abort_if($channel->tenant_id !== $tid, 403);

        $mappings = EcommerceProductMapping::where('channel_id', $channel->id)
            ->with('product')
            ->paginate(30);

        $products = Product::where('tenant_id', $tid)->orderBy('name')->get();

        // After loading mappings, get price histories for mapped products
        $productIds = $mappings->pluck('product_id')->unique();
        $priceHistories = \App\Models\ProductPriceHistory::whereIn('product_id', $productIds)
            ->where('tenant_id', $tid)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('product_id');

        return view('ecommerce.mappings', compact('channel', 'mappings', 'products', 'priceHistories'));
    }

    public function storeMapping(Request $request, EcommerceChannel $channel)
    {
        $tid = auth()->user()->tenant_id;
        abort_if($channel->tenant_id !== $tid, 403);

        $request->validate([
            'product_id'          => 'required|exists:products,id',
            'external_sku'        => [
                'required',
                'string',
                Rule::unique('ecommerce_product_mappings')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->where('channel_id', $channel->id),
            ],
            'external_product_id' => 'nullable|string',
            'price_override'      => 'nullable|numeric|min:0',
        ], [
            'external_sku.unique' => 'SKU marketplace ini sudah digunakan di channel ini.',
        ]);

        EcommerceProductMapping::create([
            'tenant_id'           => $tid,
            'channel_id'          => $channel->id,
            'product_id'          => $request->product_id,
            'external_sku'        => $request->external_sku,
            'external_product_id' => $request->external_product_id,
            'price_override'      => $request->price_override,
        ]);

        ActivityLog::record('ecommerce_mapping_created', "Mapping produk ditambahkan ke channel {$channel->platform}");

        return back()->with('success', 'Mapping produk berhasil ditambahkan.');
    }

    public function destroyMapping(EcommerceProductMapping $mapping)
    {
        abort_if($mapping->channel->tenant_id !== auth()->user()->tenant_id, 403);

        $mapping->delete();

        ActivityLog::record('ecommerce_mapping_deleted', "Mapping produk dihapus dari channel {$mapping->channel->platform}");

        return back()->with('success', 'Mapping produk berhasil dihapus.');
    }

    public function syncStockManual(EcommerceChannel $channel)
    {
        $tid = auth()->user()->tenant_id;
        abort_if($channel->tenant_id !== $tid, 403);

        $result = app(MarketplaceSyncService::class)->syncStock($channel);

        $synced = $result['success'] ?? 0;
        $failed = $result['failed'] ?? 0;

        ActivityLog::record('ecommerce_stock_sync', "Sync stok manual: {$synced} berhasil, {$failed} gagal pada channel {$channel->platform}");

        return back()->with('success', "Sinkronisasi stok selesai: {$synced} produk berhasil, {$failed} gagal.");
    }

    public function syncPricesManual(EcommerceChannel $channel)
    {
        $tid = auth()->user()->tenant_id;
        abort_if($channel->tenant_id !== $tid, 403);

        $result = app(MarketplaceSyncService::class)->syncPrices($channel);

        $synced = $result['success'] ?? 0;
        $failed = $result['failed'] ?? 0;

        ActivityLog::record('ecommerce_price_sync', "Sync harga manual: {$synced} berhasil, {$failed} gagal pada channel {$channel->platform}");

        return back()->with('success', "Sinkronisasi harga selesai: {$synced} produk berhasil, {$failed} gagal.");
    }

    public function updateChannel(Request $request, EcommerceChannel $channel)
    {
        abort_if($channel->tenant_id !== auth()->user()->tenant_id, 403);

        $request->validate([
            'shop_name'          => 'required|string|max:100',
            'api_key'            => 'nullable|string|max:500',
            'api_secret'         => 'nullable|string|max:500',
            'stock_sync_enabled' => 'boolean',
            'price_sync_enabled' => 'boolean',
            'is_active'          => 'boolean',
        ]);

        $channel->update([
            'shop_name'          => $request->shop_name,
            'api_key'            => $request->api_key ?? $channel->api_key,
            'api_secret'         => $request->api_secret ?? $channel->api_secret,
            'stock_sync_enabled' => $request->boolean('stock_sync_enabled'),
            'price_sync_enabled' => $request->boolean('price_sync_enabled'),
            'is_active'          => $request->boolean('is_active'),
        ]);

        ActivityLog::record('ecommerce_channel_updated', "Channel {$channel->platform} diperbarui");

        return back()->with('success', 'Channel e-commerce berhasil diperbarui.');
    }

    public function destroyChannel(EcommerceChannel $channel)
    {
        abort_if($channel->tenant_id !== auth()->user()->tenant_id, 403);

        $platform = $channel->platform;
        $channel->delete();

        ActivityLog::record('ecommerce_channel_deleted', "Channel {$platform} dihapus");

        return redirect()->route('ecommerce.index')->with('success', "Channel {$platform} berhasil dihapus.");
    }

    public function storeChannel(Request $request)
    {
        $request->validate([
            'platform'   => 'required|in:shopee,tokopedia,lazada',
            'shop_name'  => 'required|string|max:100',
            'api_key'    => 'required|string|max:500',
            'api_secret' => 'required|string|max:500',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // updateOrCreate akan trigger mutator enkripsi otomatis
        EcommerceChannel::updateOrCreate(
            ['tenant_id' => $tenantId, 'platform' => $request->platform],
            [
                'shop_name'    => $request->shop_name,
                'shop_id'      => $request->shop_id,
                'api_key'      => $request->api_key,
                'api_secret'   => $request->api_secret,
                'access_token' => $request->access_token,
                'is_active'    => $request->boolean('is_active', true),
            ]
        );

        ActivityLog::record('ecommerce_channel_saved', "Channel {$request->platform} disimpan");

        return back()->with('success', 'Channel e-commerce disimpan.');
    }

    public function sync(Request $request, EcommerceChannel $channel)
    {
        abort_if($channel->tenant_id !== auth()->user()->tenant_id, 403);
        $count = $this->ecommerce->syncOrders($channel);
        $channel->update(['last_sync_at' => now()]);
        ActivityLog::record('ecommerce_sync', "Sync {$count} order dari {$channel->platform}");
        return back()->with('success', "{$count} order berhasil disinkronkan.");
    }
}
