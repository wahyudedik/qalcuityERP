<?php

namespace App\Services\Manufacturing;

use App\Models\ConcreteMixDesign;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Mix Design Calculator Service
 *
 * Advanced concrete mix design calculations with SNI standards,
 * cost estimation, volume calculation, and material optimization.
 */
class MixDesignCalculatorService
{
    /**
     * Calculate mix design for specific volume
     */
    public function calculateForVolume(ConcreteMixDesign $mixDesign, float $volumeM3, float $wastePercent = 5): array
    {
        $baseNeeds = $mixDesign->calculateNeeds($volumeM3);

        // Add waste factor
        $wasteMultiplier = 1 + ($wastePercent / 100);

        $adjustedNeeds = [
            'volume_m3' => $volumeM3,
            'waste_percent' => $wastePercent,
            'waste_multiplier' => $wasteMultiplier,
            'grade' => $mixDesign->grade,
            'cement_kg' => round($baseNeeds['cement_kg'] * $wasteMultiplier, 1),
            'cement_sak' => ceil($baseNeeds['cement_kg'] * $wasteMultiplier / 50),
            'water_liter' => round($baseNeeds['water_liter'] * $wasteMultiplier, 1),
            'fine_agg_kg' => round($baseNeeds['fine_agg_kg'] * $wasteMultiplier, 1),
            'fine_agg_m3' => round($baseNeeds['fine_agg_m3'] * $wasteMultiplier, 2),
            'coarse_agg_kg' => round($baseNeeds['coarse_agg_kg'] * $wasteMultiplier, 1),
            'coarse_agg_m3' => round($baseNeeds['coarse_agg_m3'] * $wasteMultiplier, 2),
            'admixture_liter' => round($baseNeeds['admixture_liter'] * $wasteMultiplier, 2),
        ];

        return [
            'base' => $baseNeeds,
            'adjusted' => $adjustedNeeds,
            'mix_design' => $mixDesign,
        ];
    }

    /**
     * Calculate cost analysis for mix design
     */
    public function calculateCostAnalysis(ConcreteMixDesign $mixDesign, float $volumeM3, int $tenantId): array
    {
        $costPerM3 = $mixDesign->estimateCostPerM3($tenantId);

        $totalCost = $costPerM3['total'] * $volumeM3;
        $costPerSac = $totalCost / ($mixDesign->cement_kg * $volumeM3 / 50);

        return [
            'volume_m3' => $volumeM3,
            'grade' => $mixDesign->grade,
            'cost_per_m3' => $costPerM3,
            'total_cost' => round($totalCost, 0),
            'cost_per_sack_cement' => round($costPerSac, 0),
            'breakdown_percent' => $this->calculateCostPercentages($costPerM3),
        ];
    }

    /**
     * Find optimal mix design based on requirements
     */
    public function findOptimalMix(
        int $tenantId,
        float $requiredStrength,
        float $volumeM3,
        ?string $cementType = null,
        ?float $maxBudget = null,
        ?float $requiredSlump = null
    ): ?array {
        $query = ConcreteMixDesign::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('target_strength', '>=', $requiredStrength);

        if ($cementType) {
            $query->where('cement_type', $cementType);
        }

        if ($requiredSlump) {
            $query->where('slump_min', '<=', $requiredSlump)
                ->where('slump_max', '>=', $requiredSlump);
        }

        $candidates = $query->orderBy('target_strength', 'asc')->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // Calculate cost for each and find optimal
        $results = [];
        foreach ($candidates as $mix) {
            $cost = $mix->estimateCostPerM3($tenantId);

            if ($maxBudget && $cost['total'] > $maxBudget) {
                continue;
            }

            $results[] = [
                'mix_design' => $mix,
                'cost_per_m3' => $cost,
                'total_cost' => $cost['total'] * $volumeM3,
                'strength_ratio' => $mix->target_strength / $requiredStrength,
            ];
        }

        if (empty($results)) {
            return null;
        }

        // Sort by cost efficiency (lowest cost per strength unit)
        usort($results, function ($a, $b) {
            $efficiencyA = $a['total_cost'] / $a['mix_design']->target_strength;
            $efficiencyB = $b['total_cost'] / $b['mix_design']->target_strength;

            return $efficiencyA <=> $efficiencyB;
        });

        return $results[0]; // Return most cost-effective
    }

    /**
     * Compare multiple mix designs
     */
    public function compareMixDesigns(int $tenantId, array $mixDesignIds, float $volumeM3): array
    {
        $mixDesigns = ConcreteMixDesign::whereIn('id', $mixDesignIds)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $comparison = [];
        foreach ($mixDesigns as $mix) {
            $cost = $mix->estimateCostPerM3($tenantId);
            $comparison[] = [
                'id' => $mix->id,
                'grade' => $mix->grade,
                'name' => $mix->name,
                'target_strength' => $mix->target_strength,
                'water_cement_ratio' => $mix->water_cement_ratio,
                'cement_kg' => $mix->cement_kg,
                'cost_per_m3' => $cost['total'],
                'total_cost' => $cost['total'] * $volumeM3,
                'cost_breakdown' => $cost,
                'total_weight' => $mix->totalWeightPerM3(),
            ];
        }

        return $comparison;
    }

    /**
     * Calculate material availability check
     */
    public function checkMaterialAvailability(ConcreteMixDesign $mixDesign, float $volumeM3, int $tenantId): array
    {
        $needs = $mixDesign->calculateNeeds($volumeM3);

        // Map materials to product search keywords
        $materialChecks = [
            'cement' => [
                'keywords' => ['semen', 'cement', 'PCC', 'OPC'],
                'required_kg' => $needs['cement_kg'],
                'unit' => 'kg',
            ],
            'fine_aggregate' => [
                'keywords' => ['pasir', 'fine aggregate', 'agregat halus'],
                'required_kg' => $needs['fine_agg_kg'],
                'unit' => 'kg',
            ],
            'coarse_aggregate' => [
                'keywords' => ['kerikil', 'split', 'batu pecah', 'coarse aggregate'],
                'required_kg' => $needs['coarse_agg_kg'],
                'unit' => 'kg',
            ],
            'admixture' => [
                'keywords' => ['admixture', 'additive', 'sikament', 'plasticizer'],
                'required_liter' => $needs['admixture_liter'],
                'unit' => 'liter',
            ],
        ];

        $availability = [];
        $allAvailable = true;

        foreach ($materialChecks as $materialName => $config) {
            $product = Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where(function ($q) use ($config) {
                    foreach ($config['keywords'] as $kw) {
                        $q->orWhere('name', 'like', "%{$kw}%");
                    }
                })
                ->first();

            $stock = $product ? $this->getTotalStock($product->id, $tenantId) : 0;
            $required = $config['required_kg'] ?? ($config['required_liter'] ?? 0);
            $sufficient = $stock >= $required;

            if (! $sufficient) {
                $allAvailable = false;
            }

            $availability[$materialName] = [
                'product' => $product,
                'required' => $required,
                'available' => $stock,
                'shortage' => max(0, $required - $stock),
                'sufficient' => $sufficient,
                'unit' => $config['unit'],
            ];
        }

        return [
            'mix_design' => $mixDesign,
            'volume_m3' => $volumeM3,
            'availability' => $availability,
            'all_available' => $allAvailable,
        ];
    }

    /**
     * Generate mix design recommendation report
     */
    public function generateRecommendationReport(
        int $tenantId,
        float $volumeM3,
        float $requiredStrength,
        ?float $budget = null
    ): array {
        $optimal = $this->findOptimalMix($tenantId, $requiredStrength, $volumeM3, maxBudget: $budget);

        if (! $optimal) {
            return [
                'status' => 'no_match',
                'message' => 'Tidak ada mix design yang sesuai dengan kriteria',
            ];
        }

        $mixDesign = $optimal['mix_design'];
        $costAnalysis = $this->calculateCostAnalysis($mixDesign, $volumeM3, $tenantId);
        $availability = $this->checkMaterialAvailability($mixDesign, $volumeM3, $tenantId);

        return [
            'status' => 'success',
            'recommended_mix' => $mixDesign,
            'volume_m3' => $volumeM3,
            'cost_analysis' => $costAnalysis,
            'material_availability' => $availability,
            'needs_with_waste' => $this->calculateForVolume($mixDesign, $volumeM3, 5),
        ];
    }

    /**
     * Get total stock for a product across all warehouses
     */
    private function getTotalStock(int $productId, int $tenantId): float
    {
        return DB::table('product_stocks')
            ->join('warehouses', 'warehouses.id', '=', 'product_stocks.warehouse_id')
            ->where('product_stocks.product_id', $productId)
            ->where('warehouses.tenant_id', $tenantId)
            ->where('warehouses.is_active', true)
            ->sum('product_stocks.quantity') ?? 0;
    }

    /**
     * Calculate cost breakdown percentages
     */
    private function calculateCostPercentages(array $costPerM3): array
    {
        $total = $costPerM3['total'] ?: 1; // Avoid division by zero

        return [
            'cement' => round(($costPerM3['cement'] / $total) * 100, 1),
            'water' => round(($costPerM3['water'] / $total) * 100, 1),
            'fine_agg' => round(($costPerM3['fine_agg'] / $total) * 100, 1),
            'coarse_agg' => round(($costPerM3['coarse_agg'] / $total) * 100, 1),
            'admixture' => round(($costPerM3['admixture'] / $total) * 100, 1),
        ];
    }
}
