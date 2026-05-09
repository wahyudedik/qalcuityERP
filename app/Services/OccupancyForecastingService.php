<?php

namespace App\Services;

use App\Models\OccupancyForecast;
use App\Models\Reservation;
use App\Models\RevenueSnapshot;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\SpecialEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * OccupancyForecastingService - Predicts future occupancy and demand
 *
 * Features:
 * - Historical data analysis
 * - Seasonal pattern recognition
 * - Event impact prediction
 * - Booking pace analysis
 * - Multi-factor forecasting
 */
class OccupancyForecastingService
{
    private int $tenantId;

    private array $config;

    public function __construct(int $tenantId, array $config = [])
    {
        $this->tenantId = $tenantId;
        $this->config = array_merge([
            'historical_weight' => 0.4,
            'booking_pace_weight' => 0.3,
            'events_weight' => 0.2,
            'seasonal_weight' => 0.1,
            'confidence_threshold' => 0.7,
        ], $config);
    }

    /**
     * Generate occupancy forecast for a date range
     */
    public function generateForecast(Carbon $startDate, Carbon $endDate, ?int $roomTypeId = null): Collection
    {
        $forecasts = collect();
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $forecast = $this->calculateDailyForecast($date, $roomTypeId);
            $forecasts->push($forecast);
        }

        return $forecasts;
    }

    /**
     * Calculate forecast for a single day
     */
    public function calculateDailyForecast(Carbon $date, ?int $roomTypeId = null): OccupancyForecast
    {
        // Check if forecast already exists
        $existing = OccupancyForecast::where('tenant_id', $this->tenantId)
            ->where('forecast_date', $date->format('Y-m-d'))
            ->where('room_type_id', $roomTypeId)
            ->first();

        if ($existing && $existing->updated_at->diffInHours(now()) < 24) {
            return $existing;
        }

        // Get total rooms
        $totalRooms = $this->getTotalRooms($roomTypeId);

        // Calculate components
        $historicalPrediction = $this->predictFromHistoricalData($date, $roomTypeId);
        $bookingPacePrediction = $this->predictFromBookingPace($date, $roomTypeId);
        $eventImpact = $this->calculateEventImpact($date);
        $seasonalFactor = $this->calculateSeasonalFactor($date);

        // Weighted combination
        $projectedOccupancy = (
            $historicalPrediction * $this->config['historical_weight'] +
            $bookingPacePrediction * $this->config['booking_pace_weight'] +
            $eventImpact * $this->config['events_weight'] +
            $seasonalFactor * $this->config['seasonal_weight']
        );

        // Calculate confidence level
        $confidence = $this->calculateConfidence($date, $historicalPrediction, $bookingPacePrediction);

        // Calculate derived metrics
        $projectedBooked = (int) round($totalRooms * ($projectedOccupancy / 100));
        $projectedAvailable = $totalRooms - $projectedBooked;
        $projectedAdr = $this->predictADR($date, $roomTypeId, $projectedOccupancy);
        $projectedRevpar = $projectedAdr * ($projectedOccupancy / 100);

        $factors = [
            'historical_prediction' => round($historicalPrediction, 2),
            'booking_pace_prediction' => round($bookingPacePrediction, 2),
            'event_impact' => round($eventImpact, 2),
            'seasonal_factor' => round($seasonalFactor, 2),
            'days_until_date' => now()->diffInDays($date, false),
        ];

        $forecastData = [
            'tenant_id' => $this->tenantId,
            'room_type_id' => $roomTypeId,
            'forecast_date' => $date->format('Y-m-d'),
            'total_rooms' => $totalRooms,
            'projected_booked' => $projectedBooked,
            'projected_available' => $projectedAvailable,
            'projected_occupancy_rate' => round($projectedOccupancy, 2),
            'projected_adr' => round($projectedAdr, 2),
            'projected_revpar' => round($projectedRevpar, 2),
            'confidence_level' => round($confidence, 2),
            'factors' => $factors,
        ];

        if ($existing) {
            $existing->update($forecastData);

            return $existing->fresh();
        }

        return OccupancyForecast::create($forecastData);
    }

    /**
     * Predict occupancy based on historical data
     */
    private function predictFromHistoricalData(Carbon $date, ?int $roomTypeId = null): float
    {
        // Get same day of week from past 8 weeks
        $historicalOccupancy = [];

        for ($weeksAgo = 1; $weeksAgo <= 8; $weeksAgo++) {
            $pastDate = $date->copy()->subWeeks($weeksAgo);

            $snapshot = RevenueSnapshot::where('tenant_id', $this->tenantId)
                ->where('snapshot_date', $pastDate->format('Y-m-d'))
                ->first();

            if ($snapshot) {
                $historicalOccupancy[] = $snapshot->occupancy_rate;
            }
        }

        // Also check same date last year
        $lastYear = $date->copy()->subYear();
        $lastYearSnapshot = RevenueSnapshot::where('tenant_id', $this->tenantId)
            ->where('snapshot_date', $lastYear->format('Y-m-d'))
            ->first();

        if ($lastYearSnapshot) {
            $historicalOccupancy[] = $lastYearSnapshot->occupancy_rate;
        }

        if (empty($historicalOccupancy)) {
            // No historical data - use default
            return 60.0;
        }

        // Weighted average (recent weeks weighted more)
        $weightedSum = 0;
        $weightSum = 0;

        foreach ($historicalOccupancy as $index => $occupancy) {
            $weight = $index + 1;
            $weightedSum += $occupancy * $weight;
            $weightSum += $weight;
        }

        return $weightSum > 0 ? $weightedSum / $weightSum : 60.0;
    }

    /**
     * Predict occupancy based on current booking pace
     */
    private function predictFromBookingPace(Carbon $date, ?int $roomTypeId = null): float
    {
        $daysUntil = now()->diffInDays($date, false);

        if ($daysUntil < 0) {
            // Date is in the past
            return 0;
        }

        // Get current reservations for this date
        $query = Reservation::where('tenant_id', $this->tenantId)
            ->where('check_in_date', '<=', $date->format('Y-m-d'))
            ->where('check_out_date', '>', $date->format('Y-m-d'))
            ->whereIn('status', ['confirmed', 'checked_in']);

        if ($roomTypeId) {
            $query->whereHas('room', function ($q) use ($roomTypeId) {
                $q->where('room_type_id', $roomTypeId);
            });
        }

        $currentBookings = $query->count();
        $totalRooms = $this->getTotalRooms($roomTypeId);

        if ($totalRooms == 0) {
            return 0;
        }

        $currentOccupancy = ($currentBookings / $totalRooms) * 100;

        // Adjust based on typical booking pace
        $paceMultiplier = $this->getBookingPaceMultiplier($daysUntil);

        return min(100, $currentOccupancy * $paceMultiplier);
    }

    /**
     * Get booking pace multiplier based on days until arrival
     */
    private function getBookingPaceMultiplier(int $daysUntil): float
    {
        // Typical hotel booking patterns
        return match (true) {
            $daysUntil > 90 => 2.0,   // Far out: expect 2x more bookings
            $daysUntil > 60 => 1.5,   // 2-3 months: expect 50% more
            $daysUntil > 30 => 1.3,   // 1-2 months: expect 30% more
            $daysUntil > 14 => 1.15,  // 2-4 weeks: expect 15% more
            $daysUntil > 7 => 1.05,   // 1-2 weeks: expect 5% more
            $daysUntil >= 0 => 1.0,   // Less than a week: minimal change
            default => 1.0,
        };
    }

    /**
     * Calculate impact of special events
     */
    private function calculateEventImpact(Carbon $date): float
    {
        $events = SpecialEvent::getEventsForPeriod(
            $this->tenantId,
            $date,
            $date
        );

        if ($events->isEmpty()) {
            return 60.0; // Baseline
        }

        $totalImpact = 0;
        foreach ($events as $event) {
            $baseOccupancy = match ($event->impact_level) {
                'very_high' => 95,
                'high' => 85,
                'medium' => 75,
                'low' => 65,
                default => 60,
            };
            $totalImpact += $baseOccupancy;
        }

        return $totalImpact / $events->count();
    }

    /**
     * Calculate seasonal factor
     */
    private function calculateSeasonalFactor(Carbon $date): float
    {
        $month = $date->month;
        $dayOfWeek = $date->dayOfWeek;

        // Seasonal multipliers (can be customized per property)
        $seasonalMultipliers = [
            1 => 0.7,   // January - low season
            2 => 0.75,  // February
            3 => 0.85,  // March
            4 => 0.9,   // April
            5 => 0.95,  // May
            6 => 1.0,   // June
            7 => 1.15,  // July - peak summer
            8 => 1.15,  // August - peak summer
            9 => 0.95,  // September
            10 => 0.9,  // October
            11 => 0.8,  // November
            12 => 0.85, // December - holidays
        ];

        $baseOccupancy = 60;
        $seasonalOccupancy = $baseOccupancy * ($seasonalMultipliers[$month] ?? 1.0);

        // Day of week adjustment
        $dowMultiplier = match ($dayOfWeek) {
            5, 6 => 1.2,  // Friday, Saturday
            0 => 1.1,     // Sunday
            default => 1.0,
        };

        return min(100, $seasonalOccupancy * $dowMultiplier);
    }

    /**
     * Predict ADR based on various factors
     */
    private function predictADR(Carbon $date, ?int $roomTypeId, float $projectedOccupancy): float
    {
        // Get base ADR from room type
        $baseAdr = RoomType::where('id', $roomTypeId)
            ->value('base_rate') ?? 100;

        // Adjust based on projected occupancy
        $occupancyMultiplier = match (true) {
            $projectedOccupancy >= 90 => 1.3,
            $projectedOccupancy >= 80 => 1.2,
            $projectedOccupancy >= 70 => 1.1,
            $projectedOccupancy >= 50 => 1.0,
            $projectedOccupancy >= 30 => 0.9,
            default => 0.85,
        };

        // Day of week adjustment
        $dowMultiplier = match ($date->dayOfWeek) {
            5, 6 => 1.15, // Weekend
            0 => 1.1,
            default => 1.0,
        };

        return $baseAdr * $occupancyMultiplier * $dowMultiplier;
    }

    /**
     * Calculate confidence level for forecast
     */
    private function calculateConfidence(Carbon $date, float $historical, float $bookingPace): float
    {
        $daysUntil = now()->diffInDays($date, false);

        // Confidence decreases as we look further out
        $timeConfidence = match (true) {
            $daysUntil < 0 => 1.0,    // Past - 100% certain
            $daysUntil <= 7 => 0.95,  // Next week
            $daysUntil <= 14 => 0.85, // Next 2 weeks
            $daysUntil <= 30 => 0.75, // Next month
            $daysUntil <= 60 => 0.65, // Next 2 months
            $daysUntil <= 90 => 0.55, // Next quarter
            default => 0.45,          // Beyond quarter
        };

        // Confidence based on agreement between methods
        $difference = abs($historical - $bookingPace);
        $agreementConfidence = max(0, 1 - ($difference / 100));

        return round(($timeConfidence * 0.6 + $agreementConfidence * 0.4) * 100, 2);
    }

    /**
     * Get total rooms count
     */
    private function getTotalRooms(?int $roomTypeId = null): int
    {
        if ($roomTypeId) {
            return Room::where('tenant_id', $this->tenantId)
                ->where('room_type_id', $roomTypeId)
                ->where('status', '!=', 'out_of_order')
                ->count();
        }

        return Room::where('tenant_id', $this->tenantId)
            ->where('status', '!=', 'out_of_order')
            ->count();
    }

    /**
     * Analyze forecast accuracy
     */
    public function analyzeAccuracy(Carbon $startDate, Carbon $endDate): array
    {
        $forecasts = OccupancyForecast::where('tenant_id', $this->tenantId)
            ->whereBetween('forecast_date', [$startDate, $endDate])
            ->get();

        $accuracies = [];
        $totalError = 0;
        $count = 0;

        foreach ($forecasts as $forecast) {
            $actual = RevenueSnapshot::where('tenant_id', $this->tenantId)
                ->where('snapshot_date', $forecast->forecast_date)
                ->first();

            if ($actual) {
                $error = abs($forecast->projected_occupancy_rate - $actual->occupancy_rate);
                $accuracies[] = [
                    'date' => $forecast->forecast_date,
                    'predicted' => $forecast->projected_occupancy_rate,
                    'actual' => $actual->occupancy_rate,
                    'error' => $error,
                ];
                $totalError += $error;
                $count++;
            }
        }

        return [
            'forecasts_analyzed' => $count,
            'average_error' => $count > 0 ? round($totalError / $count, 2) : 0,
            'accuracy_percentage' => $count > 0 ? round(100 - ($totalError / $count), 2) : 0,
            'details' => $accuracies,
        ];
    }

    /**
     * Get demand indicators for a date range
     */
    public function getDemandIndicators(Carbon $startDate, Carbon $endDate): array
    {
        $forecasts = $this->generateForecast($startDate, $endDate);

        $highDemand = $forecasts->filter(fn ($f) => $f->projected_occupancy_rate >= 80);
        $lowDemand = $forecasts->filter(fn ($f) => $f->projected_occupancy_rate <= 40);

        return [
            'total_days' => $forecasts->count(),
            'high_demand_days' => $highDemand->count(),
            'low_demand_days' => $lowDemand->count(),
            'average_occupancy' => round($forecasts->avg('projected_occupancy_rate'), 2),
            'peak_date' => $forecasts->sortByDesc('projected_occupancy_rate')->first()?->forecast_date,
            'lowest_date' => $forecasts->sortBy('projected_occupancy_rate')->first()?->forecast_date,
            'recommendations' => $this->generateRecommendations($forecasts),
        ];
    }

    /**
     * Generate recommendations based on forecasts
     */
    private function generateRecommendations(Collection $forecasts): array
    {
        $recommendations = [];

        $highDemandDays = $forecasts->filter(fn ($f) => $f->projected_occupancy_rate >= 85);
        $lowDemandDays = $forecasts->filter(fn ($f) => $f->projected_occupancy_rate <= 40);

        if ($highDemandDays->count() >= 3) {
            $recommendations[] = [
                'type' => 'pricing',
                'priority' => 'high',
                'message' => "{$highDemandDays->count()} high demand days detected. Consider increasing rates.",
                'dates' => $highDemandDays->pluck('forecast_date')->toArray(),
            ];
        }

        if ($lowDemandDays->count() >= 5) {
            $recommendations[] = [
                'type' => 'promotion',
                'priority' => 'medium',
                'message' => "{$lowDemandDays->count()} low demand days detected. Consider promotional campaigns.",
                'dates' => $lowDemandDays->pluck('forecast_date')->toArray(),
            ];
        }

        return $recommendations;
    }
}
