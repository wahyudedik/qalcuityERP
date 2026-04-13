<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * MRP Accuracy Tracking
 * 
 * Tracks planned vs actual material usage for continuous improvement
 * 
 * @property float|null $planned_quantity
 * @property float|null $actual_quantity
 * @property float|null $variance_quantity
 * @property float|null $variance_percent
 * @property float|null $planned_cost
 * @property float|null $actual_cost
 * @property float|null $cost_variance
 */
class MrpAccuracy extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'product_id',
        'planned_quantity',
        'actual_quantity',
        'variance_quantity',
        'variance_percent',
        'planned_cost',
        'actual_cost',
        'cost_variance',
        'tracking_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'decimal:3',
            'actual_quantity' => 'decimal:3',
            'variance_quantity' => 'decimal:3',
            'variance_percent' => 'decimal:2',
            'planned_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'cost_variance' => 'decimal:2',
            'tracking_date' => 'date',
        ];
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate and set variance
     */
    public function calculateVariance(): void
    {
        $this->variance_quantity = (float) $this->actual_quantity - (float) $this->planned_quantity;
        $this->variance_percent = (float) $this->planned_quantity > 0
            ? (float) (($this->variance_quantity / $this->planned_quantity) * 100)
            : 0.0;
        $this->cost_variance = (float) $this->actual_cost - (float) $this->planned_cost;
    }

    /**
     * Get accuracy metrics for tenant
     */
    public static function getAccuracyMetrics(int $tenantId, ?string $period = '30 days'): array
    {
        $query = self::where('tenant_id', $tenantId);

        if ($period) {
            $query->where('tracking_date', '>=', now()->subDays((int) $period));
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            return [
                'total_records' => 0,
                'avg_variance_percent' => 0,
                'accuracy_rate' => 100,
                'total_planned_cost' => 0,
                'total_actual_cost' => 0,
                'total_savings_loss' => 0,
            ];
        }

        $avgVariance = $records->avg('variance_percent');
        $accurateRecords = $records->where('variance_percent', '>=', -5)->where('variance_percent', '<=', 5)->count();

        return [
            'total_records' => $records->count(),
            'avg_variance_percent' => round($avgVariance, 2),
            'accuracy_rate' => round(($accurateRecords / $records->count()) * 100, 1),
            'total_planned_cost' => round($records->sum('planned_cost'), 2),
            'total_actual_cost' => round($records->sum('actual_cost'), 2),
            'total_savings_loss' => round($records->sum('cost_variance'), 2),
            'chart_data' => self::getChartData($records),
        ];
    }

    /**
     * Get chart data for visualization
     */
    private static function getChartData($records): array
    {
        $grouped = $records->groupBy(function ($record) {
            return $record->tracking_date->format('Y-m-d');
        });

        $labels = [];
        $plannedData = [];
        $actualData = [];
        $varianceData = [];

        foreach ($grouped->sortByKeys() as $date => $dayRecords) {
            $labels[] = $date;
            $plannedData[] = round($dayRecords->sum('planned_quantity'), 2);
            $actualData[] = round($dayRecords->sum('actual_quantity'), 2);
            $varianceData[] = round($dayRecords->avg('variance_percent'), 2);
        }

        return [
            'labels' => $labels,
            'planned' => $plannedData,
            'actual' => $actualData,
            'variance' => $varianceData,
        ];
    }

    /**
     * Get dashboard data with multiple period comparisons
     */
    public static function getDashboardData(int $tenantId): array
    {
        return [
            'last_7_days' => self::getAccuracyMetrics($tenantId, '7 days'),
            'last_30_days' => self::getAccuracyMetrics($tenantId, '30 days'),
            'last_90_days' => self::getAccuracyMetrics($tenantId, '90 days'),
            'all_time' => self::getAccuracyMetrics($tenantId, null),
        ];
    }
}
