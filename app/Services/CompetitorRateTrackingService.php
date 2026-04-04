<?php

namespace App\Services;

use App\Models\CompetitorRate;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CompetitorRateTrackingService - Monitor and analyze competitor pricing
 * 
 * Features:
 * - Manual rate entry
 * - Rate comparison analysis
 * - Trend tracking
 * - Positioning analysis
 * - Rate parity monitoring
 */
class CompetitorRateTrackingService
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Record a competitor rate
     */
    public function recordRate(array $data): CompetitorRate
    {
        $rateData = [
            'tenant_id' => $this->tenantId,
            'competitor_name' => $data['competitor_name'],
            'source' => $data['source'] ?? 'manual',
            'rate_date' => $data['rate_date'],
            'rate' => $data['rate'],
            'room_type' => $data['room_type'] ?? null,
            'amenities' => $data['amenities'] ?? [],
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $data['recorded_by'] ?? null,
        ];

        return CompetitorRate::create($rateData);
    }

    /**
     * Bulk record competitor rates
     */
    public function bulkRecordRates(array $rates): array
    {
        $results = [
            'created' => 0,
            'failed' => [],
        ];

        foreach ($rates as $rateData) {
            try {
                $this->recordRate($rateData);
                $results['created']++;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'data' => $rateData,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get competitor rate analysis
     */
    public function getRateAnalysis(Carbon $startDate, Carbon $endDate, ?string $competitor = null): array
    {
        $query = CompetitorRate::where('tenant_id', $this->tenantId)
            ->whereBetween('rate_date', [$startDate, $endDate]);

        if ($competitor) {
            $query->where('competitor_name', $competitor);
        }

        $rates = $query->get();

        if ($rates->isEmpty()) {
            return [
                'message' => 'No competitor rate data available for the selected period',
                'data' => [],
            ];
        }

        $groupedByCompetitor = $rates->groupBy('competitor_name');
        $analysis = [];

        foreach ($groupedByCompetitor as $competitorName => $competitorRates) {
            $analysis[$competitorName] = [
                'average_rate' => round($competitorRates->avg('rate'), 2),
                'lowest_rate' => round($competitorRates->min('rate'), 2),
                'highest_rate' => round($competitorRates->max('rate'), 2),
                'rate_range' => round($competitorRates->max('rate') - $competitorRates->min('rate'), 2),
                'data_points' => $competitorRates->count(),
                'trend' => $this->calculateTrend($competitorRates),
                'room_types' => $competitorRates->pluck('room_type')->unique()->filter()->values()->toArray(),
            ];
        }

        // Calculate market average
        $marketAverage = $rates->avg('rate');

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'market_average' => round($marketAverage, 2),
            'competitor_count' => $groupedByCompetitor->count(),
            'total_data_points' => $rates->count(),
            'competitors' => $analysis,
        ];
    }

    /**
     * Calculate rate trend
     */
    private function calculateTrend(Collection $rates): array
    {
        $sorted = $rates->sortBy('rate_date');

        if ($sorted->count() < 2) {
            return [
                'direction' => 'stable',
                'change_percentage' => 0,
            ];
        }

        $firstRate = $sorted->first()->rate;
        $lastRate = $sorted->last()->rate;

        $change = $lastRate - $firstRate;
        $changePercentage = $firstRate > 0 ? ($change / $firstRate) * 100 : 0;

        $direction = match (true) {
            $changePercentage > 5 => 'increasing',
            $changePercentage < -5 => 'decreasing',
            default => 'stable',
        };

        return [
            'direction' => $direction,
            'change_amount' => round($change, 2),
            'change_percentage' => round($changePercentage, 2),
            'start_rate' => $firstRate,
            'end_rate' => $lastRate,
        ];
    }

    /**
     * Compare our rates with competitors
     */
    public function compareWithCompetitors(Carbon $date): array
    {
        $competitorRates = CompetitorRate::where('tenant_id', $this->tenantId)
            ->where('rate_date', $date->format('Y-m-d'))
            ->get();

        if ($competitorRates->isEmpty()) {
            return [
                'message' => 'No competitor data for the selected date',
            ];
        }

        $ourRoomTypes = RoomType::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        $comparison = [];

        foreach ($ourRoomTypes as $roomType) {
            // Find comparable competitor rates
            $comparableRates = $competitorRates->filter(function ($rate) use ($roomType) {
                // Simple matching - can be enhanced with more sophisticated logic
                $roomTypeName = strtolower($roomType->name);
                $compRoomType = strtolower($rate->room_type ?? '');

                return str_contains($compRoomType, $roomTypeName) ||
                    str_contains($roomTypeName, $compRoomType) ||
                    $this->isSimilarRoomType($roomTypeName, $compRoomType);
            });

            if ($comparableRates->isEmpty()) {
                continue;
            }

            $competitorAvg = $comparableRates->avg('rate');
            $ourRate = $roomType->base_rate;
            $difference = $ourRate - $competitorAvg;
            $percentageDiff = $competitorAvg > 0 ? ($difference / $competitorAvg) * 100 : 0;

            $comparison[$roomType->name] = [
                'our_rate' => $ourRate,
                'competitor_average' => round($competitorAvg, 2),
                'competitor_lowest' => round($comparableRates->min('rate'), 2),
                'competitor_highest' => round($comparableRates->max('rate'), 2),
                'difference' => round($difference, 2),
                'percentage_difference' => round($percentageDiff, 2),
                'position' => match (true) {
                    $percentageDiff > 10 => 'premium',
                    $percentageDiff < -10 => 'discount',
                    default => 'competitive',
                },
                'competitor_count' => $comparableRates->pluck('competitor_name')->unique()->count(),
            ];
        }

        return [
            'date' => $date->format('Y-m-d'),
            'comparison' => $comparison,
            'summary' => [
                'premium_priced' => collect($comparison)->where('position', 'premium')->count(),
                'competitively_priced' => collect($comparison)->where('position', 'competitive')->count(),
                'discount_priced' => collect($comparison)->where('position', 'discount')->count(),
            ],
        ];
    }

    /**
     * Check if room types are similar
     */
    private function isSimilarRoomType(string $type1, string $type2): bool
    {
        $similarityMap = [
            'standard' => ['deluxe', 'superior', 'classic'],
            'deluxe' => ['standard', 'superior', 'premier'],
            'suite' => ['junior suite', 'executive', 'presidential'],
            'single' => ['standard', 'solo'],
            'double' => ['twin', 'queen', 'king'],
        ];

        foreach ($similarityMap as $key => $similar) {
            if (str_contains($type1, $key) && in_array($type2, $similar)) {
                return true;
            }
            if (str_contains($type2, $key) && in_array($type1, $similar)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get rate parity report
     */
    public function getRateParityReport(Carbon $startDate, Carbon $endDate): array
    {
        $period = Carbon::parse($startDate)->daysUntil($endDate);
        $parityIssues = [];

        foreach ($period as $date) {
            $comparison = $this->compareWithCompetitors($date);

            if (isset($comparison['comparison'])) {
                foreach ($comparison['comparison'] as $roomType => $data) {
                    // Flag significant discrepancies
                    if (abs($data['percentage_difference']) > 20) {
                        $parityIssues[] = [
                            'date' => $date->format('Y-m-d'),
                            'room_type' => $roomType,
                            'our_rate' => $data['our_rate'],
                            'market_average' => $data['competitor_average'],
                            'difference_percentage' => $data['percentage_difference'],
                            'issue_type' => $data['percentage_difference'] > 0 ? 'overpriced' : 'underpriced',
                            'severity' => abs($data['percentage_difference']) > 30 ? 'high' : 'medium',
                        ];
                    }
                }
            }
        }

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_issues' => count($parityIssues),
            'high_severity' => count(array_filter($parityIssues, fn($i) => $i['severity'] === 'high')),
            'medium_severity' => count(array_filter($parityIssues, fn($i) => $i['severity'] === 'medium')),
            'overpriced_count' => count(array_filter($parityIssues, fn($i) => $i['issue_type'] === 'overpriced')),
            'underpriced_count' => count(array_filter($parityIssues, fn($i) => $i['issue_type'] === 'underpriced')),
            'issues' => $parityIssues,
        ];
    }

    /**
     * Get competitor list
     */
    public function getCompetitors(): array
    {
        return CompetitorRate::where('tenant_id', $this->tenantId)
            ->distinct()
            ->pluck('competitor_name')
            ->toArray();
    }

    /**
     * Get rate history for a competitor
     */
    public function getCompetitorHistory(string $competitor, ?int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $rates = CompetitorRate::where('tenant_id', $this->tenantId)
            ->where('competitor_name', $competitor)
            ->where('rate_date', '>=', $startDate)
            ->orderBy('rate_date')
            ->get();

        if ($rates->isEmpty()) {
            return [
                'competitor' => $competitor,
                'message' => 'No historical data available',
            ];
        }

        $groupedByDate = $rates->groupBy('rate_date');
        $dailyAverages = [];

        foreach ($groupedByDate as $date => $dateRates) {
            $dailyAverages[] = [
                'date' => $date,
                'average_rate' => round($dateRates->avg('rate'), 2),
                'lowest_rate' => round($dateRates->min('rate'), 2),
                'highest_rate' => round($dateRates->max('rate'), 2),
                'data_points' => $dateRates->count(),
            ];
        }

        return [
            'competitor' => $competitor,
            'period_days' => $days,
            'data_points' => $rates->count(),
            'average_rate' => round($rates->avg('rate'), 2),
            'lowest_rate' => round($rates->min('rate'), 2),
            'highest_rate' => round($rates->max('rate'), 2),
            'daily_history' => $dailyAverages,
            'trend' => $this->calculateTrend($rates),
        ];
    }

    /**
     * Generate competitive positioning report
     */
    public function getPositioningReport(): array
    {
        $competitors = $this->getCompetitors();
        $ourRoomTypes = RoomType::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        $positioning = [];

        foreach ($ourRoomTypes as $roomType) {
            $recentRates = CompetitorRate::where('tenant_id', $this->tenantId)
                ->where('rate_date', '>=', now()->subDays(7))
                ->get();

            if ($recentRates->isEmpty()) {
                continue;
            }

            $marketAverage = $recentRates->avg('rate');
            $ourRate = $roomType->base_rate;
            $percentageDiff = $marketAverage > 0
                ? (($ourRate - $marketAverage) / $marketAverage) * 100
                : 0;

            $positioning[$roomType->name] = [
                'our_rate' => $ourRate,
                'market_average' => round($marketAverage, 2),
                'market_range' => [
                    'low' => round($recentRates->min('rate'), 2),
                    'high' => round($recentRates->max('rate'), 2),
                ],
                'position_vs_market' => round($percentageDiff, 2),
                'competitive_set_size' => $recentRates->pluck('competitor_name')->unique()->count(),
                'recommendation' => match (true) {
                    $percentageDiff > 15 => 'Consider reducing rates to improve competitiveness',
                    $percentageDiff < -20 => 'Opportunity to increase rates',
                    default => 'Rates are competitively positioned',
                },
            ];
        }

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'competitors_tracked' => count($competitors),
            'room_types_analyzed' => count($positioning),
            'positioning' => $positioning,
        ];
    }

    /**
     * Get alerts for significant competitor rate changes
     */
    public function getRateAlerts(): array
    {
        $alerts = [];
        $recentRates = CompetitorRate::where('tenant_id', $this->tenantId)
            ->where('created_at', '>=', now()->subDays(1))
            ->get();

        foreach ($recentRates as $rate) {
            // Compare with previous rate for same competitor and date
            $previousRate = CompetitorRate::where('tenant_id', $this->tenantId)
                ->where('competitor_name', $rate->competitor_name)
                ->where('rate_date', $rate->rate_date)
                ->where('id', '!=', $rate->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($previousRate) {
                $change = $rate->rate - $previousRate->rate;
                $changePercentage = $previousRate->rate > 0
                    ? ($change / $previousRate->rate) * 100
                    : 0;

                if (abs($changePercentage) >= 10) {
                    $alerts[] = [
                        'type' => 'significant_change',
                        'severity' => abs($changePercentage) >= 20 ? 'high' : 'medium',
                        'competitor' => $rate->competitor_name,
                        'date' => $rate->rate_date,
                        'old_rate' => $previousRate->rate,
                        'new_rate' => $rate->rate,
                        'change_amount' => round($change, 2),
                        'change_percentage' => round($changePercentage, 2),
                        'detected_at' => $rate->created_at,
                    ];
                }
            }
        }

        return [
            'alert_count' => count($alerts),
            'high_priority' => count(array_filter($alerts, fn($a) => $a['severity'] === 'high')),
            'alerts' => $alerts,
        ];
    }

    /**
     * Export competitor data
     */
    public function exportData(Carbon $startDate, Carbon $endDate, string $format = 'csv'): array
    {
        $rates = CompetitorRate::where('tenant_id', $this->tenantId)
            ->whereBetween('rate_date', [$startDate, $endDate])
            ->with('recordedBy')
            ->get();

        $data = $rates->map(fn($r) => [
            'competitor_name' => $r->competitor_name,
            'source' => $r->source,
            'rate_date' => $r->rate_date,
            'rate' => $r->rate,
            'room_type' => $r->room_type,
            'notes' => $r->notes,
            'recorded_by' => $r->recordedBy?->name,
            'recorded_at' => $r->created_at,
        ]);

        return [
            'format' => $format,
            'record_count' => $data->count(),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'data' => $data->toArray(),
        ];
    }
}
