<?php

namespace App\Http\Controllers;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;
use App\Services\EcommerceService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

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
