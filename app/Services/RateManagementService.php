<?php

namespace App\Services;

use App\Models\RoomRate;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RateManagementService — Handles room rate calculations and management.
 *
 * Rate priority: dynamic > promo > seasonal > weekend (if matching day) > standard.
 * Falls back to room_type.base_rate if no active rate found.
 */
class RateManagementService
{
    /**
     * Rate type priorities (higher number = higher priority)
     */
    private const RATE_PRIORITY = [
        'dynamic' => 5,
        'promo' => 4,
        'seasonal' => 3,
        'weekend' => 2,
        'standard' => 1,
    ];

    /**
     * Get the effective rate for a room type on a specific date.
     * Priority: dynamic > promo > seasonal > weekend (if matching day) > standard.
     * Falls back to room_type.base_rate if no active rate found.
     *
     * @param int $roomTypeId
     * @param string $date Format Y-m-d
     * @param int $tenantId
     * @return float
     */
    public function getEffectiveRate(int $roomTypeId, string $date, int $tenantId): float
    {
        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = $dateCarbon->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Get all active rates for this room type that apply to this date
        $rates = RoomRate::where('tenant_id', $tenantId)
            ->where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->get();

        // Filter by day of week if applicable
        $applicableRates = $rates->filter(function ($rate) use ($dayOfWeek) {
            // If no day_of_week restriction, it applies to all days
            if (empty($rate->day_of_week)) {
                return true;
            }
            return in_array($dayOfWeek, $rate->day_of_week);
        });

        // Sort by priority (highest first)
        $sortedRates = $applicableRates->sortByDesc(function ($rate) {
            return self::RATE_PRIORITY[$rate->rate_type] ?? 0;
        });

        // Return the highest priority rate amount
        if ($sortedRates->isNotEmpty()) {
            $topRate = $sortedRates->first();

            // If it's a dynamic rate, calculate based on occupancy
            if ($topRate->rate_type === 'dynamic') {
                return $this->calculateDynamicRate($roomTypeId, $date, $tenantId, $topRate->amount);
            }

            return (float) $topRate->amount;
        }

        // Fallback to room type base rate
        $roomType = RoomType::find($roomTypeId);
        return $roomType ? (float) $roomType->base_rate : 0.0;
    }

    /**
     * Bulk update rates — create/update multiple rate entries.
     *
     * @param array $rates Array of rate data
     * @return array ['created' => int, 'updated' => int, 'errors' => array]
     */
    public function bulkUpdateRates(array $rates): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rates as $rateData) {
                try {
                    // Validate required fields
                    if (empty($rateData['room_type_id']) || empty($rateData['tenant_id'])) {
                        $errors[] = ['data' => $rateData, 'error' => 'Missing required fields: room_type_id or tenant_id'];
                        continue;
                    }

                    // Check if rate exists (by room_type_id, rate_type, and date range)
                    $existingRate = RoomRate::where('tenant_id', $rateData['tenant_id'])
                        ->where('room_type_id', $rateData['room_type_id'])
                        ->where('rate_type', $rateData['rate_type'] ?? 'standard')
                        ->where('start_date', $rateData['start_date'] ?? null)
                        ->where('end_date', $rateData['end_date'] ?? null)
                        ->first();

                    if ($existingRate) {
                        $existingRate->update($rateData);
                        $updated++;
                    } else {
                        RoomRate::create($rateData);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors[] = ['data' => $rateData, 'error' => $e->getMessage()];
                    Log::warning('RateManagementService: Failed to process rate', [
                        'data' => $rateData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RateManagementService: Bulk update failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Calculate dynamic rate based on occupancy.
     * If occupancy > 80%: base * 1.3, > 60%: base * 1.15, > 40%: base * 1.0, else base * 0.9
     * Only applies if a 'dynamic' rate type exists and is active.
     *
     * @param int $roomTypeId
     * @param string $date
     * @param int $tenantId
     * @param float|null $baseAmount The dynamic rate's base amount (optional)
     * @return float
     */
    public function calculateDynamicRate(int $roomTypeId, string $date, int $tenantId, ?float $baseAmount = null): float
    {
        // Get the dynamic rate base amount if not provided
        if ($baseAmount === null) {
            $dynamicRate = RoomRate::where('tenant_id', $tenantId)
                ->where('room_type_id', $roomTypeId)
                ->where('rate_type', 'dynamic')
                ->where('is_active', true)
                ->first();

            if (!$dynamicRate) {
                // No dynamic rate configured, return standard rate
                return $this->getEffectiveRate($roomTypeId, $date, $tenantId);
            }

            $baseAmount = (float) $dynamicRate->amount;
        }

        // Calculate occupancy for this room type on this date
        $occupancyRate = $this->calculateOccupancyRate($roomTypeId, $date, $tenantId);

        // Apply multiplier based on occupancy
        if ($occupancyRate > 80) {
            return round($baseAmount * 1.3, 2);
        } elseif ($occupancyRate > 60) {
            return round($baseAmount * 1.15, 2);
        } elseif ($occupancyRate > 40) {
            return round($baseAmount, 2);
        } else {
            return round($baseAmount * 0.9, 2);
        }
    }

    /**
     * Get all seasonal rates for a room type (for calendar display).
     *
     * @param int $roomTypeId
     * @return Collection
     */
    public function getSeasonalRates(int $roomTypeId): Collection
    {
        return RoomRate::where('room_type_id', $roomTypeId)
            ->whereIn('rate_type', ['seasonal', 'promo', 'weekend', 'dynamic'])
            ->where('is_active', true)
            ->orderBy('start_date')
            ->orderBy('rate_type')
            ->get();
    }

    /**
     * Get all rates for a room type (including standard).
     *
     * @param int $roomTypeId
     * @return Collection
     */
    public function getAllRates(int $roomTypeId): Collection
    {
        return RoomRate::where('room_type_id', $roomTypeId)
            ->orderBy('rate_type')
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Calculate occupancy rate for a room type on a specific date.
     *
     * @param int $roomTypeId
     * @param string $date
     * @param int $tenantId
     * @return float Percentage (0-100)
     */
    private function calculateOccupancyRate(int $roomTypeId, string $date, int $tenantId): float
    {
        // Get total rooms of this type
        $totalRooms = \App\Models\Room::where('room_type_id', $roomTypeId)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        if ($totalRooms === 0) {
            return 0.0;
        }

        // Count occupied rooms
        $dateCarbon = Carbon::parse($date);
        $occupiedRooms = \App\Models\Reservation::where('tenant_id', $tenantId)
            ->where('room_type_id', $roomTypeId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in_date', '<=', $dateCarbon)
            ->where('check_out_date', '>', $dateCarbon)
            ->count();

        return round(($occupiedRooms / $totalRooms) * 100, 2);
    }

    /**
     * Get rates breakdown for a date range.
     *
     * @param int $roomTypeId
     * @param string $startDate
     * @param string $endDate
     * @param int $tenantId
     * @return array
     */
    public function getRatesForDateRange(int $roomTypeId, string $startDate, string $endDate, int $tenantId): array
    {
        $rates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lt($end)) {
            $dateStr = $current->toDateString();
            $rates[$dateStr] = $this->getEffectiveRate($roomTypeId, $dateStr, $tenantId);
            $current->addDay();
        }

        return $rates;
    }
}
