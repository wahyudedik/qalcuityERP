<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\CosmeticFormula;
use App\Models\ProductVariant;
use App\Models\ProductRecall;
use App\Models\DistributionChannel;
use App\Models\ChannelSale;
use App\Services\VariantService;
use App\Services\PackagingComplianceService;
use App\Services\RecallManagementService;
use Illuminate\Http\Request;

/**
 * Cosmetic Module Controller
 * 
 * Handles variant matrix, packaging compliance, recall management,
 * and distribution channel analytics for cosmetic products.
 * 
 * @note Linter may show false positives for auth()->user() - this is standard Laravel
 */
class CosmeticModuleController extends Controller
{
    protected $variantService;
    protected $packagingService;
    protected $recallService;

    public function __construct(
        VariantService $variantService,
        PackagingComplianceService $packagingService,
        RecallManagementService $recallService
    ) {
        $this->variantService = $variantService;
        $this->packagingService = $packagingService;
        $this->recallService = $recallService;
    }

    // ==================== TASK-2.39: VARIANT MATRIX ====================

    /**
     * Show variant matrix builder
     */
    public function variantMatrix($formulaId)
    {
        $formula = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->with(['ingredients'])
            ->findOrFail($formulaId);

        $matrix = $this->variantService->getVariantMatrix(
            auth()->user()->tenant_id,
            $formulaId
        );

        return view('cosmetic.variants.matrix', compact('formula', 'matrix'));
    }

    /**
     * Store variant matrix
     */
    public function storeVariantMatrix(Request $request, $formulaId)
    {
        $validated = $request->validate([
            'variants' => 'required|array|min:1',
            'variants.*.variant_name' => 'required|string|max:255',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.size' => 'nullable|numeric',
            'variants.*.unit' => 'nullable|string|max:20',
            'variants.*.price_adjustment' => 'nullable|numeric',
            'variants.*.cost_adjustment' => 'nullable|numeric',
            'variants.*.barcode' => 'nullable|string|max:255',
        ]);

        try {
            $variants = $this->variantService->createVariantMatrix(
                auth()->user()->tenant_id,
                $formulaId,
                $validated['variants']
            );

            return back()->with('success', count($variants) . ' variant(s) created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle variant status
     */
    public function toggleVariant($id)
    {
        try {
            $variant = ProductVariant::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->variantService->toggleVariant($variant);

            return back()->with('success', 'Variant status updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete variant
     */
    public function deleteVariant($id)
    {
        try {
            $variant = ProductVariant::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->variantService->deleteVariant($variant);

            return back()->with('success', 'Variant deleted!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== TASK-2.40: PACKAGING COMPLIANCE ====================

    /**
     * Show packaging compliance checker
     */
    public function packagingCompliance()
    {
        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['approved', 'production'])
            ->orderBy('formula_name')
            ->get();

        return view('cosmetic.packaging.compliance', compact('formulas'));
    }

    /**
     * Validate label compliance
     */
    public function validateLabel(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'nullable|string',
            'bpom_number' => 'nullable|string',
            'net_content' => 'nullable|string',
            'ingredients_list' => 'nullable|string',
            'manufacturer_name' => 'nullable|string',
            'manufacturer_address' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'batch_code' => 'nullable|string',
            'warnings' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'barcode' => 'nullable|string',
        ]);

        $result = $this->packagingService->validateLabelCompliance(
            $validated,
            auth()->user()->tenant_id
        );

        return response()->json($result);
    }

    /**
     * Get packaging requirements by category
     */
    public function getPackagingRequirements(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
        ]);

        $requirements = $this->packagingService->getPackagingRequirements(
            $validated['category']
        );

        return response()->json($requirements);
    }

    /**
     * Validate batch number format
     */
    public function validateBatchNumber(Request $request)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string',
        ]);

        $result = $this->packagingService->validateBatchNumber(
            $validated['batch_number']
        );

        return response()->json($result);
    }

    // ==================== TASK-2.41: RECALL MANAGEMENT ====================

    /**
     * Show recall management dashboard
     */
    public function recallDashboard()
    {
        $stats = $this->recallService->getRecallStats(auth()->user()->tenant_id);
        $activeRecalls = $this->recallService->getActiveRecalls(auth()->user()->tenant_id);
        $expiryInfo = $this->recallService->checkExpiringBatches(auth()->user()->tenant_id, 90);

        return view('cosmetic.recall.dashboard', compact('stats', 'activeRecalls', 'expiryInfo'));
    }

    /**
     * Create recall
     */
    public function createRecall()
    {
        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('formula_name')
            ->get();

        return view('cosmetic.recall.create', compact('formulas'));
    }

    /**
     * Store recall
     */
    public function storeRecall(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:cosmetic_formulas,id',
            'batch_ids' => 'nullable|array',
            'recall_type' => 'required|in:voluntary,mandatory',
            'severity' => 'required|in:critical,major,minor',
            'reason' => 'required|string',
            'description' => 'nullable|string',
            'affected_units' => 'nullable|integer',
            'action_required' => 'required|string',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            $recall = $this->recallService->createRecall(
                auth()->user()->tenant_id,
                $validated
            );

            return redirect()->route('cosmetic.recall.show', $recall)
                ->with('success', 'Product recall initiated!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show recall details
     */
    public function showRecall($id)
    {
        $recall = ProductRecall::with(['product', 'initiator'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return view('cosmetic.recall.show', compact('recall'));
    }

    /**
     * Update recall status
     */
    public function updateRecallStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:initiated,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        try {
            $recall = ProductRecall::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->recallService->updateRecallStatus(
                $recall,
                $validated['status'],
                $validated['notes'] ?? ''
            );

            return back()->with('success', 'Recall status updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Auto-expire batches
     */
    public function autoExpireBatches()
    {
        try {
            $count = $this->recallService->autoExpireBatches(auth()->user()->tenant_id);

            return back()->with('success', "{$count} batch(es) expired and updated.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== TASK-2.42: DISTRIBUTION CHANNEL ANALYTICS ====================

    /**
     * Show distribution channels dashboard
     */
    public function distributionDashboard(Request $request)
    {
        $channels = DistributionChannel::where('tenant_id', auth()->user()->tenant_id)
            ->withCount(['sales'])
            ->orderBy('priority')
            ->get();

        $channelStats = [];
        foreach ($channels as $channel) {
            $channelStats[] = [
                'id' => $channel->id,
                'name' => $channel->channel_name,
                'type' => $channel->channel_type,
                'total_sales' => $channel->total_sales,
                'total_quantity' => $channel->total_quantity_sold,
                'transaction_count' => $channel->sales_count,
                'avg_order_value' => $channel->sales_count > 0 ? $channel->total_sales / $channel->sales_count : 0,
            ];
        }

        // Get sales trend
        $salesTrend = ChannelSale::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'completed')
            ->selectRaw('DATE(sale_date) as date, SUM(total_amount) as total, SUM(quantity_sold) as quantity')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return view('cosmetic.distribution.dashboard', compact('channels', 'channelStats', 'salesTrend'));
    }

    /**
     * Show channel details
     */
    public function showChannel($id)
    {
        $channel = DistributionChannel::with(['sales.formula', 'sales.variant'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $sales = $channel->sales()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('sale_date')
            ->paginate(20);

        return view('cosmetic.distribution.channel-show', compact('channel', 'sales'));
    }

    /**
     * Create distribution channel
     */
    public function createChannel()
    {
        return view('cosmetic.distribution.channel-create');
    }

    /**
     * Store distribution channel
     */
    public function storeChannel(Request $request)
    {
        $validated = $request->validate([
            'channel_name' => 'required|string|max:255',
            'channel_type' => 'required|in:direct,wholesale,marketplace',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'region' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'priority' => 'nullable|integer',
        ]);

        try {
            $channel = new DistributionChannel();
            $channel->tenant_id = auth()->user()->tenant_id;
            $channel->channel_name = $validated['channel_name'];
            $channel->channel_type = $validated['channel_type'];
            $channel->contact_person = $validated['contact_person'] ?? null;
            $channel->contact_email = $validated['contact_email'] ?? null;
            $channel->contact_phone = $validated['contact_phone'] ?? null;
            $channel->address = $validated['address'] ?? null;
            $channel->region = $validated['region'] ?? null;
            $channel->commission_rate = $validated['commission_rate'] ?? null;
            $channel->priority = $validated['priority'] ?? 0;
            $channel->status = 'active';
            $channel->save();

            return redirect()->route('cosmetic.distribution.dashboard')
                ->with('success', 'Distribution channel created!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Record channel sale
     */
    public function recordSale(Request $request)
    {
        $validated = $request->validate([
            'channel_id' => 'required|exists:distribution_channels,id',
            'formula_id' => 'nullable|exists:cosmetic_formulas,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'batch_id' => 'nullable|exists:cosmetic_batch_records,id',
            'sale_date' => 'required|date',
            'quantity_sold' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,completed,returned',
            'notes' => 'nullable|string',
        ]);

        try {
            $channel = DistributionChannel::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($validated['channel_id']);

            $sale = new ChannelSale();
            $sale->tenant_id = auth()->user()->tenant_id;
            $sale->channel_id = $validated['channel_id'];
            $sale->formula_id = $validated['formula_id'] ?? null;
            $sale->variant_id = $validated['variant_id'] ?? null;
            $sale->batch_id = $validated['batch_id'] ?? null;
            $sale->sale_date = $validated['sale_date'];
            $sale->quantity_sold = $validated['quantity_sold'];
            $sale->unit_price = $validated['unit_price'];
            $sale->total_amount = $validated['total_amount'];
            $sale->commission_amount = $channel->commission_rate
                ? $validated['total_amount'] * ($channel->commission_rate / 100)
                : null;
            $sale->status = $validated['status'] ?? 'completed';
            $sale->notes = $validated['notes'] ?? null;
            $sale->save();

            return back()->with('success', 'Sale recorded successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle channel status
     */
    public function toggleChannel($id)
    {
        try {
            $channel = DistributionChannel::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $channel->status = $channel->status === 'active' ? 'inactive' : 'active';
            $channel->save();

            return back()->with('success', 'Channel status updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
