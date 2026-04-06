<?php

namespace App\Services;

use App\Models\IrrigationSchedule;
use App\Models\IrrigationLog;
use App\Models\CropCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IrrigationAutomationService
{
    /**
     * Generate smart irrigation schedule
     */
    public function generateSchedule(array $data, int $tenantId): IrrigationSchedule
    {
        $cropType = $data['crop_type'] ?? 'rice';
        $areaHectares = $data['area_hectares'] ?? 1;
        $growthStage = $data['growth_stage'] ?? 'vegetative';
        $soilType = $data['soil_type'] ?? 'clay';
        $irrigationMethod = $data['irrigation_method'] ?? 'sprinkler';

        // Calculate water needs based on crop and stage
        $waterNeeds = $this->calculateWaterNeeds($cropType, $growthStage, $soilType);

        // Determine frequency
        $frequency = $this->determineFrequency($cropType, $growthStage);

        // Calculate duration
        $duration = $this->calculateDuration($waterNeeds, $irrigationMethod);

        return IrrigationSchedule::create([
            'tenant_id' => $tenantId,
            'crop_cycle_id' => $data['crop_cycle_id'] ?? null,
            'zone_name' => $data['zone_name'] ?? 'Zone 1',
            'schedule_type' => 'automatic',
            'irrigation_time' => $data['irrigation_time'] ?? '06:00:00',
            'duration_minutes' => $duration,
            'frequency' => $frequency,
            'water_volume_liters' => $waterNeeds,
            'irrigation_method' => $irrigationMethod,
            'is_active' => true,
            'weather_adjusted' => false,
            'next_irrigation_at' => $this->calculateNextIrrigation($data['irrigation_time'] ?? '06:00:00'),
            'notes' => "Auto-generated for {$cropType} ({$growthStage})",
        ]);
    }

    /**
     * Adjust schedule based on weather
     */
    public function adjustForWeather(int $scheduleId, array $weatherData): void
    {
        $schedule = IrrigationSchedule::findOrFail($scheduleId);

        $rainfall = $weatherData['rainfall'] ?? 0;
        $temperature = $weatherData['temperature'] ?? 25;
        $humidity = $weatherData['humidity'] ?? 70;

        // Reduce irrigation if raining
        if ($rainfall > 10) {
            $reduction = min(50, $rainfall); // Up to 50% reduction
            $newDuration = max(10, $schedule->duration_minutes * (1 - $reduction / 100));

            $schedule->update([
                'duration_minutes' => round($newDuration),
                'weather_adjusted' => true,
                'notes' => "Adjusted for rain: {$rainfall}mm",
            ]);
        }

        // Increase irrigation in hot weather
        if ($temperature > 35 && $humidity < 50) {
            $increase = 20; // 20% increase
            $newDuration = $schedule->duration_minutes * (1 + $increase / 100);

            $schedule->update([
                'duration_minutes' => round($newDuration),
                'weather_adjusted' => true,
                'notes' => "Adjusted for heat: {$temperature}°C",
            ]);
        }
    }

    /**
     * Record irrigation event
     */
    public function recordIrrigation(int $scheduleId, int $duration, float $waterUsed): void
    {
        $schedule = IrrigationSchedule::findOrFail($scheduleId);

        // Update schedule
        $schedule->recordIrrigation($duration, $waterUsed);

        // Create log
        IrrigationLog::create([
            'tenant_id' => $schedule->tenant_id,
            'irrigation_schedule_id' => $scheduleId,
            'irrigated_at' => now(),
            'actual_duration_minutes' => $duration,
            'actual_water_used_liters' => $waterUsed,
            'status' => 'completed',
        ]);
    }

    /**
     * Get upcoming irrigations
     */
    public function getUpcoming(int $tenantId, int $days = 7): array
    {
        return IrrigationSchedule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('next_irrigation_at', '<=', now()->addDays($days))
            ->orderBy('next_irrigation_at')
            ->with('cropCycle')
            ->get()
            ->toArray();
    }

    /**
     * Get water usage statistics
     */
    public function getWaterUsageStats(int $tenantId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $totalWater = IrrigationLog::where('tenant_id', $tenantId)
            ->where('irrigated_at', '>=', $startDate)
            ->sum('actual_water_used_liters');

        $totalIrrigations = IrrigationLog::where('tenant_id', $tenantId)
            ->where('irrigated_at', '>=', $startDate)
            ->count();

        $avgWaterPerIrrigation = $totalIrrigations > 0 ? $totalWater / $totalIrrigations : 0;

        return [
            'total_water_liters' => round($totalWater, 2),
            'total_irrigations' => $totalIrrigations,
            'avg_water_per_irrigation' => round($avgWaterPerIrrigation, 2),
            'period_days' => $days,
        ];
    }

    /**
     * Calculate water needs based on crop type and growth stage
     */
    protected function calculateWaterNeeds(string $cropType, string $growthStage, string $soilType): float
    {
        // Base water needs per hectare per day (liters)
        $baseNeeds = match ($cropType) {
            'rice' => 8000,
            'corn' => 6000,
            'tomato' => 5000,
            'wheat' => 4500,
            default => 5000
        };

        // Growth stage multiplier
        $stageMultiplier = match ($growthStage) {
            'planted' => 0.5,
            'vegetative' => 1.0,
            'flowering' => 1.3,
            'fruiting' => 1.2,
            'ready_to_harvest' => 0.7,
            default => 1.0
        };

        // Soil type adjustment
        $soilAdjustment = match ($soilType) {
            'sand' => 1.2, // Sandy soil drains faster
            'clay' => 0.9, // Clay retains water
            'loam' => 1.0,
            default => 1.0
        };

        return $baseNeeds * $stageMultiplier * $soilAdjustment;
    }

    /**
     * Determine irrigation frequency
     */
    protected function determineFrequency(string $cropType, string $growthStage): string
    {
        if (in_array($growthStage, ['flowering', 'fruiting'])) {
            return 'daily';
        }

        return match ($cropType) {
            'rice' => 'daily',
            'tomato' => 'daily',
            'corn' => 'custom',
            default => 'daily'
        };
    }

    /**
     * Calculate irrigation duration in minutes
     */
    protected function calculateDuration(float $waterNeeds, string $method): int
    {
        // Flow rate in liters per minute per method
        $flowRate = match ($method) {
            'sprinkler' => 100,
            'drip' => 50,
            'flood' => 200,
            default => 100
        };

        return max(10, round($waterNeeds / $flowRate));
    }

    /**
     * Calculate next irrigation time
     */
    protected function calculateNextIrrigation(string $time): Carbon
    {
        return Carbon::now()->addDay()->setTimeFromTimeString($time);
    }
}
