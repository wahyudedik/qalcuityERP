<?php

namespace App\Services;

use App\Models\WorkOrder;

/**
 * BUG-MFG-003 FIX: Production Costing Service with Overhead Calculation
 *
 * Provides accurate production cost calculation including:
 * - Material costs (from BOM consumption)
 * - Labor costs (manual or calculated)
 * - Overhead costs (4 methods: manual, work_center, % of labor, % of material)
 * - Operation-based costing from work centers
 */
class ProductionCostingService
{
    /**
     * Calculate complete production cost for a work order
     */
    public function calculateProductionCost(WorkOrder $workOrder): array
    {
        // 1. Material cost (from consumed materials)
        $materialCost = (float) $workOrder->material_cost;

        // 2. Labor cost (manual input or calculated)
        $laborCost = (float) $workOrder->labor_cost;

        // 3. Calculate overhead based on method
        $overheadResult = $this->calculateOverhead($workOrder);

        // 4. Calculate operation costs from work centers
        $operationCost = $this->calculateOperationCost($workOrder);

        // 5. Total cost
        $totalCost = $materialCost + $laborCost + $overheadResult['overhead'] + $operationCost;

        // 6. Cost per unit
        $totalGoodQty = (float) $workOrder->totalGoodQty();
        $costPerUnit = $totalGoodQty > 0 ? $totalCost / $totalGoodQty : 0;

        return [
            'work_order_id' => $workOrder->id,
            'work_order_number' => $workOrder->number,
            'material_cost' => round($materialCost, 2),
            'labor_cost' => round($laborCost, 2),
            'overhead_cost' => round($overheadResult['overhead'], 2),
            'overhead_method' => $overheadResult['method'],
            'overhead_breakdown' => $overheadResult['breakdown'],
            'operation_cost' => round($operationCost, 2),
            'total_cost' => round($totalCost, 2),
            'total_good_qty' => $totalGoodQty,
            'cost_per_unit' => round($costPerUnit, 2),
            'cost_components' => [
                'material_percentage' => $totalCost > 0 ? round(($materialCost / $totalCost) * 100, 1) : 0,
                'labor_percentage' => $totalCost > 0 ? round(($laborCost / $totalCost) * 100, 1) : 0,
                'overhead_percentage' => $totalCost > 0 ? round(($overheadResult['overhead'] / $totalCost) * 100, 1) : 0,
                'operation_percentage' => $totalCost > 0 ? round(($operationCost / $totalCost) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Calculate overhead cost based on configured method
     */
    public function calculateOverhead(WorkOrder $workOrder): array
    {
        $method = $workOrder->overhead_method ?? 'manual';
        $overhead = 0;
        $breakdown = [];

        switch ($method) {
            case 'work_center':
                // Calculate from work center operations
                $result = $this->calculateWorkCenterOverhead($workOrder);
                $overhead = $result['total'];
                $breakdown = $result['breakdown'];
                break;

            case 'percentage_of_labor':
                // Percentage of labor cost
                $laborCost = (float) $workOrder->labor_cost;
                $rate = (float) ($workOrder->overhead_rate ?? 0);
                $overhead = $laborCost * ($rate / 100);
                $breakdown = [
                    'labor_cost' => $laborCost,
                    'overhead_rate_percentage' => $rate,
                    'calculation' => "{$laborCost} × {$rate}% = {$overhead}",
                ];
                break;

            case 'percentage_of_material':
                // Percentage of material cost
                $materialCost = (float) $workOrder->material_cost;
                $rate = (float) ($workOrder->overhead_rate ?? 0);
                $overhead = $materialCost * ($rate / 100);
                $breakdown = [
                    'material_cost' => $materialCost,
                    'overhead_rate_percentage' => $rate,
                    'calculation' => "{$materialCost} × {$rate}% = {$overhead}",
                ];
                break;

            case 'manual':
            default:
                // Use manual input (backward compatibility)
                $overhead = (float) ($workOrder->overhead_cost ?? 0);
                $breakdown = [
                    'manual_input' => $overhead,
                    'note' => 'Manual overhead entry',
                ];
                break;
        }

        return [
            'overhead' => round($overhead, 2),
            'method' => $method,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate overhead from work center operations
     */
    protected function calculateWorkCenterOverhead(WorkOrder $workOrder): array
    {
        $operations = $workOrder->operations()
            ->with('workCenter')
            ->where('status', 'completed')
            ->get();

        $totalOverhead = 0;
        $breakdown = [];
        $totalHours = 0;

        foreach ($operations as $operation) {
            if (! $operation->workCenter) {
                continue;
            }

            $actualHours = (float) ($operation->actual_hours ?? $operation->estimated_hours ?? 0);
            $overheadRate = (float) ($operation->workCenter->overhead_rate_per_hour ?? 0);
            $operationOverhead = $actualHours * $overheadRate;

            $totalOverhead += $operationOverhead;
            $totalHours += $actualHours;

            $breakdown[] = [
                'operation' => $operation->name,
                'work_center' => $operation->workCenter->name,
                'actual_hours' => $actualHours,
                'overhead_rate_per_hour' => $overheadRate,
                'overhead_cost' => round($operationOverhead, 2),
            ];
        }

        // Update work order with calculated values
        $workOrder->update([
            'calculated_overhead' => $totalOverhead,
            'total_operation_hours' => $totalHours,
        ]);

        return [
            'total' => $totalOverhead,
            'total_hours' => $totalHours,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate operation costs (labor + overhead from work centers)
     */
    public function calculateOperationCost(WorkOrder $workOrder): float
    {
        $operations = $workOrder->operations()
            ->with('workCenter')
            ->where('status', 'completed')
            ->get();

        $totalOperationCost = 0;

        foreach ($operations as $operation) {
            if (! $operation->workCenter) {
                continue;
            }

            $actualHours = (float) ($operation->actual_hours ?? $operation->estimated_hours ?? 0);
            $costPerHour = (float) ($operation->workCenter->cost_per_hour ?? 0);
            $operationCost = $actualHours * $costPerHour;

            $totalOperationCost += $operationCost;
        }

        return $totalOperationCost;
    }

    /**
     * Auto-calculate and update work order costs
     */
    public function autoCalculateCosts(WorkOrder $workOrder): array
    {
        $costData = $this->calculateProductionCost($workOrder);

        // Update work order with calculated costs
        $workOrder->update([
            'overhead_cost' => $costData['overhead_cost'],
            'total_cost' => $costData['total_cost'],
        ]);

        return $costData;
    }

    /**
     * Get cost analysis dashboard data
     */
    public function getCostAnalysis(int $tenantId, int $months = 3): array
    {
        $startDate = now()->subMonths($months);

        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->get();

        if ($workOrders->isEmpty()) {
            return ['message' => 'No completed work orders found'];
        }

        $totalMaterialCost = 0;
        $totalLaborCost = 0;
        $totalOverheadCost = 0;
        $totalOperationCost = 0;
        $totalProductionCost = 0;
        $totalGoodQty = 0;

        $costsByProduct = [];
        $costsByDate = [];

        foreach ($workOrders as $wo) {
            $costData = $this->calculateProductionCost($wo);

            $totalMaterialCost += $costData['material_cost'];
            $totalLaborCost += $costData['labor_cost'];
            $totalOverheadCost += $costData['overhead_cost'];
            $totalOperationCost += $costData['operation_cost'];
            $totalProductionCost += $costData['total_cost'];
            $totalGoodQty += $costData['total_good_qty'];

            // By product
            $productName = $wo->product->name ?? 'Unknown';
            if (! isset($costsByProduct[$productName])) {
                $costsByProduct[$productName] = [
                    'product_name' => $productName,
                    'total_cost' => 0,
                    'total_qty' => 0,
                    'work_orders' => 0,
                ];
            }
            $costsByProduct[$productName]['total_cost'] += $costData['total_cost'];
            $costsByProduct[$productName]['total_qty'] += $costData['total_good_qty'];
            $costsByProduct[$productName]['work_orders']++;

            // By date
            $dateKey = $wo->completed_at->format('Y-m-d');
            if (! isset($costsByDate[$dateKey])) {
                $costsByDate[$dateKey] = [
                    'date' => $dateKey,
                    'total_cost' => 0,
                    'work_orders' => 0,
                ];
            }
            $costsByDate[$dateKey]['total_cost'] += $costData['total_cost'];
            $costsByDate[$dateKey]['work_orders']++;
        }

        $avgCostPerUnit = $totalGoodQty > 0 ? $totalProductionCost / $totalGoodQty : 0;

        return [
            'summary' => [
                'total_work_orders' => $workOrders->count(),
                'total_good_qty' => round($totalGoodQty, 2),
                'total_material_cost' => round($totalMaterialCost, 2),
                'total_labor_cost' => round($totalLaborCost, 2),
                'total_overhead_cost' => round($totalOverheadCost, 2),
                'total_operation_cost' => round($totalOperationCost, 2),
                'total_production_cost' => round($totalProductionCost, 2),
                'avg_cost_per_unit' => round($avgCostPerUnit, 2),
                'cost_breakdown' => [
                    'material_percentage' => $totalProductionCost > 0 ? round(($totalMaterialCost / $totalProductionCost) * 100, 1) : 0,
                    'labor_percentage' => $totalProductionCost > 0 ? round(($totalLaborCost / $totalProductionCost) * 100, 1) : 0,
                    'overhead_percentage' => $totalProductionCost > 0 ? round(($totalOverheadCost / $totalProductionCost) * 100, 1) : 0,
                    'operation_percentage' => $totalProductionCost > 0 ? round(($totalOperationCost / $totalProductionCost) * 100, 1) : 0,
                ],
            ],
            'by_product' => array_values($costsByProduct),
            'by_date' => array_values($costsByDate),
        ];
    }

    /**
     * Recalculate all completed work orders (for data migration or correction)
     */
    public function recalculateAllWorkOrders(int $tenantId): int
    {
        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->get();

        $count = 0;
        foreach ($workOrders as $wo) {
            $this->autoCalculateCosts($wo);
            $count++;
        }

        return $count;
    }
}
