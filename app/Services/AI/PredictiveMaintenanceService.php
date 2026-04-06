<?php

namespace App\Services\AI;

use App\Models\PredictiveMaintenance;
use App\Models\Asset;
use Illuminate\Support\Facades\Log;

class PredictiveMaintenanceService
{
    /**
     * Predict maintenance needs for all assets
     */
    public function predictForAllAssets(int $tenantId): array
    {
        $assets = Asset::where('tenant_id', $tenantId)->get();
        $predictions = [];

        foreach ($assets as $asset) {
            $prediction = $this->predictForAsset($asset);
            if ($prediction) {
                $predictions[] = $prediction;
            }
        }

        return [
            'success' => true,
            'total_assets' => $assets->count(),
            'predictions_generated' => count($predictions),
            'predictions' => $predictions,
        ];
    }

    /**
     * Predict maintenance for specific asset
     */
    public function predictForAsset(Asset $asset): ?array
    {
        try {
            // Calculate failure probability based on multiple factors
            $failureProbability = $this->calculateFailureProbability($asset);

            // Predict maintenance due date
            $maintenanceDueDate = $this->predictMaintenanceDate($asset);

            // Estimate remaining lifespan
            $remainingLifespan = $this->estimateRemainingLifespan($asset);

            // Determine severity
            $severity = $this->determineSeverity($failureProbability, $remainingLifespan);

            // Generate recommendations
            $recommendations = $this->generateRecommendations($asset, $failureProbability, $severity);

            // Save prediction
            $prediction = PredictiveMaintenance::create([
                'tenant_id' => $asset->tenant_id,
                'asset_id' => $asset->id,
                'prediction_type' => 'failure_probability',
                'probability' => $failureProbability,
                'predicted_date' => $maintenanceDueDate,
                'severity' => $severity,
                'contributing_factors' => $this->getContributingFactors($asset),
                'recommendations' => $recommendations,
                'status' => 'pending',
            ]);

            return [
                'prediction_id' => $prediction->id,
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'failure_probability' => round($failureProbability * 100, 2) . '%',
                'predicted_date' => $maintenanceDueDate,
                'severity' => $severity,
                'remaining_lifespan_months' => $remainingLifespan,
                'recommendations' => $recommendations,
            ];

        } catch (\Throwable $e) {
            Log::error('Predictive maintenance failed for asset ' . $asset->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate failure probability using ML-inspired algorithm
     */
    protected function calculateFailureProbability(Asset $asset): float
    {
        $factors = [];

        // Factor 1: Age of asset (older = higher risk)
        $ageInMonths = $asset->purchase_date ? now()->diffInMonths($asset->purchase_date) : 0;
        $expectedLifespan = $asset->expected_lifespan_months ?? 60;
        $ageFactor = min($ageInMonths / $expectedLifespan, 1.0) * 0.3;

        // Factor 2: Usage intensity
        $usageHours = $asset->total_usage_hours ?? 0;
        $maxUsageHours = $asset->max_usage_hours ?? 10000;
        $usageFactor = min($usageHours / $maxUsageHours, 1.0) * 0.25;

        // Factor 3: Maintenance history
        $lastMaintenance = $asset->last_maintenance_date ? now()->diffInDays($asset->last_maintenance_date) : 365;
        $maintenanceInterval = $asset->maintenance_interval_days ?? 90;
        $maintenanceFactor = min($lastMaintenance / $maintenanceInterval, 1.0) * 0.25;

        // Factor 4: Previous failures
        $failureCount = $asset->failure_count ?? 0;
        $failureFactor = min($failureCount / 10, 1.0) * 0.2;

        $probability = $ageFactor + $usageFactor + $maintenanceFactor + $failureFactor;

        return min(max($probability, 0), 1.0); // Clamp between 0 and 1
    }

    /**
     * Predict when maintenance is due
     */
    protected function predictMaintenanceDate(Asset $asset): ?\Carbon\Carbon
    {
        $lastMaintenance = $asset->last_maintenance_date;
        $interval = $asset->maintenance_interval_days ?? 90;

        if ($lastMaintenance) {
            return $lastMaintenance->addDays($interval);
        }

        // If no maintenance history, predict based on age
        $purchaseDate = $asset->purchase_date;
        if ($purchaseDate) {
            return $purchaseDate->addDays($interval);
        }

        return now()->addDays($interval);
    }

    /**
     * Estimate remaining lifespan in months
     */
    protected function estimateRemainingLifespan(Asset $asset): int
    {
        $expectedLifespan = $asset->expected_lifespan_months ?? 60;
        $ageInMonths = $asset->purchase_date ? now()->diffInMonths($asset->purchase_date) : 0;

        $remaining = max($expectedLifespan - $ageInMonths, 0);

        // Adjust based on condition
        $condition = $asset->condition ?? 'good';
        $conditionMultiplier = match ($condition) {
            'excellent' => 1.2,
            'good' => 1.0,
            'fair' => 0.8,
            'poor' => 0.5,
            default => 1.0
        };

        return intval($remaining * $conditionMultiplier);
    }

    /**
     * Determine severity level
     */
    protected function determineSeverity(float $probability, int $remainingLifespan): string
    {
        if ($probability >= 0.8 || $remainingLifespan <= 3) {
            return 'critical';
        } elseif ($probability >= 0.6 || $remainingLifespan <= 6) {
            return 'high';
        } elseif ($probability >= 0.4 || $remainingLifespan <= 12) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Generate maintenance recommendations
     */
    protected function generateRecommendations(Asset $asset, float $probability, string $severity): array
    {
        $recommendations = [];

        if ($severity === 'critical') {
            $recommendations[] = [
                'action' => 'Immediate inspection required',
                'priority' => 'urgent',
                'estimated_cost' => $asset->replacement_cost ?? 0,
                'timeline' => 'Within 24 hours'
            ];
        }

        if ($probability > 0.5) {
            $recommendations[] = [
                'action' => 'Schedule preventive maintenance',
                'priority' => 'high',
                'estimated_cost' => ($asset->replacement_cost ?? 0) * 0.1,
                'timeline' => 'Within 1 week'
            ];
        }

        $recommendations[] = [
            'action' => 'Check lubrication and wear parts',
            'priority' => 'medium',
            'estimated_cost' => 500000,
            'timeline' => 'Within 2 weeks'
        ];

        $recommendations[] = [
            'action' => 'Update maintenance log',
            'priority' => 'low',
            'estimated_cost' => 0,
            'timeline' => 'Within 1 month'
        ];

        return $recommendations;
    }

    /**
     * Get contributing factors for prediction
     */
    protected function getContributingFactors(Asset $asset): array
    {
        return [
            'age_months' => $asset->purchase_date ? now()->diffInMonths($asset->purchase_date) : 0,
            'usage_hours' => $asset->total_usage_hours ?? 0,
            'days_since_maintenance' => $asset->last_maintenance_date ? now()->diffInDays($asset->last_maintenance_date) : null,
            'failure_count' => $asset->failure_count ?? 0,
            'condition' => $asset->condition ?? 'unknown',
        ];
    }

    /**
     * Schedule maintenance from prediction
     */
    public function scheduleMaintenance(int $predictionId, \Carbon\Carbon $scheduledDate, int $userId): bool
    {
        $prediction = PredictiveMaintenance::find($predictionId);

        if (!$prediction) {
            return false;
        }

        $prediction->update([
            'status' => 'scheduled',
            'scheduled_date' => $scheduledDate,
            'scheduled_by_user_id' => $userId,
        ]);

        return true;
    }

    /**
     * Mark prediction as completed
     */
    public function markCompleted(int $predictionId, string $notes = ''): bool
    {
        $prediction = PredictiveMaintenance::find($predictionId);

        if (!$prediction) {
            return false;
        }

        $prediction->update([
            'status' => 'completed',
            'notes' => $notes,
        ]);

        // Update asset's last maintenance date
        $asset = $prediction->asset;
        if ($asset) {
            $asset->update([
                'last_maintenance_date' => now(),
            ]);
        }

        return true;
    }

    /**
     * Get pending predictions
     */
    public function getPendingPredictions(int $tenantId, string $severity = null): array
    {
        $query = PredictiveMaintenance::where('tenant_id', $tenantId)
            ->where('status', 'pending');

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->with('asset')
            ->orderBy('probability', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get maintenance statistics
     */
    public function getMaintenanceStats(int $tenantId): array
    {
        $total = PredictiveMaintenance::where('tenant_id', $tenantId)->count();
        $pending = PredictiveMaintenance::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();
        $scheduled = PredictiveMaintenance::where('tenant_id', $tenantId)
            ->where('status', 'scheduled')
            ->count();
        $completed = PredictiveMaintenance::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->count();

        $bySeverity = PredictiveMaintenance::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        $avgProbability = PredictiveMaintenance::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->avg('probability');

        return [
            'total_predictions' => $total,
            'pending' => $pending,
            'scheduled' => $scheduled,
            'completed' => $completed,
            'by_severity' => $bySeverity,
            'average_probability' => round($avgProbability ?? 0, 4),
            'critical_count' => $bySeverity['critical'] ?? 0,
            'high_risk_count' => ($bySeverity['critical'] ?? 0) + ($bySeverity['high'] ?? 0),
        ];
    }

    /**
     * Dismiss prediction (false positive)
     */
    public function dismissPrediction(int $predictionId, string $reason = ''): bool
    {
        $prediction = PredictiveMaintenance::find($predictionId);

        if (!$prediction) {
            return false;
        }

        $prediction->update([
            'status' => 'dismissed',
            'notes' => $reason,
        ]);

        return true;
    }
}
