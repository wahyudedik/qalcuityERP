<?php

namespace App\Services;

use App\Models\RatePlan;
use App\Models\DynamicPricingRule;
use App\Models\OccupancyForecast;
use App\Models\CompetitorRate;
use App\Models\SpecialEvent;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * DynamicPricingEngine - Advanced pricing calculation engine
 * 
 * Features:
 * - Occupancy-based pricing adjustments
 * - Competitor rate analysis
 * - Event-based pricing
 * - Day-of-week adjustments
 * - Length of stay discounts
 * - Advance booking incentives
 */
class DynamicPricingEngine
{
    private int $tenantId;
    private array $cache = [];

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Calculate optimal rate for a room type on a specific date
     */
    public function calculateOptimalRate(
        int $roomTypeId,
        Carbon $date,
        ?int $ratePlanId = null,
        array $context = []
    ): array {
        $roomType = RoomType::find($roomTypeId);
        if (!$roomType) {
            throw new \InvalidArgumentException('Room type not found');
        }

        // Get base rate
        $baseRate = $this->getBaseRate($roomTypeId, $ratePlanId, $date);

        // Apply all pricing rules
        $adjustments = [];
        $finalRate = $baseRate;

        // 1. Occupancy-based adjustment
        $occupancyAdjustment = $this->calculateOccupancyAdjustment($roomTypeId, $date);
        if ($occupancyAdjustment !== 0) {
            $adjustments[] = [
                'type' => 'occupancy',
                'description' => 'Occupancy-based adjustment',
                'amount' => $occupancyAdjustment,
            ];
            $finalRate += $occupancyAdjustment;
        }

        // 2. Competitor-based adjustment
        $competitorAdjustment = $this->calculateCompetitorAdjustment($roomType, $date);
        if ($competitorAdjustment !== 0) {
            $adjustments[] = [
                'type' => 'competitor',
                'description' => 'Competitor rate adjustment',
                'amount' => $competitorAdjustment,
            ];
            $finalRate += $competitorAdjustment;
        }

        // 3. Event-based adjustment
        $eventAdjustment = $this->calculateEventAdjustment($date);
        if ($eventAdjustment !== 0) {
            $adjustments[] = [
                'type' => 'event',
                'description' => 'Special event adjustment',
                'amount' => $eventAdjustment,
            ];
            $finalRate += $eventAdjustment;
        }

        // 4. Day of week adjustment
        $dowAdjustment = $this->calculateDayOfWeekAdjustment($date);
        if ($dowAdjustment !== 0) {
            $adjustments[] = [
                'type' => 'day_of_week',
                'description' => 'Day of week adjustment',
                'amount' => $dowAdjustment,
            ];
            $finalRate += $dowAdjustment;
        }

        // 5. Length of stay adjustment
        if (isset($context['length_of_stay'])) {
            $losAdjustment = $this->calculateLengthOfStayAdjustment($context['length_of_stay']);
            if ($losAdjustment !== 0) {
                $adjustments[] = [
                    'type' => 'length_of_stay',
                    'description' => 'Length of stay discount',
                    'amount' => $losAdjustment,
                ];
                $finalRate += $losAdjustment;
            }
        }

        // 6. Advance booking adjustment
        if (isset($context['booking_date'])) {
            $advanceAdjustment = $this->calculateAdvanceBookingAdjustment($context['booking_date'], $date);
            if ($advanceAdjustment !== 0) {
                $adjustments[] = [
                    'type' => 'advance_booking',
                    'description' => 'Advance booking incentive',
                    'amount' => $advanceAdjustment,
                ];
                $finalRate += $advanceAdjustment;
            }
        }

        // Apply dynamic pricing rules from database
        $ruleAdjustments = $this->applyDynamicPricingRules($baseRate, $date, $context);
        foreach ($ruleAdjustments as $adj) {
            $adjustments[] = $adj;
            $finalRate += $adj['amount'];
        }

        // Ensure rate is within bounds
        $finalRate = $this->applyRateBounds($finalRate, $roomTypeId);

        return [
            'room_type_id' => $roomTypeId,
            'date' => $date->format('Y-m-d'),
            'base_rate' => round($baseRate, 2),
            'final_rate' => round($finalRate, 2),
            'total_adjustment' => round($finalRate - $baseRate, 2),
            'adjustment_percentage' => $baseRate > 0 ? round((($finalRate - $baseRate) / $baseRate) * 100, 2) : 0,
            'adjustments' => $adjustments,
            'factors' => $this->getPricingFactors($roomTypeId, $date),
        ];
    }

    /**
     * Get base rate from rate plan or room type
     */
    private function getBaseRate(int $roomTypeId, ?int $ratePlanId, Carbon $date): float
    {
        if ($ratePlanId) {
            $ratePlan = RatePlan::where('id', $ratePlanId)
                ->where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->first();

            if ($ratePlan && $ratePlan->isValidForDate($date)) {
                return $ratePlan->getEffectiveRate($date);
            }
        }

        // Fallback to room type base rate
        $roomType = RoomType::find($roomTypeId);
        return $roomType?->base_rate ?? 0;
    }

    /**
     * Calculate occupancy-based rate adjustment
     */
    private function calculateOccupancyAdjustment(int $roomTypeId, Carbon $date): float
    {
        $forecast = OccupancyForecast::where('tenant_id', $this->tenantId)
            ->where('room_type_id', $roomTypeId)
            ->where('forecast_date', $date->format('Y-m-d'))
            ->first();

        if (!$forecast) {
            return 0;
        }

        $occupancyRate = $forecast->projected_occupancy_rate;

        // Dynamic pricing based on forecasted occupancy
        return match (true) {
            $occupancyRate >= 90 => 50,  // High demand: +50
            $occupancyRate >= 80 => 30,  // Very high: +30
            $occupancyRate >= 70 => 15,  // High: +15
            $occupancyRate >= 60 => 0,   // Normal: no change
            $occupancyRate >= 40 => -10, // Low: -10
            $occupancyRate >= 20 => -20, // Very low: -20
            default => -30,              // Extremely low: -30
        };
    }

    /**
     * Calculate competitor-based adjustment
     */
    private function calculateCompetitorAdjustment(RoomType $roomType, Carbon $date): float
    {
        $avgCompetitorRate = CompetitorRate::getAverageRate(
            $this->tenantId,
            $date,
            $date
        );

        if ($avgCompetitorRate <= 0) {
            return 0;
        }

        $currentRate = $roomType->base_rate;
        $difference = $avgCompetitorRate - $currentRate;
        $percentageDiff = ($difference / $currentRate) * 100;

        // Adjust to stay competitive (within 5% of average)
        if ($percentageDiff > 10) {
            // Competitors are charging more - we can increase
            return min($difference * 0.3, 20);
        } elseif ($percentageDiff < -10) {
            // Competitors are charging less - consider decreasing
            return max($difference * 0.3, -20);
        }

        return 0;
    }

    /**
     * Calculate event-based adjustment
     */
    private function calculateEventAdjustment(Carbon $date): float
    {
        $events = SpecialEvent::getEventsForPeriod(
            $this->tenantId,
            $date,
            $date
        );

        $totalAdjustment = 0;

        foreach ($events as $event) {
            if ($event->affects_pricing) {
                $multiplier = $event->getImpactMultiplier();
                $baseAdjustment = 20; // Base event adjustment
                $totalAdjustment += $baseAdjustment * ($multiplier - 1);
            }
        }

        return $totalAdjustment;
    }

    /**
     * Calculate day of week adjustment
     */
    private function calculateDayOfWeekAdjustment(Carbon $date): float
    {
        $dayOfWeek = $date->dayOfWeek;

        return match ($dayOfWeek) {
            5, 6 => 15,  // Friday, Saturday: +15
            0 => 10,     // Sunday: +10
            4 => 5,      // Thursday: +5
            1, 2, 3 => -5, // Monday-Wednesday: -5
            default => 0,
        };
    }

    /**
     * Calculate length of stay adjustment (discount for longer stays)
     */
    private function calculateLengthOfStayAdjustment(int $nights): float
    {
        return match (true) {
            $nights >= 14 => -30,  // 2+ weeks: -30
            $nights >= 7 => -20,   // 1+ week: -20
            $nights >= 5 => -10,   // 5+ nights: -10
            $nights >= 3 => -5,    // 3+ nights: -5
            default => 0,
        };
    }

    /**
     * Calculate advance booking adjustment
     */
    private function calculateAdvanceBookingAdjustment(Carbon $bookingDate, Carbon $checkInDate): float
    {
        $daysInAdvance = $bookingDate->diffInDays($checkInDate, false);

        return match (true) {
            $daysInAdvance >= 90 => -15,  // 90+ days: -15
            $daysInAdvance >= 60 => -10,  // 60+ days: -10
            $daysInAdvance >= 30 => -5,   // 30+ days: -5
            $daysInAdvance <= 1 => 10,    // Last minute: +10
            default => 0,
        };
    }

    /**
     * Apply dynamic pricing rules from database
     */
    private function applyDynamicPricingRules(float $baseRate, Carbon $date, array $context): array
    {
        $adjustments = [];

        $rules = DynamicPricingRule::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date);
            })
            ->orderBy('priority')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matchesConditions($date)) {
                $amount = $rule->adjustment_type === 'percentage'
                    ? $baseRate * ($rule->adjustment_value / 100)
                    : $rule->adjustment_value;

                $adjustments[] = [
                    'type' => 'rule:' . $rule->rule_type,
                    'description' => $rule->name,
                    'amount' => round($amount, 2),
                    'rule_id' => $rule->id,
                ];
            }
        }

        return $adjustments;
    }

    /**
     * Apply minimum and maximum rate bounds
     */
    private function applyRateBounds(float $rate, int $roomTypeId): float
    {
        $roomType = RoomType::find($roomTypeId);
        if (!$roomType) {
            return max(0, $rate);
        }

        $minRate = $roomType->base_rate * 0.5;  // Minimum 50% of base
        $maxRate = $roomType->base_rate * 2.0;  // Maximum 200% of base

        return max($minRate, min($maxRate, $rate));
    }

    /**
     * Get pricing factors for analysis
     */
    private function getPricingFactors(int $roomTypeId, Carbon $date): array
    {
        $forecast = OccupancyForecast::where('tenant_id', $this->tenantId)
            ->where('room_type_id', $roomTypeId)
            ->where('forecast_date', $date->format('Y-m-d'))
            ->first();

        $events = SpecialEvent::getEventsForPeriod(
            $this->tenantId,
            $date,
            $date
        );

        $avgCompetitorRate = CompetitorRate::getAverageRate(
            $this->tenantId,
            $date,
            $date
        );

        return [
            'forecasted_occupancy' => $forecast?->projected_occupancy_rate ?? null,
            'forecast_confidence' => $forecast?->confidence_level ?? null,
            'active_events' => $events->pluck('name')->toArray(),
            'competitor_avg_rate' => $avgCompetitorRate > 0 ? $avgCompetitorRate : null,
            'day_of_week' => $date->format('l'),
            'is_weekend' => in_array($date->dayOfWeek, [5, 6, 0]),
        ];
    }

    /**
     * Bulk calculate rates for a date range
     */
    public function calculateRateRange(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $ratePlanId = null,
        array $context = []
    ): array {
        $rates = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $rates[] = $this->calculateOptimalRate($roomTypeId, $current->copy(), $ratePlanId, $context);
            $current->addDay();
        }

        return $rates;
    }

    /**
     * Get rate calendar for all room types
     */
    public function getRateCalendar(Carbon $startDate, Carbon $endDate): array
    {
        $roomTypes = RoomType::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        $calendar = [];

        foreach ($roomTypes as $roomType) {
            $calendar[$roomType->id] = [
                'room_type' => $roomType->name,
                'base_rate' => $roomType->base_rate,
                'rates' => $this->calculateRateRange($roomType->id, $startDate, $endDate),
            ];
        }

        return $calendar;
    }
}
