<?php

namespace App\Services\Fisheries;

use App\Models\CatchLog;
use App\Models\FishingTrip;
use App\Models\FishingVessel;
use App\Models\FishingZone;
use App\Models\FishSpecies;
use Illuminate\Support\Str;

class CatchTrackingService
{
    /**
     * Plan a new fishing trip
     */
    public function planTrip(int $tenantId, int $vesselId, int $captainId, ?int $fishingZoneId = null, array $crewIds = [], ?string $departureTime = null): array
    {
        try {
            $vessel = FishingVessel::findOrFail($vesselId);

            // Generate trip number
            $tripNumber = 'FT-'.date('Ymd').'-'.Str::upper(Str::random(5));

            // Create trip
            $trip = FishingTrip::create([
                'tenant_id' => $tenantId,
                'vessel_id' => $vesselId,
                'captain_id' => $captainId,
                'fishing_zone_id' => $fishingZoneId,
                'trip_number' => $tripNumber,
                'departure_time' => $departureTime ?? now(),
                'status' => 'planned',
            ]);

            // Assign crew
            if (! empty($crewIds)) {
                foreach ($crewIds as $crewId) {
                    $trip->crew()->attach($crewId, ['role' => 'crew']);
                }
            }

            return [
                'success' => true,
                'trip' => $trip,
                'trip_number' => $tripNumber,
            ];
        } catch (\Exception $e) {
            \Log::error('Plan trip failed', [
                'vessel_id' => $vesselId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Start fishing trip (mark as departed)
     */
    public function startTrip(int $tripId): bool
    {
        try {
            $trip = FishingTrip::findOrFail($tripId);
            $trip->update([
                'status' => 'departed',
                'departure_time' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Start trip failed', [
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Record catch
     */
    public function recordCatch(int $tripId, int $speciesId, float $quantity, float $totalWeight, ?int $gradeId = null, ?float $freshnessScore = null, ?string $catchMethod = null, ?float $depth = null, ?float $latitude = null, ?float $longitude = null): array
    {
        try {
            $trip = FishingTrip::findOrFail($tripId);
            $species = FishSpecies::findOrFail($speciesId);

            // Calculate average weight
            $averageWeight = $quantity > 0 ? $totalWeight / $quantity : null;

            // Create catch log
            $catch = CatchLog::create([
                'tenant_id' => $trip->tenant_id,
                'fishing_trip_id' => $tripId,
                'species_id' => $speciesId,
                'grade_id' => $gradeId,
                'quantity' => $quantity,
                'total_weight' => $totalWeight,
                'average_weight' => $averageWeight,
                'freshness_score' => $freshnessScore,
                'caught_at' => now(),
                'catch_method' => $catchMethod,
                'depth' => $depth,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            // Update trip total catch weight
            $trip->increment('total_catch_weight', $totalWeight);

            return [
                'success' => true,
                'catch' => $catch,
                'estimated_value' => $catch->estimated_value,
            ];
        } catch (\Exception $e) {
            \Log::error('Record catch failed', [
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update trip GPS position
     */
    public function updatePosition(int $tripId, float $latitude, float $longitude): bool
    {
        try {
            $trip = FishingTrip::findOrFail($tripId);
            $trip->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $trip->status === 'planned' ? 'departed' : $trip->status,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Update position failed', [
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Complete fishing trip
     */
    public function completeTrip(int $tripId, ?float $fuelConsumed = null, ?string $returnTime = null): array
    {
        try {
            $trip = FishingTrip::findOrFail($tripId);
            $trip->update([
                'status' => 'completed',
                'return_time' => $returnTime ?? now(),
                'fuel_consumed' => $fuelConsumed,
            ]);

            // Calculate metrics
            $metrics = $this->calculateTripMetrics($tripId);

            return [
                'success' => true,
                'trip' => $trip,
                'metrics' => $metrics,
            ];
        } catch (\Exception $e) {
            \Log::error('Complete trip failed', [
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate trip metrics
     */
    public function calculateTripMetrics(int $tripId): array
    {
        $trip = FishingTrip::with(['catchLogs.species', 'vessel'])->findOrFail($tripId);

        $totalCatchWeight = $trip->catchLogs->sum('total_weight');
        $totalValue = $trip->catchLogs->sum('estimated_value');
        $duration = $trip->duration();

        // Fuel efficiency (kg per liter)
        $fuelEfficiency = $trip->fuel_consumed > 0
            ? $totalCatchWeight / $trip->fuel_consumed
            : 0;

        // Catch rate (kg per hour)
        $catchRate = $duration > 0
            ? $totalCatchWeight / $duration
            : 0;

        // Species distribution
        $speciesDistribution = $trip->catchLogs->groupBy('species_id')->map(function ($catches) {
            return [
                'species_name' => $catches->first()->species->common_name,
                'total_weight' => $catches->sum('total_weight'),
                'quantity' => $catches->sum('quantity'),
                'value' => $catches->sum('estimated_value'),
            ];
        })->values();

        return [
            'trip_duration_hours' => $duration,
            'total_catch_weight' => $totalCatchWeight,
            'total_estimated_value' => $totalValue,
            'fuel_consumed' => $trip->fuel_consumed,
            'fuel_efficiency_kg_per_liter' => round($fuelEfficiency, 2),
            'catch_rate_kg_per_hour' => round($catchRate, 2),
            'species_distribution' => $speciesDistribution,
            'crew_count' => $trip->crew->count(),
        ];
    }

    /**
     * Get trip summary
     */
    public function getTripSummary(int $tripId): array
    {
        $trip = FishingTrip::with([
            'vessel',
            'captain',
            'fishingZone',
            'crew',
            'catchLogs.species',
            'catchLogs.grade',
        ])->findOrFail($tripId);

        $metrics = $this->calculateTripMetrics($tripId);

        return [
            'trip' => $trip,
            'metrics' => $metrics,
        ];
    }

    /**
     * Track quota usage for fishing zone
     */
    public function trackQuotaUsage(int $zoneId, string $periodStart, string $periodEnd): array
    {
        $zone = FishingZone::findOrFail($zoneId);

        $trips = FishingTrip::where('fishing_zone_id', $zoneId)
            ->whereBetween('departure_time', [$periodStart, $periodEnd])
            ->where('status', 'completed')
            ->with('catchLogs')
            ->get();

        $totalCatchBySpecies = [];
        $totalWeight = 0;

        foreach ($trips as $trip) {
            foreach ($trip->catchLogs as $catch) {
                $speciesId = $catch->species_id;

                if (! isset($totalCatchBySpecies[$speciesId])) {
                    $totalCatchBySpecies[$speciesId] = [
                        'species_id' => $speciesId,
                        'species_name' => $catch->species->common_name,
                        'total_weight' => 0,
                        'quantity' => 0,
                    ];
                }

                $totalCatchBySpecies[$speciesId]['total_weight'] += $catch->total_weight;
                $totalCatchBySpecies[$speciesId]['quantity'] += $catch->quantity;
                $totalWeight += $catch->total_weight;
            }
        }

        $quotaRemaining = $zone->quota_limit ? $zone->quota_limit - $totalWeight : null;
        $quotaUsedPercentage = $zone->quota_limit && $zone->quota_limit > 0
            ? ($totalWeight / $zone->quota_limit) * 100
            : 0;

        return [
            'zone' => $zone,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_trips' => $trips->count(),
            'total_catch_weight' => $totalWeight,
            'quota_limit' => $zone->quota_limit,
            'quota_remaining' => $quotaRemaining,
            'quota_used_percentage' => round($quotaUsedPercentage, 2),
            'catch_by_species' => array_values($totalCatchBySpecies),
        ];
    }

    /**
     * Get catch analytics
     */
    public function getCatchAnalytics(int $tenantId, ?string $period = null): array
    {
        $query = CatchLog::where('tenant_id', $tenantId)
            ->with(['species', 'grade', 'fishingTrip.vessel']);

        if ($period === 'today') {
            $query->whereDate('caught_at', today());
        } elseif ($period === 'this_week') {
            $query->whereBetween('caught_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereMonth('caught_at', now()->month);
        }

        $catches = $query->get();

        $totalWeight = $catches->sum('total_weight');
        $totalValue = $catches->sum('estimated_value');
        $averageFreshness = $catches->avg('freshness_score');

        // Top species by weight
        $topSpecies = $catches->groupBy('species_id')
            ->map(function ($group) {
                return [
                    'species_name' => $group->first()->species->common_name,
                    'total_weight' => $group->sum('total_weight'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total_weight')
            ->take(10)
            ->values();

        return [
            'total_catches' => $catches->count(),
            'total_weight_kg' => $totalWeight,
            'total_estimated_value' => $totalValue,
            'average_freshness_score' => round($averageFreshness ?? 0, 2),
            'top_species' => $topSpecies,
            'period' => $period ?? 'all_time',
        ];
    }
}
