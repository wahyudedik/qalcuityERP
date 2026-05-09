<?php

namespace App\Services;

use App\Models\OccupancyForecast;
use App\Models\PricingRecommendation;
use App\Models\RatePlan;
use App\Models\RevenueSnapshot;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * RateOptimizationService - Yield Management and Rate Optimization
 *
 * Features:
 * - Revenue optimization algorithms
 * - Length of stay optimization
 * - Overbooking optimization
 * - Channel mix optimization
 * - Automated pricing recommendations
 */
class RateOptimizationService
{
    private int $tenantId;

    private DynamicPricingEngine $pricingEngine;

    private OccupancyForecastingService $forecastingService;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->pricingEngine = new DynamicPricingEngine($tenantId);
        $this->forecastingService = new OccupancyForecastingService($tenantId);
    }

    /**
     * Generate pricing recommendations for all room types
     */
    public function generateRecommendations(Carbon $startDate, Carbon $endDate): Collection
    {
        $recommendations = collect();
        $roomTypes = RoomType::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($roomTypes as $roomType) {
            $roomRecommendations = $this->generateRoomTypeRecommendations(
                $roomType,
                $startDate,
                $endDate
            );
            $recommendations = $recommendations->merge($roomRecommendations);
        }

        return $recommendations;
    }

    /**
     * Generate recommendations for a specific room type
     */
    public function generateRoomTypeRecommendations(
        RoomType $roomType,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $recommendations = collect();
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $recommendation = $this->analyzeAndRecommend($roomType, $current);

            if ($recommendation) {
                $recommendations->push($recommendation);
            }

            $current->addDay();
        }

        return $recommendations;
    }

    /**
     * Analyze market conditions and generate recommendation
     */
    private function analyzeAndRecommend(RoomType $roomType, Carbon $date): ?PricingRecommendation
    {
        $currentRate = $roomType->base_rate;

        // Get optimal rate from pricing engine
        $optimalRate = $this->pricingEngine->calculateOptimalRate(
            $roomType->id,
            $date
        );

        $recommendedRate = $optimalRate['final_rate'];
        $changePercentage = $currentRate > 0
            ? (($recommendedRate - $currentRate) / $currentRate) * 100
            : 0;

        // Only create recommendation if change is significant (> 5%)
        if (abs($changePercentage) < 5) {
            return null;
        }

        // Build reasoning
        $reasoning = $this->buildRecommendationReasoning($roomType, $date, $optimalRate);

        // Check if recommendation already exists
        $existing = PricingRecommendation::where('tenant_id', $this->tenantId)
            ->where('room_type_id', $roomType->id)
            ->where('recommendation_date', $date->format('Y-m-d'))
            ->where('status', 'pending')
            ->first();

        $data = [
            'tenant_id' => $this->tenantId,
            'room_type_id' => $roomType->id,
            'recommendation_date' => $date->format('Y-m-d'),
            'current_rate' => $currentRate,
            'recommended_rate' => $recommendedRate,
            'suggested_change_percentage' => round($changePercentage, 2),
            'reasoning' => $reasoning,
            'supporting_data' => $optimalRate,
            'status' => 'pending',
        ];

        if ($existing) {
            $existing->update($data);

            return $existing->fresh();
        }

        return PricingRecommendation::create($data);
    }

    /**
     * Build detailed reasoning for recommendation
     */
    private function buildRecommendationReasoning(
        RoomType $roomType,
        Carbon $date,
        array $optimalRate
    ): string {
        $factors = $optimalRate['factors'];
        $reasons = [];

        // Occupancy factor
        if ($factors['forecasted_occupancy'] !== null) {
            $occupancy = $factors['forecasted_occupancy'];
            if ($occupancy >= 80) {
                $reasons[] = "High forecasted occupancy ({$occupancy}%) indicates strong demand";
            } elseif ($occupancy <= 40) {
                $reasons[] = "Low forecasted occupancy ({$occupancy}%) suggests need for rate reduction";
            }
        }

        // Competitor factor
        if ($factors['competitor_avg_rate'] !== null) {
            $competitorAvg = $factors['competitor_avg_rate'];
            $difference = (($competitorAvg - $roomType->base_rate) / $roomType->base_rate) * 100;

            if ($difference > 10) {
                $reasons[] = "Competitors averaging {$competitorAvg} ({$difference}% higher)";
            } elseif ($difference < -10) {
                $reasons[] = "Competitors averaging {$competitorAvg} (".abs($difference).'% lower)';
            }
        }

        // Event factor
        if (! empty($factors['active_events'])) {
            $events = implode(', ', $factors['active_events']);
            $reasons[] = "Special events affecting demand: {$events}";
        }

        // Day of week factor
        if ($factors['is_weekend']) {
            $reasons[] = 'Weekend premium pricing opportunity';
        }

        // Adjustment summary
        $adjustments = $optimalRate['adjustments'];
        if (! empty($adjustments)) {
            $adjustmentNames = array_column($adjustments, 'type');
            $reasons[] = 'Applied adjustments: '.implode(', ', $adjustmentNames);
        }

        return implode('. ', $reasons).'.';
    }

    /**
     * Optimize rates for maximum revenue (Yield Management)
     */
    public function optimizeYield(Carbon $startDate, Carbon $endDate): array
    {
        $results = [];
        $roomTypes = RoomType::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($roomTypes as $roomType) {
            $optimization = $this->optimizeRoomTypeYield($roomType, $startDate, $endDate);
            $results[$roomType->id] = $optimization;
        }

        return $results;
    }

    /**
     * Optimize yield for a specific room type
     */
    private function optimizeRoomTypeYield(
        RoomType $roomType,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        // Get forecasts
        $forecasts = $this->forecastingService->generateForecast(
            $startDate,
            $endDate,
            $roomType->id
        );

        $currentRevenue = 0;
        $optimizedRevenue = 0;
        $recommendations = [];

        foreach ($forecasts as $forecast) {
            $date = Carbon::parse($forecast->forecast_date);

            // Current projected revenue
            $currentRate = $roomType->base_rate;
            $currentRevpar = $currentRate * ($forecast->projected_occupancy_rate / 100);
            $currentRevenue += $currentRevpar * $roomType->rooms_count;

            // Optimized rate
            $optimalRate = $this->pricingEngine->calculateOptimalRate(
                $roomType->id,
                $date
            );

            // Adjust occupancy based on rate change (price elasticity)
            $rateChange = ($optimalRate['final_rate'] - $currentRate) / $currentRate;
            $elasticityFactor = -0.8; // Typical hotel price elasticity
            $occupancyChange = $rateChange * $elasticityFactor * 100;

            $adjustedOccupancy = max(0, min(
                100,
                $forecast->projected_occupancy_rate + $occupancyChange
            ));

            $optimizedRevpar = $optimalRate['final_rate'] * ($adjustedOccupancy / 100);
            $optimizedRevenue += $optimizedRevpar * $roomType->rooms_count;

            if (abs($optimalRate['final_rate'] - $currentRate) > ($currentRate * 0.05)) {
                $recommendations[] = [
                    'date' => $date->format('Y-m-d'),
                    'current_rate' => $currentRate,
                    'recommended_rate' => $optimalRate['final_rate'],
                    'current_occupancy' => round($forecast->projected_occupancy_rate, 1),
                    'adjusted_occupancy' => round($adjustedOccupancy, 1),
                    'revenue_impact' => round($optimizedRevpar - $currentRevpar, 2),
                ];
            }
        }

        $revenueLift = $currentRevenue > 0
            ? (($optimizedRevenue - $currentRevenue) / $currentRevenue) * 100
            : 0;

        return [
            'room_type' => $roomType->name,
            'current_projected_revenue' => round($currentRevenue, 2),
            'optimized_projected_revenue' => round($optimizedRevenue, 2),
            'revenue_lift_percentage' => round($revenueLift, 2),
            'revenue_lift_amount' => round($optimizedRevenue - $currentRevenue, 2),
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Calculate optimal length of stay restrictions
     */
    public function calculateLosRestrictions(Carbon $date): array
    {
        $forecasts = OccupancyForecast::where('tenant_id', $this->tenantId)
            ->whereBetween('forecast_date', [
                $date->format('Y-m-d'),
                $date->copy()->addDays(7)->format('Y-m-d'),
            ])
            ->get();

        $highDemandDates = $forecasts->filter(fn ($f) => $f->projected_occupancy_rate >= 85);
        $lowDemandDates = $forecasts->filter(fn ($f) => $f->projected_occupancy_rate <= 40);

        $restrictions = [];

        // High demand dates - enforce minimum stay
        if ($highDemandDates->count() >= 2) {
            $restrictions[] = [
                'type' => 'minimum_stay',
                'dates' => $highDemandDates->pluck('forecast_date')->toArray(),
                'value' => 2,
                'reason' => 'High demand period - minimum 2-night stay',
            ];
        }

        // Low demand dates - allow flexible stays
        if ($lowDemandDates->count() >= 3) {
            $restrictions[] = [
                'type' => 'maximum_discount',
                'dates' => $lowDemandDates->pluck('forecast_date')->toArray(),
                'value' => 20,
                'reason' => 'Low demand period - up to 20% discount allowed',
            ];
        }

        return $restrictions;
    }

    /**
     * Optimize channel mix for maximum profitability
     */
    public function optimizeChannelMix(Carbon $startDate, Carbon $endDate): array
    {
        $snapshots = RevenueSnapshot::where('tenant_id', $this->tenantId)
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->get();

        if ($snapshots->isEmpty()) {
            return [
                'message' => 'Insufficient data for channel mix optimization',
                'recommendations' => [],
            ];
        }

        $channelData = [];

        foreach ($snapshots as $snapshot) {
            $breakdown = $snapshot->breakdown_by_channel ?? [];

            foreach ($breakdown as $channel => $data) {
                if (! isset($channelData[$channel])) {
                    $channelData[$channel] = [
                        'bookings' => 0,
                        'revenue' => 0,
                        'commission_cost' => 0,
                    ];
                }

                $channelData[$channel]['bookings'] += $data['bookings'] ?? 0;
                $channelData[$channel]['revenue'] += $data['revenue'] ?? 0;

                // Estimate commission (varies by channel)
                $commissionRate = match ($channel) {
                    'bookingcom' => 0.15,
                    'expedia' => 0.18,
                    'agoda' => 0.15,
                    'airbnb' => 0.03,
                    'direct' => 0,
                    default => 0.10,
                };

                $channelData[$channel]['commission_cost'] += ($data['revenue'] ?? 0) * $commissionRate;
            }
        }

        // Calculate net revenue and profitability
        foreach ($channelData as $channel => &$data) {
            $data['net_revenue'] = $data['revenue'] - $data['commission_cost'];
            $data['commission_percentage'] = $data['revenue'] > 0
                ? ($data['commission_cost'] / $data['revenue']) * 100
                : 0;
            $data['avg_booking_value'] = $data['bookings'] > 0
                ? $data['revenue'] / $data['bookings']
                : 0;
        }

        // Sort by net revenue
        uasort($channelData, fn ($a, $b) => $b['net_revenue'] <=> $a['net_revenue']);

        // Generate recommendations
        $recommendations = [];
        $directShare = $channelData['direct']['bookings'] ?? 0;
        $totalBookings = array_sum(array_column($channelData, 'bookings'));
        $directPercentage = $totalBookings > 0 ? ($directShare / $totalBookings) * 100 : 0;

        if ($directPercentage < 30) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'increase_direct_bookings',
                'message' => 'Direct bookings at '.round($directPercentage, 1).'%. Target: 40%+',
                'suggested_actions' => [
                    'Offer exclusive perks for direct bookings',
                    'Implement best rate guarantee',
                    'Reduce OTA dependency through targeted campaigns',
                ],
            ];
        }

        // Find high-commission channels
        $highCommissionChannels = array_filter(
            $channelData,
            fn ($d) => $d['commission_percentage'] > 15
        );

        if (! empty($highCommissionChannels)) {
            $channelNames = implode(', ', array_keys($highCommissionChannels));
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'reduce_high_commission_dependency',
                'message' => "High commission channels detected: {$channelNames}",
                'suggested_actions' => [
                    'Negotiate lower commission rates',
                    'Reduce availability on high-commission channels',
                    'Promote alternative booking methods',
                ],
            ];
        }

        return [
            'channel_performance' => $channelData,
            'recommendations' => $recommendations,
            'target_mix' => [
                'direct' => 40,
                'bookingcom' => 25,
                'expedia' => 15,
                'other' => 20,
            ],
        ];
    }

    /**
     * Calculate overbooking recommendations
     */
    public function calculateOverbookingRecommendation(): array
    {
        // Analyze historical no-show and cancellation rates
        $recentStats = RevenueSnapshot::where('tenant_id', $this->tenantId)
            ->where('snapshot_date', '>=', now()->subDays(30))
            ->get();

        $avgCancellations = $recentStats->avg('cancellations_today') ?? 0;
        $totalReservations = $recentStats->sum('total_reservations') ?? 1;
        $cancellationRate = ($avgCancellations / max(1, $totalReservations)) * 100;

        // Calculate recommended overbooking level
        $recommendedOverbooking = match (true) {
            $cancellationRate >= 15 => 10,  // High cancellation rate
            $cancellationRate >= 10 => 7,   // Medium-high
            $cancellationRate >= 5 => 5,    // Medium
            default => 3,                   // Low
        };

        return [
            'average_cancellation_rate' => round($cancellationRate, 2),
            'recommended_overbooking_percentage' => $recommendedOverbooking,
            'risk_level' => match (true) {
                $cancellationRate >= 15 => 'high',
                $cancellationRate >= 10 => 'medium',
                default => 'low',
            },
            'recommendation' => "Overbook by {$recommendedOverbooking}% to maximize occupancy",
            'caution' => 'Monitor walk-in availability and have upgrade options ready',
        ];
    }

    /**
     * Get revenue management KPIs
     */
    public function getKPIs(Carbon $startDate, Carbon $endDate): array
    {
        $snapshots = RevenueSnapshot::where('tenant_id', $this->tenantId)
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->get();

        if ($snapshots->isEmpty()) {
            return [
                'message' => 'No data available for the selected period',
            ];
        }

        $roomCount = Room::where('tenant_id', $this->tenantId)
            ->where('status', '!=', 'out_of_order')
            ->count();

        $totalRoomNights = $roomCount * $startDate->diffInDays($endDate);

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate),
            ],
            'occupancy' => [
                'average' => round($snapshots->avg('occupancy_rate'), 2),
                'highest' => round($snapshots->max('occupancy_rate'), 2),
                'lowest' => round($snapshots->min('occupancy_rate'), 2),
            ],
            'adr' => [
                'average' => round($snapshots->avg('adr'), 2),
                'highest' => round($snapshots->max('adr'), 2),
                'lowest' => round($snapshots->min('adr'), 2),
            ],
            'revpar' => [
                'average' => round($snapshots->avg('revpar'), 2),
                'highest' => round($snapshots->max('revpar'), 2),
                'lowest' => round($snapshots->min('revpar'), 2),
            ],
            'revenue' => [
                'total' => round($snapshots->sum('total_revenue'), 2),
                'average_daily' => round($snapshots->avg('total_revenue'), 2),
            ],
            'pickup' => [
                'total_bookings' => $snapshots->sum('new_bookings_today'),
                'total_cancellations' => $snapshots->sum('cancellations_today'),
                'net_pickup' => $snapshots->sum('new_bookings_today') - $snapshots->sum('cancellations_today'),
            ],
        ];
    }

    /**
     * Apply bulk rate updates
     */
    public function applyBulkRateUpdate(array $updates): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($updates as $update) {
            try {
                $roomType = RoomType::where('tenant_id', $this->tenantId)
                    ->where('id', $update['room_type_id'])
                    ->first();

                if (! $roomType) {
                    throw new \Exception('Room type not found');
                }

                $oldRate = $roomType->base_rate;
                $roomType->update(['base_rate' => $update['new_rate']]);

                // Create rate plan update if specified
                if (isset($update['rate_plan_id'])) {
                    $ratePlan = RatePlan::find($update['rate_plan_id']);
                    if ($ratePlan) {
                        $ratePlan->update(['base_rate' => $update['new_rate']]);
                    }
                }

                $results['success'][] = [
                    'room_type_id' => $roomType->id,
                    'room_type_name' => $roomType->name,
                    'old_rate' => $oldRate,
                    'new_rate' => $update['new_rate'],
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'update' => $update,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
