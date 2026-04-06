<?php

namespace App\Services\Fisheries;

use App\Models\AquaculturePond;
use App\Models\WaterQualityLog;
use App\Models\FeedingSchedule;
use App\Models\MortalityLog;

class AquacultureManagementService
{
    /**
     * Create aquaculture pond
     */
    public function createPond(int $tenantId, array $data): AquaculturePond
    {
        return AquaculturePond::create([
            'tenant_id' => $tenantId,
            'pond_code' => $data['pond_code'],
            'pond_name' => $data['pond_name'],
            'surface_area' => $data['surface_area'],
            'depth' => $data['depth'],
            'volume' => $data['volume'],
            'pond_type' => $data['pond_type'] ?? 'earthen',
            'water_source' => $data['water_source'] ?? 'natural',
            'carrying_capacity' => $data['carrying_capacity'],
            'status' => 'empty',
        ]);
    }

    /**
     * Stock pond with fish
     */
    public function stockPond(int $pondId, int $speciesId, float $quantity, string $stockingDate = null): bool
    {
        try {
            $pond = AquaculturePond::findOrFail($pondId);
            $pond->update([
                'current_species_id' => $speciesId,
                'current_stock' => $quantity,
                'stocking_date' => $stockingDate ?? now(),
                'status' => 'stocked',
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Stock pond failed', ['pond_id' => $pondId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Log water quality parameters
     */
    public function logWaterQuality(int $tenantId, ?int $pondId = null, ?int $fishingZoneId = null, array $parameters = [], ?int $userId = null): WaterQualityLog
    {
        return WaterQualityLog::create([
            'tenant_id' => $tenantId,
            'pond_id' => $pondId,
            'fishing_zone_id' => $fishingZoneId,
            'ph_level' => $parameters['ph_level'] ?? null,
            'dissolved_oxygen' => $parameters['dissolved_oxygen'] ?? null,
            'temperature' => $parameters['temperature'] ?? null,
            'salinity' => $parameters['salinity'] ?? null,
            'ammonia' => $parameters['ammonia'] ?? null,
            'nitrite' => $parameters['nitrite'] ?? null,
            'nitrate' => $parameters['nitrate'] ?? null,
            'turbidity' => $parameters['turbidity'] ?? null,
            'measurement_method' => $parameters['method'] ?? 'manual',
            'measured_by_user_id' => $userId,
            'measured_at' => now(),
        ]);
    }

    /**
     * Check water quality safety
     */
    public function checkWaterQuality(int $logId): array
    {
        $log = WaterQualityLog::findOrFail($logId);

        $issues = [];

        if (!$log->isPhSafe()) {
            $issues[] = "pH level {$log->ph_level} is outside safe range (6.5-9.0)";
        }

        if (!$log->isOxygenAdequate()) {
            $issues[] = "Dissolved oxygen {$log->dissolved_oxygen} mg/L is below minimum (5.0 mg/L)";
        }

        if ($log->ammonia && $log->ammonia > 0.5) {
            $issues[] = "Ammonia level {$log->ammonia} mg/L is too high";
        }

        return [
            'is_safe' => empty($issues),
            'issues' => $issues,
            'parameters' => $log,
        ];
    }

    /**
     * Create feeding schedule
     */
    public function createFeedingSchedule(int $pondId, int $feedProductId, string $scheduleDate, string $feedingTime, float $quantity): FeedingSchedule
    {
        return FeedingSchedule::create([
            'tenant_id' => AquaculturePond::findOrFail($pondId)->tenant_id,
            'pond_id' => $pondId,
            'feed_product_id' => $feedProductId,
            'schedule_date' => $scheduleDate,
            'feeding_time' => $feedingTime,
            'planned_quantity' => $quantity,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Record actual feeding
     */
    public function recordFeeding(int $scheduleId, float $actualQuantity, ?int $userId = null): bool
    {
        try {
            $schedule = FeedingSchedule::findOrFail($scheduleId);
            $schedule->update([
                'actual_quantity' => $actualQuantity,
                'status' => 'completed',
                'fed_by_user_id' => $userId,
                'completed_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Record feeding failed', ['schedule_id' => $scheduleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Record mortality
     */
    public function recordMortality(int $tenantId, ?int $pondId = null, ?int $tripId = null, int $count, float $totalWeight = null, string $cause = null, string $symptoms = null, ?int $userId = null): MortalityLog
    {
        return MortalityLog::create([
            'tenant_id' => $tenantId,
            'pond_id' => $pondId,
            'fishing_trip_id' => $tripId,
            'count' => $count,
            'total_weight' => $totalWeight,
            'cause_of_death' => $cause,
            'symptoms' => $symptoms,
            'reported_by_user_id' => $userId,
            'reported_at' => now(),
        ]);
    }

    /**
     * Calculate Feed Conversion Ratio (FCR)
     */
    public function calculateFCR(int $pondId, string $periodStart, string $periodEnd): array
    {
        $pond = AquaculturePond::findOrFail($pondId);

        // Total feed consumed
        $totalFeed = FeedingSchedule::where('pond_id', $pondId)
            ->whereBetween('schedule_date', [$periodStart, $periodEnd])
            ->where('status', 'completed')
            ->sum('actual_quantity');

        // Weight gain (simplified - should track initial and final weight)
        $currentStock = $pond->current_stock;

        // FCR = Feed Given / Weight Gain
        $fcr = $currentStock > 0 ? $totalFeed / $currentStock : 0;

        return [
            'pond_id' => $pondId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_feed_kg' => $totalFeed,
            'current_stock' => $currentStock,
            'fcr' => round($fcr, 2),
            'fcr_rating' => $this->getFCRRating($fcr),
        ];
    }

    /**
     * Get FCR rating
     */
    protected function getFCRRating(float $fcr): string
    {
        if ($fcr < 1.2) {
            return 'excellent';
        } elseif ($fcr < 1.5) {
            return 'good';
        } elseif ($fcr < 2.0) {
            return 'average';
        }

        return 'poor';
    }

    /**
     * Get pond dashboard data
     */
    public function getPondDashboard(int $pondId): array
    {
        $pond = AquaculturePond::with('currentSpecies')->findOrFail($pondId);

        // Latest water quality
        $latestWaterQuality = WaterQualityLog::where('pond_id', $pondId)
            ->orderBy('measured_at', 'desc')
            ->first();

        // Upcoming feeding schedules
        $upcomingFeedings = FeedingSchedule::where('pond_id', $pondId)
            ->where('status', 'scheduled')
            ->where('schedule_date', '>=', today())
            ->orderBy('schedule_date')
            ->limit(5)
            ->get();

        // Recent mortality
        $recentMortality = MortalityLog::where('pond_id', $pondId)
            ->orderBy('reported_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'pond' => $pond,
            'utilization_percentage' => $pond->utilizationPercentage(),
            'days_to_harvest' => $pond->daysToHarvest(),
            'latest_water_quality' => $latestWaterQuality,
            'water_quality_status' => $latestWaterQuality ? [
                'ph_safe' => $latestWaterQuality->isPhSafe(),
                'oxygen_adequate' => $latestWaterQuality->isOxygenAdequate(),
            ] : null,
            'upcoming_feedings' => $upcomingFeedings,
            'recent_mortality' => $recentMortality,
        ];
    }

    /**
     * Generate growth report
     */
    public function generateGrowthReport(int $pondId, string $periodStart, string $periodEnd): array
    {
        $pond = AquaculturePond::findOrFail($pondId);

        // Average weight over time (from catch/mortality logs)
        $mortalityLogs = MortalityLog::where('pond_id', $pondId)
            ->whereBetween('reported_at', [$periodStart, $periodEnd])
            ->get();

        $averageMortalityWeight = $mortalityLogs->avg('total_weight');

        // Feeding efficiency
        $totalFeed = FeedingSchedule::where('pond_id', $pondId)
            ->whereBetween('schedule_date', [$periodStart, $periodEnd])
            ->where('status', 'completed')
            ->sum('actual_quantity');

        return [
            'pond' => $pond,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'current_stock' => $pond->current_stock,
            'average_mortality_weight' => $averageMortalityWeight,
            'total_feed_consumed' => $totalFeed,
            'growth_rate_estimate' => 'N/A - Requires initial stocking weight data',
        ];
    }
}
