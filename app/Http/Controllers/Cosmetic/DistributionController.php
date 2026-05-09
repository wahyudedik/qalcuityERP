<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\ChannelInventory;
use App\Models\ChannelPricing;
use App\Models\ChannelSalesPerformance;
use App\Models\CosmeticFormula;
use App\Models\DistributionChannel;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Stats
        $stats = [
            'total_channels' => DistributionChannel::where('tenant_id', $tenantId)->count(),
            'active_channels' => DistributionChannel::where('tenant_id', $tenantId)->active()->count(),
            'retail_channels' => DistributionChannel::where('tenant_id', $tenantId)->byType('retail')->count(),
            'online_channels' => DistributionChannel::where('tenant_id', $tenantId)->byType('online_marketplace')->count(),
            'distributor_channels' => DistributionChannel::where('tenant_id', $tenantId)->byType('distributor')->count(),
        ];

        // Channels list
        $channels = DistributionChannel::where('tenant_id', $tenantId)
            ->when($request->type, fn ($q) => $q->byType($request->type))
            ->withCount(['pricing', 'inventory'])
            ->latest()
            ->paginate(15);

        return view('cosmetic.distribution.index', compact('stats', 'channels'));
    }

    public function storeChannel(Request $request)
    {
        $validated = $request->validate([
            'channel_name' => 'required|string|max:255',
            'channel_type' => 'required|in:retail,online_marketplace,distributor,reseller_mlm',
            'description' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['channel_code'] = DistributionChannel::getNextChannelCode();
        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;
        $validated['discount_rate'] = $validated['discount_rate'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        DistributionChannel::create($validated);

        return back()->with('success', 'Distribution channel created!');
    }

    public function pricingIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $channels = DistributionChannel::where('tenant_id', $tenantId)->active()->get();
        $formulas = CosmeticFormula::where('tenant_id', $tenantId)->get();

        $pricing = ChannelPricing::where('tenant_id', $tenantId)
            ->when($request->channel_id, fn ($q) => $q->where('channel_id', $request->channel_id))
            ->when($request->formula_id, fn ($q) => $q->where('formula_id', $request->formula_id))
            ->with(['channel', 'product'])
            ->latest()
            ->paginate(20);

        return view('cosmetic.distribution.pricing', compact('channels', 'formulas', 'pricing'));
    }

    public function storePricing(Request $request)
    {
        $validated = $request->validate([
            'channel_id' => 'required|exists:distribution_channels,id',
            'formula_id' => 'required|exists:cosmetic_formulas,id',
            'base_price' => 'required|numeric|min:0',
            'channel_price' => 'required|numeric|min:0',
            'minimum_order_quantity' => 'nullable|numeric|min:1',
            'bulk_discount_threshold' => 'nullable|numeric|min:0',
            'bulk_discount_rate' => 'nullable|numeric|min:0|max:100',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['minimum_order_quantity'] = $validated['minimum_order_quantity'] ?? 1;

        ChannelPricing::updateOrCreate(
            [
                'tenant_id' => $validated['tenant_id'],
                'channel_id' => $validated['channel_id'],
                'formula_id' => $validated['formula_id'],
            ],
            $validated
        );

        return back()->with('success', 'Channel pricing saved!');
    }

    public function inventoryIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $channels = DistributionChannel::where('tenant_id', $tenantId)->active()->get();

        $inventory = ChannelInventory::where('tenant_id', $tenantId)
            ->when($request->channel_id, fn ($q) => $q->where('channel_id', $request->channel_id))
            ->with(['channel', 'product'])
            ->latest()
            ->paginate(20);

        return view('cosmetic.distribution.inventory', compact('channels', 'inventory'));
    }

    public function restock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $inventory = ChannelInventory::where('tenant_id', $tenantId)->findOrFail($id);
        $inventory->restock($validated['quantity']);

        return back()->with('success', 'Stock added successfully!');
    }

    public function performanceIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Stats
        $stats = [
            'total_sales' => ChannelSalesPerformance::where('tenant_id', $tenantId)->thisMonth()->sum('total_sales'),
            'total_units' => ChannelSalesPerformance::where('tenant_id', $tenantId)->thisMonth()->sum('total_units'),
            'total_commission' => ChannelSalesPerformance::where('tenant_id', $tenantId)->thisMonth()->sum('total_commission'),
            'total_orders' => ChannelSalesPerformance::where('tenant_id', $tenantId)->thisMonth()->sum('order_count'),
        ];

        $channels = DistributionChannel::where('tenant_id', $tenantId)->active()->get();

        // Performance by channel
        $performance = ChannelSalesPerformance::where('tenant_id', $tenantId)
            ->when($request->channel_id, fn ($q) => $q->where('channel_id', $request->channel_id))
            ->when($request->date_from, fn ($q) => $q->where('sale_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->where('sale_date', '<=', $request->date_to))
            ->with('channel')
            ->latest('sale_date')
            ->paginate(20);

        return view('cosmetic.distribution.performance', compact('stats', 'channels', 'performance'));
    }

    public function recordSale(Request $request)
    {
        $validated = $request->validate([
            'channel_id' => 'required|exists:distribution_channels,id',
            'sales_amount' => 'required|numeric|min:0',
            'units' => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $channel = DistributionChannel::where('tenant_id', $tenantId)->findOrFail($validated['channel_id']);

        $commission = $channel->calculateCommission($validated['sales_amount']);

        ChannelSalesPerformance::recordSale(
            $tenantId,
            $validated['channel_id'],
            $validated['sales_amount'],
            $validated['units'],
            $commission
        );

        return back()->with('success', 'Sale recorded successfully!');
    }
}
