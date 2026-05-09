<?php

namespace App\Services;

use App\Models\CosmeticFormula;
use App\Models\FormulaIngredient;
use App\Models\FormulaVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cosmetic Formula Service
 *
 * TASK-2.24: Centralized service for formula management
 * TASK-2.25: Formula versioning
 * TASK-2.26: Approval workflow
 */
class CosmeticFormulaService
{
    /**
     * Create a new cosmetic formula with ingredients
     *
     * @param  array  $data  Formula data
     * @param  array  $ingredients  Array of ingredient data
     */
    public function createFormula(array $data, array $ingredients = []): CosmeticFormula
    {
        return DB::transaction(function () use ($data, $ingredients) {
            // Generate formula code
            $data['formula_code'] = CosmeticFormula::getNextFormulaCode();
            $data['status'] = 'draft';
            $data['created_by'] = Auth::id();
            $data['tenant_id'] = Auth::user()->tenant_id;

            // Create formula
            $formula = CosmeticFormula::create($data);

            // Add ingredients if provided
            if (! empty($ingredients)) {
                $this->addIngredients($formula, $ingredients);
            }

            Log::info('Cosmetic formula created', [
                'formula_id' => $formula->id,
                'formula_code' => $formula->formula_code,
                'user_id' => Auth::id(),
            ]);

            return $formula;
        });
    }

    /**
     * Add ingredients to a formula
     */
    public function addIngredients(CosmeticFormula $formula, array $ingredients): void
    {
        foreach ($ingredients as $index => $ingredientData) {
            FormulaIngredient::create([
                'tenant_id' => $formula->tenant_id,
                'formula_id' => $formula->id,
                'inci_name' => $ingredientData['inci_name'],
                'common_name' => $ingredientData['common_name'] ?? null,
                'cas_number' => $ingredientData['cas_number'] ?? null,
                'product_id' => $ingredientData['product_id'] ?? null,
                'quantity' => $ingredientData['quantity'],
                'unit' => $ingredientData['unit'],
                'percentage' => $ingredientData['percentage'] ?? null,
                'function' => $ingredientData['function'] ?? null,
                'phase' => $ingredientData['phase'] ?? null,
                'sort_order' => $ingredientData['sort_order'] ?? ($index + 1),
            ]);
        }

        // Recalculate total cost
        $formula->calculateTotalCost();
    }

    /**
     * Update formula ingredients
     */
    public function updateIngredients(CosmeticFormula $formula, array $ingredients): void
    {
        DB::transaction(function () use ($formula, $ingredients) {
            // Delete existing ingredients
            $formula->ingredients()->delete();

            // Add new ingredients
            $this->addIngredients($formula, $ingredients);

            Log::info('Formula ingredients updated', [
                'formula_id' => $formula->id,
                'ingredient_count' => count($ingredients),
            ]);
        });
    }

    /**
     * Calculate ingredient percentages based on quantities
     */
    public function calculatePercentages(CosmeticFormula $formula): array
    {
        $totalQuantity = $formula->ingredients()->sum('quantity');

        if ($totalQuantity == 0) {
            return [];
        }

        $percentages = [];
        foreach ($formula->ingredients as $ingredient) {
            $percentage = round(($ingredient->quantity / $totalQuantity) * 100, 2);
            $percentages[$ingredient->id] = $percentage;
        }

        return $percentages;
    }

    /**
     * Scale formula to new batch size
     *
     * @return array Scaled ingredients
     */
    public function scaleFormula(CosmeticFormula $formula, float $newBatchSize, string $newUnit = 'grams'): array
    {
        $currentBatchSize = $formula->batch_size;

        if ($currentBatchSize == 0) {
            throw new \InvalidArgumentException('Current batch size cannot be zero');
        }

        $scaleFactor = $newBatchSize / $currentBatchSize;
        $scaledIngredients = [];

        foreach ($formula->ingredients as $ingredient) {
            $scaledIngredients[] = [
                'ingredient_id' => $ingredient->id,
                'inci_name' => $ingredient->inci_name,
                'common_name' => $ingredient->common_name,
                'original_quantity' => $ingredient->quantity,
                'scaled_quantity' => round($ingredient->quantity * $scaleFactor, 2),
                'unit' => $newUnit,
                'percentage' => $ingredient->percentage,
                'function' => $ingredient->function,
                'phase' => $ingredient->phase,
            ];
        }

        return [
            'original_batch_size' => $currentBatchSize,
            'original_unit' => $formula->batch_unit,
            'new_batch_size' => $newBatchSize,
            'new_unit' => $newUnit,
            'scale_factor' => round($scaleFactor, 4),
            'ingredients' => $scaledIngredients,
        ];
    }

    /**
     * TASK-2.25: Create formula version
     */
    public function createVersion(
        CosmeticFormula $formula,
        array $changes,
        string $reason,
        bool $major = false
    ): FormulaVersion {
        return DB::transaction(function () use ($formula, $changes, $reason, $major) {
            // Get current version or start with v1.0
            $latestVersion = $formula->versions()->first();
            $currentVersion = $latestVersion ? $latestVersion->version_number : 'v1.0';

            // Generate next version number
            $nextVersion = FormulaVersion::getNextVersion($currentVersion, $major);

            // Create version record
            $version = FormulaVersion::create([
                'tenant_id' => $formula->tenant_id,
                'formula_id' => $formula->id,
                'version_number' => $nextVersion,
                'changes_summary' => json_encode($changes),
                'reason_for_change' => $reason,
                'changed_by' => Auth::id(),
            ]);

            Log::info('Formula version created', [
                'formula_id' => $formula->id,
                'version' => $nextVersion,
                'major' => $major,
                'user_id' => Auth::id(),
            ]);

            return $version;
        });
    }

    /**
     * TASK-2.25: Compare two formula versions
     */
    public function compareVersions(CosmeticFormula $formula, string $version1, string $version2): array
    {
        $v1 = $formula->versions()->where('version_number', $version1)->first();
        $v2 = $formula->versions()->where('version_number', $version2)->first();

        if (! $v1 || ! $v2) {
            throw new \InvalidArgumentException('One or both versions not found');
        }

        // Get formula state at each version (snapshot would be ideal, but using current for now)
        $changes = [
            'version_1' => [
                'number' => $v1->version_number,
                'created_at' => $v1->created_at,
                'changes' => json_decode($v1->changes_summary, true),
            ],
            'version_2' => [
                'number' => $v2->version_number,
                'created_at' => $v2->created_at,
                'changes' => json_decode($v2->changes_summary, true),
            ],
        ];

        return $changes;
    }

    /**
     * TASK-2.26: Submit formula for approval
     */
    public function submitForApproval(CosmeticFormula $formula): CosmeticFormula
    {
        if (! $formula->isDraft()) {
            throw new \InvalidArgumentException('Only draft formulas can be submitted for approval');
        }

        // Validation checks
        if ($formula->ingredients()->count() === 0) {
            throw new \InvalidArgumentException('Formula must have at least one ingredient');
        }

        if (! $formula->target_ph) {
            throw new \InvalidArgumentException('Target pH must be specified');
        }

        $formula->update([
            'status' => 'testing',
        ]);

        Log::info('Formula submitted for approval', [
            'formula_id' => $formula->id,
            'user_id' => Auth::id(),
        ]);

        return $formula;
    }

    /**
     * TASK-2.26: Approve formula
     */
    public function approveFormula(CosmeticFormula $formula, string $notes = ''): CosmeticFormula
    {
        if (! $formula->isTesting()) {
            throw new \InvalidArgumentException('Only formulas in testing status can be approved');
        }

        return DB::transaction(function () use ($formula, $notes) {
            $formula->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Create initial version upon approval
            if ($formula->versions()->count() === 0) {
                $this->createVersion(
                    $formula,
                    ['Initial approved version'],
                    $notes ?: 'Initial approval',
                    true
                );
            }

            Log::info('Formula approved', [
                'formula_id' => $formula->id,
                'approved_by' => Auth::id(),
                'notes' => $notes,
            ]);

            return $formula;
        });
    }

    /**
     * TASK-2.26: Reject formula
     */
    public function rejectFormula(CosmeticFormula $formula, string $reason): CosmeticFormula
    {
        if (! $formula->isTesting()) {
            throw new \InvalidArgumentException('Only formulas in testing status can be rejected');
        }

        $formula->update([
            'status' => 'draft',
        ]);

        Log::warning('Formula rejected', [
            'formula_id' => $formula->id,
            'rejected_by' => Auth::id(),
            'reason' => $reason,
        ]);

        return $formula;
    }

    /**
     * TASK-2.26: Move formula to production
     */
    public function moveToProduction(CosmeticFormula $formula): CosmeticFormula
    {
        if (! $formula->isApproved()) {
            throw new \InvalidArgumentException('Only approved formulas can be moved to production');
        }

        if (! $formula->isReadyForProduction()) {
            $missing = [];

            if ($formula->ingredients()->count() === 0) {
                $missing[] = 'ingredients';
            }

            if (! $formula->actual_ph) {
                $missing[] = 'actual pH measurement';
            }

            $hasStabilityTest = $formula->stabilityTests()
                ->where('overall_result', 'Pass')
                ->exists();

            if (! $hasStabilityTest) {
                $missing[] = 'passing stability test';
            }

            throw new \InvalidArgumentException('Formula not ready for production. Missing: '.implode(', ', $missing));
        }

        $formula->update([
            'status' => 'production',
        ]);

        Log::info('Formula moved to production', [
            'formula_id' => $formula->id,
            'user_id' => Auth::id(),
        ]);

        return $formula;
    }

    /**
     * TASK-2.26: Discontinue formula
     */
    public function discontinueFormula(CosmeticFormula $formula, string $reason): CosmeticFormula
    {
        if ($formula->isDiscontinued()) {
            throw new \InvalidArgumentException('Formula is already discontinued');
        }

        $formula->update([
            'status' => 'discontinued',
            'notes' => ($formula->notes ? $formula->notes."\n\n" : '').
                'Discontinued on '.now()->format('Y-m-d').' by '.Auth::user()->name."\nReason: ".$reason,
        ]);

        Log::warning('Formula discontinued', [
            'formula_id' => $formula->id,
            'reason' => $reason,
            'user_id' => Auth::id(),
        ]);

        return $formula;
    }

    /**
     * Validate formula ingredient percentages sum to 100%
     */
    public function validatePercentages(CosmeticFormula $formula): array
    {
        $totalPercentage = $formula->ingredients()->sum('percentage');

        return [
            'total_percentage' => round($totalPercentage, 2),
            'is_valid' => abs($totalPercentage - 100.0) < 0.01,
            'difference' => round(100.0 - $totalPercentage, 2),
        ];
    }

    /**
     * Get formula statistics
     */
    public function getFormulaStatistics(int $tenantId): array
    {
        return [
            'total_formulas' => CosmeticFormula::where('tenant_id', $tenantId)->count(),
            'draft' => CosmeticFormula::where('tenant_id', $tenantId)->where('status', 'draft')->count(),
            'testing' => CosmeticFormula::where('tenant_id', $tenantId)->where('status', 'testing')->count(),
            'approved' => CosmeticFormula::where('tenant_id', $tenantId)->where('status', 'approved')->count(),
            'production' => CosmeticFormula::where('tenant_id', $tenantId)->where('status', 'production')->count(),
            'discontinued' => CosmeticFormula::where('tenant_id', $tenantId)->where('status', 'discontinued')->count(),
        ];
    }

    /**
     * Check ingredient restrictions
     */
    public function checkIngredientRestrictions(CosmeticFormula $formula): array
    {
        $violations = [];

        foreach ($formula->ingredients as $ingredient) {
            if (! $ingredient->product_id) {
                continue;
            }

            $restrictions = $ingredient->product->restrictions ?? [];

            foreach ($restrictions as $restriction) {
                if ($restriction->isExceeded($ingredient->percentage ?? 0)) {
                    $violations[] = [
                        'ingredient' => $ingredient->inci_name,
                        'restriction' => $restriction->restriction_type,
                        'limit' => $restriction->max_percentage,
                        'actual' => $ingredient->percentage,
                        'severity' => $restriction->severity,
                    ];
                }
            }
        }

        return [
            'has_violations' => ! empty($violations),
            'violations' => $violations,
            'violation_count' => count($violations),
        ];
    }
}
