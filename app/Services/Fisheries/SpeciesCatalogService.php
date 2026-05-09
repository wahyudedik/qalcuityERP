<?php

namespace App\Services\Fisheries;

use App\Models\CatchLog;
use App\Models\FishSpecies;
use App\Models\FreshnessAssessment;
use App\Models\QualityGrade;

class SpeciesCatalogService
{
    /**
     * List all species
     */
    public function listSpecies(int $tenantId, ?string $category = null, ?string $search = null): array
    {
        $query = FishSpecies::where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('common_name', 'like', '%'.$search.'%')
                    ->orWhere('scientific_name', 'like', '%'.$search.'%');
            });
        }

        return $query->orderBy('common_name')->get()->toArray();
    }

    /**
     * Add new species
     */
    public function addSpecies(int $tenantId, array $data): FishSpecies
    {
        return FishSpecies::create([
            'tenant_id' => $tenantId,
            'species_code' => $data['species_code'],
            'common_name' => $data['common_name'],
            'scientific_name' => $data['scientific_name'] ?? null,
            'category' => $data['category'] ?? 'marine',
            'family' => $data['family'] ?? null,
            'avg_weight' => $data['avg_weight'] ?? null,
            'max_weight' => $data['max_weight'] ?? null,
            'market_price_per_kg' => $data['market_price_per_kg'] ?? null,
            'preferred_habitat' => $data['preferred_habitat'] ?? null,
            'characteristics' => $data['characteristics'] ?? [],
            'description' => $data['description'] ?? null,
            'is_endangered' => $data['is_endangered'] ?? false,
        ]);
    }

    /**
     * Manage quality grades
     */
    public function manageGrades(int $tenantId, array $gradesData): array
    {
        $created = [];

        foreach ($gradesData as $gradeData) {
            $grade = QualityGrade::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'grade_code' => $gradeData['grade_code'],
                ],
                [
                    'grade_name' => $gradeData['grade_name'],
                    'description' => $gradeData['description'] ?? null,
                    'price_multiplier' => $gradeData['price_multiplier'] ?? 1.0,
                    'criteria' => $gradeData['criteria'] ?? [],
                    'sort_order' => $gradeData['sort_order'] ?? 0,
                ]
            );

            $created[] = $grade;
        }

        return $created;
    }

    /**
     * Assess freshness of catch
     */
    public function assessFreshness(int $catchLogId, float $overallScore, array $criteria = [], ?int $assessorId = null, string $assessmentType = 'visual'): FreshnessAssessment
    {
        return FreshnessAssessment::create([
            'tenant_id' => CatchLog::findOrFail($catchLogId)->tenant_id,
            'catch_log_id' => $catchLogId,
            'overall_score' => $overallScore,
            'eye_clarity' => $criteria['eye_clarity'] ?? null,
            'gill_color' => $criteria['gill_color'] ?? null,
            'skin_firmness' => $criteria['skin_firmness'] ?? null,
            'odor_score' => $criteria['odor_score'] ?? null,
            'assessed_by_type' => $assessmentType,
            'assessor_id' => $assessorId,
            'assessed_at' => now(),
        ]);
    }

    /**
     * Calculate market value based on species and grade
     */
    public function calculateMarketValue(int $speciesId, float $weight, ?int $gradeId = null): float
    {
        $species = FishSpecies::findOrFail($speciesId);
        $basePrice = $species->market_price_per_kg ?? 0;

        if ($gradeId) {
            $grade = QualityGrade::find($gradeId);
            if ($grade) {
                return $weight * $basePrice * $grade->price_multiplier;
            }
        }

        return $weight * $basePrice;
    }

    /**
     * Get species statistics
     */
    public function getSpeciesStatistics(int $speciesId, ?string $period = null): array
    {
        $species = FishSpecies::findOrFail($speciesId);

        $query = CatchLog::where('species_id', $speciesId);

        if ($period === 'this_month') {
            $query->whereMonth('caught_at', now()->month);
        } elseif ($period === 'this_year') {
            $query->whereYear('caught_at', now()->year);
        }

        $catches = $query->get();

        return [
            'species' => $species,
            'total_catches' => $catches->count(),
            'total_weight_kg' => $catches->sum('total_weight'),
            'total_quantity' => $catches->sum('quantity'),
            'total_value' => $catches->sum('estimated_value'),
            'average_weight_per_fish' => $catches->avg('average_weight'),
            'average_freshness_score' => round($catches->avg('freshness_score') ?? 0, 2),
            'period' => $period ?? 'all_time',
        ];
    }
}
