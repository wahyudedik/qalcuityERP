<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Supplier Performance Tracking
 * 
 * Tracks supplier metrics: lead time, quality, delivery, cost
 * 
 * @property Carbon|null $expected_delivery_date
 * @property Carbon|null $actual_delivery_date
 */
class SupplierPerformance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'purchase_order_id',
        'evaluation_date',
        'period_start',
        'period_end',
        'expected_delivery_date',
        'actual_delivery_date',
        'lead_time_days',
        'expected_lead_time_days',
        'lead_time_variance_days',
        'on_time_delivery',
        'quantity_ordered',
        'quantity_received',
        'quantity_rejected',
        'quality_rate',
        'delivery_score',
        'quality_score',
        'cost_score',
        'responsiveness_score',
        'overall_score',
        'rating_grade',
        'total_po_value',
        'actual_po_value',
        'cost_variance',
        'defect_notes',
        'delivery_notes',
        'evaluated_by',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date' => 'date',
            'lead_time_days' => 'decimal:2',
            'expected_lead_time_days' => 'decimal:2',
            'lead_time_variance_days' => 'decimal:2',
            'on_time_delivery' => 'boolean',
            'quantity_ordered' => 'decimal:3',
            'quantity_received' => 'decimal:3',
            'quantity_rejected' => 'decimal:3',
            'quality_rate' => 'decimal:2',
            'delivery_score' => 'decimal:2',
            'quality_score' => 'decimal:2',
            'cost_score' => 'decimal:2',
            'responsiveness_score' => 'decimal:2',
            'overall_score' => 'decimal:2',
            'total_po_value' => 'decimal:2',
            'actual_po_value' => 'decimal:2',
            'cost_variance' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Calculate all performance metrics
     */
    public function calculateMetrics(): void
    {
        // Lead time calculation
        if ($this->expected_delivery_date && $this->actual_delivery_date) {
            $this->lead_time_days = (float) $this->expected_delivery_date->diffInDays($this->actual_delivery_date, false);
            $this->lead_time_variance_days = (float) $this->lead_time_days - (float) ($this->expected_lead_time_days ?? 0);
            $this->on_time_delivery = $this->lead_time_days <= 0; // On time or early
        }

        // Quality rate
        if ($this->quantity_received > 0) {
            $this->quality_rate = (float) ((($this->quantity_received - $this->quantity_rejected) / $this->quantity_received) * 100);
        }

        // Calculate individual scores (0-100)
        $this->delivery_score = (float) $this->calculateDeliveryScore();
        $this->quality_score = (float) $this->quality_rate;
        $this->cost_score = (float) $this->calculateCostScore();
        $this->responsiveness_score = (float) $this->calculateResponsivenessScore();

        // Overall weighted score
        $this->overall_score = (float) (
            ($this->delivery_score * 0.30) +      // 30% Delivery
            ($this->quality_score * 0.35) +       // 35% Quality
            ($this->cost_score * 0.20) +          // 20% Cost
            ($this->responsiveness_score * 0.15)  // 15% Responsiveness
        );

        // Rating grade
        $this->rating_grade = $this->getRatingGrade((float) $this->overall_score);
    }

    /**
     * Calculate delivery score (0-100)
     */
    private function calculateDeliveryScore(): float
    {
        if (!$this->expected_delivery_date || !$this->actual_delivery_date) {
            return 50; // Default if no data
        }

        $variance = $this->lead_time_variance_days;

        if ($variance <= 0) {
            return 100; // On time or early
        } elseif ($variance <= 2) {
            return 80; // 1-2 days late
        } elseif ($variance <= 5) {
            return 60; // 3-5 days late
        } elseif ($variance <= 10) {
            return 40; // 6-10 days late
        } else {
            return max(0, 20 - ($variance - 10)); // >10 days late
        }
    }

    /**
     * Calculate cost score (0-100)
     */
    private function calculateCostScore(): float
    {
        if ($this->total_po_value <= 0) {
            return 50;
        }

        $variancePercent = (($this->actual_po_value - $this->total_po_value) / $this->total_po_value) * 100;

        if ($variancePercent <= 0) {
            return 100; // Under or on budget
        } elseif ($variancePercent <= 5) {
            return 80; // 0-5% over
        } elseif ($variancePercent <= 10) {
            return 60; // 5-10% over
        } elseif ($variancePercent <= 20) {
            return 40; // 10-20% over
        } else {
            return max(0, 40 - ($variancePercent - 20)); // >20% over
        }
    }

    /**
     * Calculate responsiveness score (0-100)
     * 
     * TODO: Phase 2 Enhancement - Implement when communication tracking needed
     * 
     * Future implementation options:
     * 1. PO acknowledgment time tracking
     * 2. Email response time monitoring
     * 3. Quote request-to-response duration
     * 4. Issue resolution turnaround time
     * 
     * Current: Returns baseline score of 75 (neutral)
     * Impact: Only affects 15% of overall score, system is 85% functional without this
     * Priority: Low - Focus on delivery/quality/cost first
     */
    private function calculateResponsivenessScore(): float
    {
        // Phase 1: Baseline score (neutral performance)
        // All suppliers start with same responsiveness rating
        // TODO: Enhance with actual communication metrics in Phase 2
        return 75;
    }

    /**
     * Get rating grade based on score
     */
    private function getRatingGrade(float $score): string
    {
        if ($score >= 90)
            return 'A+';
        if ($score >= 85)
            return 'A';
        if ($score >= 80)
            return 'A-';
        if ($score >= 75)
            return 'B+';
        if ($score >= 70)
            return 'B';
        if ($score >= 65)
            return 'B-';
        if ($score >= 60)
            return 'C+';
        if ($score >= 55)
            return 'C';
        if ($score >= 50)
            return 'C-';
        if ($score >= 40)
            return 'D';
        return 'F';
    }

    /**
     * Get aggregated performance for supplier
     */
    public static function getSupplierPerformance(int $tenantId, int $supplierId, ?string $period = '90 days'): array
    {
        $query = self::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId);

        if ($period) {
            $query->where('evaluation_date', '>=', now()->subDays((int) $period));
        }

        $records = $query->orderByDesc('evaluation_date')->get();

        if ($records->isEmpty()) {
            return [
                'total_evaluations' => 0,
                'avg_overall_score' => 0,
                'avg_delivery_score' => 0,
                'avg_quality_score' => 0,
                'avg_cost_score' => 0,
                'on_time_delivery_rate' => 0,
                'avg_quality_rate' => 0,
                'current_grade' => 'N/A',
                'total_pos' => 0,
                'total_po_value' => 0,
                'trend' => 'stable',
            ];
        }

        $onTimeCount = $records->where('on_time_delivery', true)->count();
        $currentGrade = $records->first()->rating_grade ?? 'N/A';

        // Calculate trend (compare last 30 days vs previous 30 days)
        $last30Days = $records->where('evaluation_date', '>=', now()->subDays(30));
        $prev30Days = $records->whereBetween('evaluation_date', [now()->subDays(60), now()->subDays(30)]);

        $trend = 'stable';
        if ($last30Days->isNotEmpty() && $prev30Days->isNotEmpty()) {
            $currentAvg = $last30Days->avg('overall_score');
            $prevAvg = $prev30Days->avg('overall_score');
            $diff = $currentAvg - $prevAvg;

            if ($diff > 5)
                $trend = 'improving';
            elseif ($diff < -5)
                $trend = 'declining';
        }

        return [
            'total_evaluations' => $records->count(),
            'avg_overall_score' => round($records->avg('overall_score'), 2),
            'avg_delivery_score' => round($records->avg('delivery_score'), 2),
            'avg_quality_score' => round($records->avg('quality_score'), 2),
            'avg_cost_score' => round($records->avg('cost_score'), 2),
            'on_time_delivery_rate' => round(($onTimeCount / $records->count()) * 100, 1),
            'avg_quality_rate' => round($records->avg('quality_rate'), 2),
            'current_grade' => $currentGrade,
            'total_pos' => $records->unique('purchase_order_id')->count(),
            'total_po_value' => round($records->sum('total_po_value'), 2),
            'trend' => $trend,
            'chart_data' => self::getChartData($records),
        ];
    }

    /**
     * Get chart data for visualization
     */
    private static function getChartData($records): array
    {
        $grouped = $records->groupBy(function ($record) {
            return $record->evaluation_date->format('Y-m-d');
        });

        $labels = [];
        $overallScores = [];
        $deliveryScores = [];
        $qualityScores = [];
        $costScores = [];

        foreach ($grouped->sortByKeys() as $date => $dayRecords) {
            $labels[] = $date;
            $overallScores[] = round($dayRecords->avg('overall_score'), 2);
            $deliveryScores[] = round($dayRecords->avg('delivery_score'), 2);
            $qualityScores[] = round($dayRecords->avg('quality_score'), 2);
            $costScores[] = round($dayRecords->avg('cost_score'), 2);
        }

        return [
            'labels' => $labels,
            'overall' => $overallScores,
            'delivery' => $deliveryScores,
            'quality' => $qualityScores,
            'cost' => $costScores,
        ];
    }

    /**
     * Get supplier rankings
     */
    public static function getSupplierRankings(int $tenantId, int $limit = 10, ?string $period = '90 days'): array
    {
        $query = self::where('tenant_id', $tenantId);

        if ($period) {
            $query->where('evaluation_date', '>=', now()->subDays((int) $period));
        }

        $rankings = $query->selectRaw('
                supplier_id,
                COUNT(*) as evaluation_count,
                AVG(overall_score) as avg_score,
                AVG(delivery_score) as avg_delivery,
                AVG(quality_score) as avg_quality,
                AVG(cost_score) as avg_cost,
                SUM(CASE WHEN on_time_delivery = 1 THEN 1 ELSE 0 END) as on_time_count,
                AVG(quality_rate) as avg_quality_rate
            ')
            ->groupBy('supplier_id')
            ->orderByDesc('avg_score')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->supplier_name = Supplier::find($item->supplier_id)?->name ?? 'Unknown';
                $item->on_time_rate = $item->evaluation_count > 0
                    ? round(($item->on_time_count / $item->evaluation_count) * 100, 1)
                    : 0;
                $item->grade = self::getGradeFromScore($item->avg_score);
                return $item;
            });

        return $rankings->toArray();
    }

    /**
     * Get grade from score
     */
    private static function getGradeFromScore(float $score): string
    {
        if ($score >= 90)
            return 'A+';
        if ($score >= 85)
            return 'A';
        if ($score >= 80)
            return 'A-';
        if ($score >= 75)
            return 'B+';
        if ($score >= 70)
            return 'B';
        if ($score >= 65)
            return 'B-';
        if ($score >= 60)
            return 'C+';
        if ($score >= 55)
            return 'C';
        if ($score >= 50)
            return 'C-';
        if ($score >= 40)
            return 'D';
        return 'F';
    }
}
