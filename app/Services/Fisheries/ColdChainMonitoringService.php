<?php

namespace App\Services\Fisheries;

use App\Models\ColdStorageUnit;
use App\Models\TemperatureLog;
use App\Models\ColdChainAlert;
use App\Models\RefrigeratedTransport;

class ColdChainMonitoringService
{
    /**
     * Monitor temperature and check thresholds
     */
    public function monitorTemperature(int $storageUnitId, float $temperature, ?float $humidity = null, string $sensorId = null): array
    {
        try {
            $unit = ColdStorageUnit::findOrFail($storageUnitId);

            // Update current temperature
            $unit->update(['current_temperature' => $temperature]);

            // Log temperature
            $log = TemperatureLog::create([
                'tenant_id' => $unit->tenant_id,
                'cold_storage_unit_id' => $unit->id,
                'temperature' => $temperature,
                'humidity' => $humidity,
                'sensor_id' => $sensorId ?? $unit->sensor_id,
                'recorded_by' => $sensorId ? 'auto' : 'manual',
                'recorded_at' => now(),
            ]);

            // Check thresholds
            $alerts = $this->checkThresholds($unit, $temperature, $log->id);

            return [
                'success' => true,
                'temperature' => $temperature,
                'is_safe' => $unit->isTemperatureSafe(),
                'alerts_triggered' => count($alerts),
                'log_id' => $log->id,
            ];
        } catch (\Exception $e) {
            \Log::error('Monitor temperature failed', [
                'storage_unit_id' => $storageUnitId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check temperature thresholds and trigger alerts
     */
    protected function checkThresholds(ColdStorageUnit $unit, float $temperature, int $logId): array
    {
        $alerts = [];

        if ($temperature < $unit->min_temperature || $temperature > $unit->max_temperature) {
            $severity = $this->calculateSeverity($temperature, $unit->min_temperature, $unit->max_temperature);

            $alert = ColdChainAlert::create([
                'tenant_id' => $unit->tenant_id,
                'cold_storage_unit_id' => $unit->id,
                'temperature_log_id' => $logId,
                'alert_type' => 'threshold_breach',
                'severity' => $severity,
                'message' => "Temperature {$temperature}°C is outside safe range ({$unit->min_temperature}°C - {$unit->max_temperature}°C)",
                'recorded_temperature' => $temperature,
                'threshold_min' => $unit->min_temperature,
                'threshold_max' => $unit->max_temperature,
            ]);

            $alerts[] = $alert;

            // TODO: Send notification (SMS, email, push)
        }

        return $alerts;
    }

    /**
     * Calculate alert severity based on deviation
     */
    protected function calculateSeverity(float $temperature, float $min, float $max): string
    {
        $deviation = max(abs($temperature - $min), abs($temperature - $max));

        if ($deviation > 5) {
            return 'emergency';
        } elseif ($deviation > 3) {
            return 'critical';
        }

        return 'warning';
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(int $alertId, int $userId): bool
    {
        try {
            $alert = ColdChainAlert::findOrFail($alertId);
            $alert->update([
                'is_acknowledged' => true,
                'acknowledged_by_user_id' => $userId,
                'acknowledged_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Acknowledge alert failed', [
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Resolve alert
     */
    public function resolveAlert(int $alertId, string $resolutionNotes): bool
    {
        try {
            $alert = ColdChainAlert::findOrFail($alertId);
            $alert->update([
                'resolution_notes' => $resolutionNotes,
                'resolved_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Resolve alert failed', [
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get active (unacknowledged) alerts
     */
    public function getActiveAlerts(int $tenantId, ?string $severity = null): array
    {
        $query = ColdChainAlert::where('tenant_id', $tenantId)
            ->where('is_acknowledged', false)
            ->with(['coldStorageUnit', 'temperatureLog']);

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Get temperature history
     */
    public function getTemperatureHistory(int $storageUnitId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = TemperatureLog::where('cold_storage_unit_id', $storageUnitId)
            ->with('coldStorageUnit');

        if ($startDate) {
            $query->where('recorded_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('recorded_at', '<=', $endDate);
        }

        return $query->orderBy('recorded_at', 'desc')->get()->toArray();
    }

    /**
     * Generate compliance report
     */
    public function generateComplianceReport(int $tenantId, string $periodStart, string $periodEnd): array
    {
        $units = ColdStorageUnit::where('tenant_id', $tenantId)->get();

        $report = [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_units' => $units->count(),
            'compliant_units' => 0,
            'non_compliant_units' => 0,
            'total_alerts' => 0,
            'resolved_alerts' => 0,
            'unit_details' => [],
        ];

        foreach ($units as $unit) {
            $alerts = ColdChainAlert::where('cold_storage_unit_id', $unit->id)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->get();

            $isCompliant = $alerts->where('severity', '!=', 'emergency')->count() === 0;

            $report['unit_details'][] = [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
                'is_compliant' => $isCompliant,
                'alert_count' => $alerts->count(),
                'critical_alerts' => $alerts->where('severity', 'critical')->count(),
                'emergency_alerts' => $alerts->where('severity', 'emergency')->count(),
            ];

            if ($isCompliant) {
                $report['compliant_units']++;
            } else {
                $report['non_compliant_units']++;
            }

            $report['total_alerts'] += $alerts->count();
            $report['resolved_alerts'] += $alerts->whereNotNull('resolved_at')->count();
        }

        return $report;
    }
}
