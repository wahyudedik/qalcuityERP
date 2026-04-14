<?php

namespace App\Services\Manufacturing;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\WorkOrder;
use App\Services\MrpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MRP Planning Service
 * 
 * Enhances basic MRP calculation with:
 * - Lead time calculations
 * - Purchase recommendations
 * - Production scheduling
 * - Priority scoring
 * - Time-phased planning
 */
class MrpPlanningService
{
    protected MrpService $mrpService;

    public function __construct(MrpService $mrpService)
    {
        $this->mrpService = $mrpService;
    }

    /**
     * Generate comprehensive MRP planning with recommendations
     */
    public function generatePlanningReport(int $tenantId, ?int $bomId = null, float $quantity = 1): array
    {
        $mrpResults = $bomId
            ? $this->runSingleBomMrp($tenantId, $bomId, $quantity)
            : $this->runFullMrp($tenantId);

        if (empty($mrpResults)) {
            return ['status' => 'no_requirements', 'message' => 'Tidak ada kebutuhan material'];
        }

        // Add lead time and recommendations
        $planningItems = [];
        foreach ($mrpResults as $item) {
            $planningItems[] = $this->addItemPlanning($item, $tenantId);
        }

        // Sort by priority (shortages first, then by lead time)
        usort($planningItems, function ($a, $b) {
            if ($a['has_shortage'] && !$b['has_shortage'])
                return -1;
            if (!$a['has_shortage'] && $b['has_shortage'])
                return 1;
            return $a['lead_time_days'] <=> $b['lead_time_days'];
        });

        $summary = $this->generateSummary($planningItems);

        return [
            'status' => 'success',
            'items' => $planningItems,
            'summary' => $summary,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Add planning details to a single MRP item
     */
    protected function addItemPlanning(array $item, int $tenantId): array
    {
        $product = Product::find($item['product_id']);

        // Get supplier lead time (from last PO)
        $leadTime = $this->calculateLeadTime($item['product_id'], $tenantId);

        // Calculate order date needed
        $orderDate = now()->addDays($leadTime);

        // Determine action needed
        $action = $this->determineAction($item, $leadTime);

        return array_merge($item, [
            'lead_time_days' => $leadTime,
            'order_by_date' => $orderDate->format('Y-m-d'),
            'order_by_date_formatted' => $orderDate->format('d M Y'),
            'has_shortage' => $item['shortage'] > 0,
            'action' => $action,
            'priority' => $this->calculatePriority($item, $leadTime),
            'supplier_info' => $this->getSupplierInfo($item['product_id'], $tenantId),
        ]);
    }

    /**
     * Calculate average lead time from purchase history
     */
    protected function calculateLeadTime(int $productId, int $tenantId): int
    {
        // Get last 5 POs for this product
        $poHistory = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.tenant_id', $tenantId)
            ->where('purchase_order_items.product_id', $productId)
            ->where('purchase_orders.status', 'received')
            ->whereNotNull('purchase_orders.received_at')
            ->whereNotNull('purchase_orders.order_date')
            ->orderByDesc('purchase_orders.received_at')
            ->limit(5)
            ->get();

        if ($poHistory->isEmpty()) {
            return 7; // Default 7 days
        }

        $totalDays = 0;
        $count = 0;

        foreach ($poHistory as $po) {
            $orderDate = is_string($po->order_date) ? strtotime($po->order_date) : $po->order_date;
            $receivedDate = is_string($po->received_at) ? strtotime($po->received_at) : $po->received_at;

            $days = ceil(($receivedDate - $orderDate) / 86400);
            if ($days > 0) {
                $totalDays += $days;
                $count++;
            }
        }

        return $count > 0 ? max(1, ceil($totalDays / $count)) : 7;
    }

    /**
     * Determine recommended action
     */
    protected function determineAction(array $item, int $leadTime): array
    {
        if ($item['shortage'] <= 0) {
            return [
                'type' => 'no_action',
                'message' => 'Stok mencukupi',
                'urgency' => 'low',
            ];
        }

        $urgency = $this->calculateUrgency($item['shortage'], $leadTime);

        return [
            'type' => 'purchase_recommended',
            'message' => "Segera order {$item['product_name']} sebanyak " . round($item['shortage'], 2) . " {$item['unit']}",
            'urgency' => $urgency,
            'recommended_qty' => ceil($item['shortage']),
            'order_by' => now()->addDays($leadTime)->format('d M Y'),
        ];
    }

    /**
     * Calculate urgency level
     */
    protected function calculateUrgency(float $shortage, int $leadTime): string
    {
        if ($leadTime <= 2 && $shortage > 0) {
            return 'critical';
        }

        if ($leadTime <= 5) {
            return 'high';
        }

        if ($shortage > 100) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Calculate priority score (lower = higher priority)
     */
    protected function calculatePriority(array $item, int $leadTime): int
    {
        $score = 0;

        // Shortage weight (0-50 points)
        if ($item['shortage'] > 0) {
            $score += 50;
        }

        // Lead time weight (0-30 points)
        $score += max(0, 30 - ($leadTime * 3));

        // Quantity weight (0-20 points)
        if (isset($item['required'])) {
            $score += min(20, $item['required'] / 10);
        }

        return (int) $score;
    }

    /**
     * Get supplier information for product
     */
    protected function getSupplierInfo(int $productId, int $tenantId): ?array
    {
        // Get last supplier from PO history
        $lastSupplier = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->where('purchase_orders.tenant_id', $tenantId)
            ->where('purchase_order_items.product_id', $productId)
            ->where('purchase_orders.status', 'received')
            ->orderByDesc('purchase_orders.created_at')
            ->select('suppliers.name as supplier_name', 'suppliers.phone', 'suppliers.email')
            ->first();

        if (!$lastSupplier) {
            return null;
        }

        return [
            'name' => $lastSupplier->supplier_name,
            'phone' => $lastSupplier->phone,
            'email' => $lastSupplier->email,
        ];
    }

    /**
     * Generate summary statistics
     */
    protected function generateSummary(array $items): array
    {
        $totalItems = count($items);
        $shortages = array_filter($items, fn($item) => $item['has_shortage']);
        $criticalItems = array_filter($items, fn($item) => ($item['action']['urgency'] ?? 'low') === 'critical');
        $highItems = array_filter($items, fn($item) => ($item['action']['urgency'] ?? 'low') === 'high');

        $totalShortageValue = 0;
        foreach ($shortages as $item) {
            $product = Product::find($item['product_id']);
            $totalShortageValue += $item['shortage'] * ($product->price_buy ?? 0);
        }

        return [
            'total_items' => $totalItems,
            'items_with_shortage' => count($shortages),
            'items_sufficient' => $totalItems - count($shortages),
            'critical_items' => count($criticalItems),
            'high_priority_items' => count($highItems),
            'estimated_shortage_value' => round($totalShortageValue, 0),
            'health_percentage' => $totalItems > 0
                ? round((($totalItems - count($shortages)) / $totalItems) * 100, 1)
                : 100,
        ];
    }

    /**
     * Run MRP for single BOM
     */
    protected function runSingleBomMrp(int $tenantId, int $bomId, float $quantity): array
    {
        $bom = \App\Models\Bom::with('lines.product')->find($bomId);

        if (!$bom || $bom->tenant_id !== $tenantId) {
            return [];
        }

        return $this->mrpService->calculate($bom, $quantity, $tenantId);
    }

    /**
     * Run full MRP for all pending WOs
     */
    protected function runFullMrp(int $tenantId): array
    {
        return $this->mrpService->runFullMrp($tenantId);
    }

    /**
     * Create auto purchase orders for shortages
     */
    public function createAutoPurchaseOrders(int $tenantId, array $options = []): array
    {
        $planning = $this->generatePlanningReport($tenantId);

        if ($planning['status'] !== 'success') {
            return ['success' => false, 'message' => 'Tidak ada kebutuhan material'];
        }

        $shortages = array_filter($planning['items'], fn($item) => $item['has_shortage']);
        $created = [];
        $errors = [];

        foreach ($shortages as $item) {
            try {
                // Group by supplier if needed
                // For now, create individual POs
                $po = PurchaseOrder::create([
                    'tenant_id' => $tenantId,
                    'supplier_id' => null, // TODO: Auto-assign supplier
                    'user_id' => Auth::id(),
                    'order_date' => now(),
                    'expected_date' => now()->addDays($item['lead_time_days']),
                    'status' => 'draft',
                    'notes' => "Auto-generated dari MRP Planning - {$item['product_name']}",
                ]);

                $created[] = [
                    'po_id' => $po->id,
                    'product' => $item['product_name'],
                    'quantity' => $item['shortage'],
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'product' => $item['product_name'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => count($created) > 0,
            'created' => $created,
            'errors' => $errors,
            'total_created' => count($created),
        ];
    }

    /**
     * Get MRP dashboard data
     */
    public function getDashboardData(int $tenantId): array
    {
        $planning = $this->generatePlanningReport($tenantId);

        return [
            'planning' => $planning,
            'pending_work_orders' => WorkOrder::where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'pending_purchase_orders' => PurchaseOrder::where('tenant_id', $tenantId)
                ->whereIn('status', ['confirmed', 'partial'])
                ->count(),
            'low_stock_items' => Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereHas('productStocks', function ($query) {
                    $query->whereRaw('quantity < (SELECT stock_min FROM products WHERE id = product_stocks.product_id)');
                })
                ->count(),
        ];
    }
}
