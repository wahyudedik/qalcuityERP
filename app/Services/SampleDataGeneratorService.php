<?php

namespace App\Services;

use App\Models\OnboardingProfile;
use App\Models\SampleDataLog;
use App\Models\SampleDataTemplate;
use App\Models\Tenant;
use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreModulesGenerator;
use App\Services\DemoData\Generators\AgricultureGenerator;
use App\Services\DemoData\Generators\ConstructionGenerator;
use App\Services\DemoData\Generators\HealthcareGenerator;
use App\Services\DemoData\Generators\HotelGenerator;
use App\Services\DemoData\Generators\ManufacturingGenerator;
use App\Services\DemoData\Generators\RestaurantGenerator;
use App\Services\DemoData\Generators\RetailGenerator;
use App\Services\DemoData\Generators\ServicesGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SampleDataGeneratorService
{
    /**
     * Validate that the tenant exists in the database.
     *
     * @throws \RuntimeException if tenant is not found
     */
    private function validateTenant(int $tenantId): void
    {
        if (! Tenant::where('id', $tenantId)->exists()) {
            throw new \RuntimeException("Tenant with ID {$tenantId} not found.");
        }
    }

    /**
     * Check whether demo data has already been generated for this tenant/user.
     */
    private function isAlreadyGenerated(int $tenantId, int $userId): bool
    {
        return OnboardingProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('sample_data_generated', true)
            ->exists();
    }

    /**
     * Resolve the correct industry generator for the given industry slug.
     * Falls back to RetailGenerator for unknown industries.
     */
    protected function resolveGenerator(string $industry): BaseIndustryGenerator
    {
        return match ($industry) {
            'retail' => new RetailGenerator,
            'restaurant' => new RestaurantGenerator,
            'hotel' => new HotelGenerator,
            'construction' => new ConstructionGenerator,
            'agriculture' => new AgricultureGenerator,
            'manufacturing' => new ManufacturingGenerator,
            'services' => new ServicesGenerator,
            'healthcare' => new HealthcareGenerator,
            default => new RetailGenerator,
        };
    }

    /**
     * Generate sample data for the given industry and tenant.
     *
     * Flow:
     *  1. Validate tenant exists
     *  2. Check idempotency (sample_data_generated flag)
     *  3. Create SampleDataLog with status 'processing'
     *  4. Wrap core + industry generation in a DB transaction
     *     a. CoreModulesGenerator::generate() — fatal if it throws
     *     b. IndustryGenerator::generate()    — non-fatal; logs warning on failure
     *  5. On success: update SampleDataLog to 'completed', set sample_data_generated = true
     *  6. On core failure: update SampleDataLog to 'failed', return {success: false}
     */
    public function generateForIndustry(string $industry, int $tenantId, int $userId): array
    {
        // ── 1. Validate tenant ────────────────────────────────────────────────
        try {
            $this->validateTenant($tenantId);
        } catch (\RuntimeException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        // ── 2. Idempotency check ──────────────────────────────────────────────
        if ($this->isAlreadyGenerated($tenantId, $userId)) {
            return [
                'success' => true,
                'records_created' => 0,
                'generated_data' => [],
            ];
        }

        // ── 3. Create Demo_Log with status 'processing' ───────────────────────
        $log = SampleDataLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        // ── 4. Execute inside a DB transaction ────────────────────────────────
        $failedModules = [];
        $coreContext = null;
        $industryResult = [];

        try {
            DB::transaction(function () use (
                $industry, $tenantId, &$coreContext, &$industryResult, &$failedModules
            ) {
                // ── 4a. Core modules (fatal on failure) ───────────────────────
                $coreGenerator = new CoreModulesGenerator;
                $coreContext = $coreGenerator->generate($tenantId);

                // ── 4b. Industry modules (non-fatal on failure) ───────────────
                $industryGenerator = $this->resolveGenerator($industry);

                try {
                    $industryResult = $industryGenerator->generate($coreContext);
                } catch (\Throwable $e) {
                    Log::warning('Industry module generation failed', [
                        'tenant_id' => $tenantId,
                        'industry' => $industry,
                        'error' => $e->getMessage(),
                    ]);
                    $failedModules[] = $industry;
                    $industryResult = [];
                }
            });
        } catch (\Throwable $e) {
            // Core modules failed — transaction was rolled back
            Log::error('Core module generation failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        // ── 5. Transaction succeeded — update log and profile ─────────────────
        $recordsCreated = ($coreContext?->recordsCreated ?? 0)
            + (isset($industryResult['records_created']) ? (int) $industryResult['records_created'] : 0);

        $generatedData = array_merge(
            ['core' => $coreContext ? [
                'products' => count($coreContext->productIds),
                'customers' => count($coreContext->customerIds),
                'suppliers' => count($coreContext->supplierIds),
                'employees' => count($coreContext->employeeIds),
            ] : []],
            ['industry' => $industryResult['generated_data'] ?? $industryResult],
            ['failed_modules' => $failedModules]
        );

        $log->update([
            'status' => 'completed',
            'records_created' => $recordsCreated,
            'generated_data' => $generatedData,
            'completed_at' => now(),
        ]);

        OnboardingProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->update(['sample_data_generated' => true]);

        return [
            'success' => true,
            'records_created' => $recordsCreated,
            'generated_data' => $generatedData,
        ];
    }

    /**
     * Get available templates for the given industry.
     */
    public function getTemplates(string $industry): array
    {
        return SampleDataTemplate::where('industry', $industry)
            ->where('is_active', true)
            ->orderBy('usage_count', 'desc')
            ->get()
            ->toArray();
    }
}
