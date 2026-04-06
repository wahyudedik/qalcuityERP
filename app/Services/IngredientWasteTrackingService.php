<?php

namespace App\Services;

use App\Models\IngredientWaste;
use App\Models\InventoryItem;
use Carbon\Carbon;

class IngredientWasteTrackingService
{
    /**
     * Record ingredient waste
     */
    public function recordWaste(array $data): IngredientWaste
    {
        $quantity = $data['quantity_wasted'];
        $costPerUnit = $data['cost_per_unit'];

        $waste = IngredientWaste::create([
            'tenant_id' => $data['tenant_id'],
            'inventory_item_id' => $data['inventory_item_id'] ?? null,
            'item_name' => $data['item_name'],
            'quantity_wasted' => $quantity,
            'unit' => $data['unit'],
            'cost_per_unit' => $costPerUnit,
            'total_waste_cost' => IngredientWaste::calculateWasteCost($quantity, $costPerUnit),
            'waste_type' => $data['waste_type'],
            'reason' => $data['reason'] ?? null,
            'wasted_by' => $data['wasted_by'] ?? auth()->id(),
            'wasted_at' => $data['wasted_at'] ?? now(),
            'department' => $data['department'] ?? 'kitchen',
            'preventive_action' => $data['preventive_action'] ?? null,
        ]);

        // Update inventory if item is tracked
        if ($data['inventory_item_id']) {
            $this->updateInventoryStock($data['inventory_item_id'], $quantity);
        }

        return $waste;
    }

    /**
     * Get waste statistics
     */
    public function getWasteStats(int $tenantId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = IngredientWaste::where('tenant_id', $tenantId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->whereMonth('wasted_at', now()->month)
                ->whereYear('wasted_at', now()->year);
        }

        $wastes = $query->get();

        return [
            'total_waste_cost' => $wastes->sum('total_waste_cost'),
            'total_items_wasted' => $wastes->count(),
            'by_type' => $wastes->groupBy('waste_type')->map(fn($group) => [
                'count' => $group->count(),
                'total_cost' => $group->sum('total_waste_cost'),
            ]),
            'by_department' => $wastes->groupBy('department')->map(fn($group) => [
                'count' => $group->count(),
                'total_cost' => $group->sum('total_waste_cost'),
            ]),
            'top_wasted_items' => $this->getTopWastedItems($wastes),
            'daily_average' => $wastes->count() > 0
                ? $wastes->sum('total_waste_cost') / max(1, $wastes->unique('wasted_at')->count())
                : 0,
        ];
    }

    /**
     * Get waste trends over time
     */
    public function getWasteTrends(int $tenantId, int $daysBack = 30): array
    {
        $startDate = now()->subDays($daysBack);

        $dailyWaste = IngredientWaste::where('tenant_id', $tenantId)
            ->where('wasted_at', '>=', $startDate)
            ->selectRaw('DATE(wasted_at) as date, SUM(total_waste_cost) as total_cost, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'daily_trends' => $dailyWaste,
            'total_cost' => $dailyWaste->sum('total_cost'),
            'average_daily_cost' => $dailyWaste->avg('total_cost') ?? 0,
            'trend_direction' => $this->calculateTrendDirection($dailyWaste),
        ];
    }

    /**
     * Get waste by item
     */
    public function getWasteByItem(int $tenantId, int $daysBack = 30): array
    {
        $startDate = now()->subDays($daysBack);

        $wasteByItem = IngredientWaste::where('tenant_id', $tenantId)
            ->where('wasted_at', '>=', $startDate)
            ->selectRaw('item_name, SUM(quantity_wasted) as total_quantity, unit, SUM(total_waste_cost) as total_cost, COUNT(*) as occurrences')
            ->groupBy('item_name', 'unit')
            ->orderByDesc('total_cost')
            ->limit(20)
            ->get();

        return $wasteByItem->toArray();
    }

    /**
     * Get most common waste reasons
     */
    public function getCommonWasteReasons(int $tenantId, int $daysBack = 30): array
    {
        $startDate = now()->subDays($daysBack);

        return IngredientWaste::where('tenant_id', $tenantId)
            ->where('wasted_at', '>=', $startDate)
            ->whereNotNull('reason')
            ->selectRaw('reason, COUNT(*) as count, SUM(total_waste_cost) as total_cost')
            ->groupBy('reason')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Generate waste reduction recommendations
     */
    public function generateRecommendations(int $tenantId): array
    {
        $stats = $this->getWasteStats($tenantId);
        $recommendations = [];

        // Check for high spoilage
        if (isset($stats['by_type']['spoilage'])) {
            $spoilage = $stats['by_type']['spoilage'];
            if ($spoilage['total_cost'] > 1000000) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'Spoilage',
                    'message' => 'High spoilage costs detected. Review storage conditions and FIFO practices.',
                    'potential_savings' => $spoilage['total_cost'] * 0.5,
                ];
            }
        }

        // Check for preparation errors
        if (isset($stats['by_type']['preparation_error'])) {
            $errors = $stats['by_type']['preparation_error'];
            if ($errors['count'] > 10) {
                $recommendations[] = [
                    'priority' => 'medium',
                    'category' => 'Training',
                    'message' => 'Frequent preparation errors. Consider staff training on standard recipes.',
                    'potential_savings' => $errors['total_cost'] * 0.7,
                ];
            }
        }

        // Check for expired items
        if (isset($stats['by_type']['expired'])) {
            $expired = $stats['by_type']['expired'];
            if ($expired['total_cost'] > 500000) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'Inventory Management',
                    'message' => 'Significant expired inventory. Implement better stock rotation and ordering.',
                    'potential_savings' => $expired['total_cost'] * 0.8,
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Export waste report
     */
    public function exportWasteReport(int $tenantId, Carbon $startDate, Carbon $endDate): array
    {
        $wastes = IngredientWaste::where('tenant_id', $tenantId)
            ->dateRange($startDate, $endDate)
            ->with(['inventoryItem', 'wastedBy'])
            ->orderBy('wasted_at', 'desc')
            ->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => $this->getWasteStats($tenantId, $startDate, $endDate),
            'details' => $wastes->map(fn($waste) => [
                'date' => $waste->wasted_at->format('Y-m-d H:i'),
                'item' => $waste->item_name,
                'quantity' => $waste->quantity_wasted,
                'unit' => $waste->unit,
                'cost' => $waste->total_waste_cost,
                'type' => $waste->getWasteTypeLabel(),
                'reason' => $waste->reason,
                'department' => $waste->department,
                'recorded_by' => $waste->wastedBy?->name ?? 'Unknown',
            ]),
        ];
    }

    /**
     * Update inventory stock after waste
     */
    private function updateInventoryStock(int $itemId, float $quantity): void
    {
        $item = InventoryItem::find($itemId);
        if ($item) {
            $newStock = max(0, $item->current_stock - $quantity);
            $item->update(['current_stock' => $newStock]);
        }
    }

    /**
     * Get top wasted items
     */
    private function getTopWastedItems($wastes): array
    {
        return $wastes->groupBy('item_name')
            ->map(fn($group) => [
                'item_name' => $group->first()->item_name,
                'total_quantity' => $group->sum('quantity_wasted'),
                'unit' => $group->first()->unit,
                'total_cost' => $group->sum('total_waste_cost'),
                'occurrences' => $group->count(),
            ])
            ->sortByDesc('total_cost')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Calculate trend direction
     */
    private function calculateTrendDirection($dailyWaste): string
    {
        if ($dailyWaste->count() < 2) {
            return 'stable';
        }

        $firstHalf = $dailyWaste->take(floor($dailyWaste->count() / 2))->avg('total_cost');
        $secondHalf = $dailyWaste->skip(floor($dailyWaste->count() / 2))->avg('total_cost');

        if ($secondHalf > $firstHalf * 1.1) {
            return 'increasing';
        } elseif ($secondHalf < $firstHalf * 0.9) {
            return 'decreasing';
        }

        return 'stable';
    }
}
