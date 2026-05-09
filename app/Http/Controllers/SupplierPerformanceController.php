<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierPerformanceController extends Controller
{
    private function tid(): int
    {
        return Auth::user()->tenant_id ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Main Supplier Performance Dashboard
     */
    public function dashboard(Request $request)
    {
        $tenantId = $this->tid();
        $period = $request->input('period', '90');

        // Get all suppliers with performance data
        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($supplier) use ($tenantId, $period) {
                $performance = SupplierPerformance::getSupplierPerformance(
                    $tenantId,
                    $supplier->id,
                    "{$period} days"
                );
                $supplier->performance = $performance;

                return $supplier;
            });

        // Get rankings
        $rankings = SupplierPerformance::getSupplierRankings($tenantId, 10, "{$period} days");

        // Summary stats
        $totalSuppliers = $suppliers->count();
        $avgScore = $suppliers->avg('performance.avg_overall_score');
        $topGrade = $suppliers->where('performance.current_grade', '!=', 'N/A')
            ->sortByDesc('performance.avg_overall_score')
            ->first();

        return view('procurement.supplier-performance', compact(
            'suppliers',
            'rankings',
            'period',
            'totalSuppliers',
            'avgScore',
            'topGrade'
        ));
    }

    /**
     * Supplier Detail Performance Page
     */
    public function detail(Request $request, Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== $this->tid(), 403);

        $tenantId = $this->tid();
        $period = $request->input('period', '90');

        // Get detailed performance metrics
        $performance = SupplierPerformance::getSupplierPerformance(
            $tenantId,
            $supplier->id,
            "{$period} days"
        );

        // Get individual evaluations
        $evaluations = SupplierPerformance::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplier->id)
            ->with(['purchaseOrder', 'evaluatedBy'])
            ->orderByDesc('evaluation_date')
            ->paginate(20);

        // Get recent POs
        $recentPOs = PurchaseOrder::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('procurement.supplier-performance-detail', compact(
            'supplier',
            'performance',
            'evaluations',
            'recentPOs',
            'period'
        ));
    }

    /**
     * Evaluate Supplier Performance (manual entry)
     */
    public function storeEvaluation(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'evaluation_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'expected_lead_time_days' => 'nullable|numeric|min:0',
            'quantity_ordered' => 'required|numeric|min:0',
            'quantity_received' => 'required|numeric|min:0',
            'quantity_rejected' => 'nullable|numeric|min:0',
            'total_po_value' => 'required|numeric|min:0',
            'actual_po_value' => 'required|numeric|min:0',
            'defect_notes' => 'nullable|string',
            'delivery_notes' => 'nullable|string',
        ]);

        $evaluation = SupplierPerformance::create([
            'tenant_id' => $this->tid(),
            'supplier_id' => $data['supplier_id'],
            'purchase_order_id' => $data['purchase_order_id'] ?? null,
            'evaluation_date' => $data['evaluation_date'],
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'actual_delivery_date' => $data['actual_delivery_date'] ?? null,
            'expected_lead_time_days' => $data['expected_lead_time_days'] ?? null,
            'quantity_ordered' => $data['quantity_ordered'],
            'quantity_received' => $data['quantity_received'],
            'quantity_rejected' => $data['quantity_rejected'] ?? 0,
            'total_po_value' => $data['total_po_value'],
            'actual_po_value' => $data['actual_po_value'],
            'defect_notes' => $data['defect_notes'] ?? null,
            'delivery_notes' => $data['delivery_notes'] ?? null,
            'evaluated_by' => Auth::id(),
        ]);

        // Auto-calculate all metrics
        $evaluation->calculateMetrics();
        $evaluation->save();

        return back()->with('success', "Supplier evaluation created. Overall Score: {$evaluation->overall_score} (Grade: {$evaluation->rating_grade})");
    }

    /**
     * Auto-evaluate from completed Purchase Order
     */
    public function autoEvaluateFromPO(PurchaseOrder $po)
    {
        abort_if($po->tenant_id !== $this->tid(), 403);

        // Check if already evaluated
        $exists = SupplierPerformance::where('purchase_order_id', $po->id)->exists();
        if ($exists) {
            return back()->with('info', 'This PO has already been evaluated.');
        }

        // Check if PO is received
        if ($po->status !== 'received' && $po->status !== 'completed') {
            return back()->with('error', 'PO must be received/completed before evaluation.');
        }

        $evaluation = SupplierPerformance::create([
            'tenant_id' => $this->tid(),
            'supplier_id' => $po->supplier_id,
            'purchase_order_id' => $po->id,
            'evaluation_date' => $po->received_at ?? now(),
            'expected_delivery_date' => $po->expected_delivery_date ?? null,
            'actual_delivery_date' => $po->received_at ?? null,
            'expected_lead_time_days' => 7, // Default, can be from supplier contract
            'quantity_ordered' => $po->items->sum('quantity') ?? 0,
            'quantity_received' => $po->items->sum('received_quantity') ?? 0,
            'quantity_rejected' => 0, // TODO: Get from QC
            'total_po_value' => $po->total_amount ?? 0,
            'actual_po_value' => $po->total_amount ?? 0,
            'evaluated_by' => Auth::id(),
        ]);

        $evaluation->calculateMetrics();
        $evaluation->save();

        return back()->with('success', "Auto-evaluation created. Score: {$evaluation->overall_score} (Grade: {$evaluation->rating_grade})");
    }
}
