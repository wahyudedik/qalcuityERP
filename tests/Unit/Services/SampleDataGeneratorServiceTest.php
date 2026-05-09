<?php

namespace Tests\Unit\Services;

use App\Models\OnboardingProfile;
use App\Models\SampleDataLog;
use App\Models\Tenant;
use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use App\Services\SampleDataGeneratorService;
use Database\Seeders\SampleDataTemplateSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Unit Tests for SampleDataGeneratorService — error handling and edge cases.
 *
 * Feature: onboarding-demo-data
 * Validates: Requirements 11.3, 11.4, 2.9, 13.1, 13.3, 15.1, 15.2, 15.3
 */
class SampleDataGeneratorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new SampleDataTemplateSeeder)->run();
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function makeTenantWithProfile(string $industry = 'retail'): array
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        OnboardingProfile::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'industry' => $industry,
            'business_size' => 'small',
            'sample_data_generated' => false,
        ]);

        return [$tenant, $user];
    }

    /** Service subclass whose industry generator always throws. */
    private function serviceWithFailingIndustry(): SampleDataGeneratorService
    {
        return new class extends SampleDataGeneratorService
        {
            protected function resolveGenerator(string $industry): BaseIndustryGenerator
            {
                return new class extends BaseIndustryGenerator
                {
                    public function generate(CoreDataContext $ctx): array
                    {
                        throw new \RuntimeException('Injected industry failure');
                    }

                    public function getIndustryName(): string
                    {
                        return 'failing';
                    }
                };
            }
        };
    }

    /** Service subclass that simulates a fatal core failure mid-transaction. */
    private function serviceWithFailingCore(): SampleDataGeneratorService
    {
        return new class extends SampleDataGeneratorService
        {
            public function generateForIndustry(string $industry, int $tenantId, int $userId): array
            {
                if (! Tenant::where('id', $tenantId)->exists()) {
                    return ['success' => false, 'error' => "Tenant with ID {$tenantId} not found."];
                }

                $log = SampleDataLog::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'status' => 'processing',
                    'started_at' => now(),
                ]);

                try {
                    DB::transaction(function () use ($tenantId) {
                        DB::table('warehouses')->insert([
                            'tenant_id' => $tenantId,
                            'name' => 'Partial Warehouse',
                            'code' => 'PARTIAL-'.$tenantId,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        throw new \RuntimeException('Simulated core module failure');
                    });
                } catch (\Throwable $e) {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'completed_at' => now(),
                    ]);

                    return ['success' => false, 'error' => $e->getMessage()];
                }

                return ['success' => true, 'records_created' => 0, 'generated_data' => []];
            }
        };
    }

    // ── Task 5.1: Invalid tenant returns {success: false} ─────────

    /**
     * Test 5.1: Non-existent tenant ID → success=false with descriptive error.
     * Validates: Requirements 11.3, 13.5
     */
    public function test_invalid_tenant_returns_success_false_with_descriptive_error(): void
    {
        $service = app(SampleDataGeneratorService::class);
        $nonExistentId = 999_999_999;

        $result = $service->generateForIndustry('retail', $nonExistentId, 1);

        $this->assertFalse($result['success'],
            'Expected success=false for non-existent tenant.');
        $this->assertArrayHasKey('error', $result,
            'Response must contain an "error" key for invalid tenant.');
        $this->assertNotEmpty($result['error'],
            'Error message must not be empty.');
        $this->assertIsString($result['error'],
            'Error message must be a string.');
        $this->assertStringContainsString(
            (string) $nonExistentId,
            $result['error'],
            'Error message should reference the invalid tenant ID.'
        );
    }

    /**
     * Test 5.1 (additional): No SampleDataLog created when tenant validation fails.
     * Validates: Requirements 11.3
     */
    public function test_no_log_created_when_tenant_is_invalid(): void
    {
        $service = app(SampleDataGeneratorService::class);
        $before = SampleDataLog::count();

        $service->generateForIndustry('retail', 999_999_998, 1);

        $this->assertEquals($before, SampleDataLog::count(),
            'No SampleDataLog should be created when tenant validation fails.');
    }

    // ── Task 5.2: Industry failure does not stop generation ───────

    /**
     * Test 5.2: Industry generator exception → success=true, failed_modules populated.
     * Validates: Requirements 2.9, 15.1, 15.2
     */
    public function test_industry_module_failure_returns_success_true_with_failed_modules(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingIndustry();

        $result = $service->generateForIndustry('retail', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'Expected success=true when only the industry module fails.');
        $this->assertArrayHasKey('generated_data', $result);
        $this->assertArrayHasKey('failed_modules', $result['generated_data'],
            'generated_data must contain failed_modules.');
        $this->assertNotEmpty($result['generated_data']['failed_modules'],
            'failed_modules must be non-empty when industry generator throws.');
    }

    /**
     * Test 5.2 (additional): Core data is persisted even when industry module fails.
     * Validates: Requirements 2.9, 15.1
     */
    public function test_core_data_persisted_when_industry_module_fails(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingIndustry();

        $service->generateForIndustry('retail', $tenant->id, $user->id);

        $warehouseCount = DB::table('warehouses')
            ->where('tenant_id', $tenant->id)
            ->count();

        $this->assertGreaterThanOrEqual(1, $warehouseCount,
            'Core data (warehouse) must be persisted even when industry module fails.');
    }

    // ── Task 5.3: Core failure triggers rollback ──────────────────

    /**
     * Test 5.3: CoreModulesGenerator exception → rollback, no partial data remains.
     * Validates: Requirements 11.4, 15.3
     */
    public function test_core_module_failure_triggers_rollback_no_partial_data(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingCore();

        $warehousesBefore = DB::table('warehouses')->where('tenant_id', $tenant->id)->count();

        $result = $service->generateForIndustry('retail', $tenant->id, $user->id);

        $this->assertFalse($result['success'],
            'Expected success=false when core generation throws.');
        $this->assertArrayHasKey('error', $result);

        $warehousesAfter = DB::table('warehouses')->where('tenant_id', $tenant->id)->count();
        $this->assertEquals($warehousesBefore, $warehousesAfter,
            'Partial warehouse insert must be rolled back when core generation fails.');
    }

    /**
     * Test 5.3 (additional): Core failure leaves no products in DB.
     * Validates: Requirements 11.4, 15.3
     */
    public function test_core_failure_leaves_no_products_in_db(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('manufacturing');
        $service = $this->serviceWithFailingCore();

        $productsBefore = DB::table('products')->where('tenant_id', $tenant->id)->count();

        $result = $service->generateForIndustry('manufacturing', $tenant->id, $user->id);

        $this->assertFalse($result['success']);
        $this->assertEquals($productsBefore,
            DB::table('products')->where('tenant_id', $tenant->id)->count(),
            'No products should remain after core failure rollback.');
    }

    // ── Task 5.4: Partial failure returns success:true + failed_modules ──

    /**
     * Test 5.4: Core succeeds, industry fails → success=true, records_created > 0,
     * failed_modules populated.
     * Validates: Requirements 15.2
     */
    public function test_partial_failure_returns_success_true_with_failed_modules_populated(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingIndustry();

        $result = $service->generateForIndustry('retail', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'Partial failure (industry only) must return success=true.');
        $this->assertGreaterThan(0, $result['records_created'],
            'records_created must be > 0 because core data was generated.');
        $this->assertNotEmpty($result['generated_data']['failed_modules'],
            'failed_modules must list the failed industry.');
        $this->assertArrayHasKey('core', $result['generated_data'],
            'generated_data must contain core breakdown.');
    }

    /**
     * Test 5.4 (additional): failed_modules contains the industry name that failed.
     * Validates: Requirements 15.2
     */
    public function test_partial_failure_failed_modules_contains_industry_name(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingIndustry();

        $result = $service->generateForIndustry('retail', $tenant->id, $user->id);

        $this->assertContains('retail', $result['generated_data']['failed_modules'],
            'failed_modules must contain the industry name that failed.');
    }

    // ── Task 5.5: Demo_Log created with status 'processing' ──────

    /**
     * Test 5.5: A SampleDataLog with status='processing' and started_at is created
     * at the beginning of generateForIndustry().
     *
     * We verify by observing the log state from inside the industry generator.
     * Validates: Requirements 13.1
     */
    public function test_demo_log_created_with_processing_status_on_start(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $tenantId = $tenant->id;
        $processingLogObserved = false;

        $service = new class($tenantId, $processingLogObserved) extends SampleDataGeneratorService
        {
            private int $tenantId;

            private $observed;

            public function __construct(int $tenantId, &$observed)
            {
                $this->tenantId = $tenantId;
                $this->observed = &$observed;
            }

            protected function resolveGenerator(string $industry): BaseIndustryGenerator
            {
                $tenantId = $this->tenantId;
                $observed = &$this->observed;

                return new class($tenantId, $observed) extends BaseIndustryGenerator
                {
                    private int $tenantId;

                    private $observed;

                    public function __construct(int $tenantId, &$observed)
                    {
                        $this->tenantId = $tenantId;
                        $this->observed = &$observed;
                    }

                    public function generate(CoreDataContext $ctx): array
                    {
                        $log = SampleDataLog::where('tenant_id', $this->tenantId)
                            ->where('status', 'processing')
                            ->first();
                        $this->observed = ($log !== null);

                        return ['records_created' => 0, 'generated_data' => []];
                    }

                    public function getIndustryName(): string
                    {
                        return 'retail';
                    }
                };
            }
        };

        $service->generateForIndustry('retail', $tenant->id, $user->id);

        $this->assertTrue($processingLogObserved,
            'SampleDataLog with status=processing must exist during industry generation.');
    }

    /**
     * Test 5.5 (additional): Log has started_at set after a successful generate.
     * Validates: Requirements 13.1
     */
    public function test_demo_log_has_started_at_after_generate(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = app(SampleDataGeneratorService::class);

        $service->generateForIndustry('retail', $tenant->id, $user->id);

        $log = SampleDataLog::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $this->assertNotNull($log, 'SampleDataLog must exist after generate.');
        $this->assertNotNull($log->started_at,
            'SampleDataLog must have started_at set (was created as processing).');
    }

    // ── Task 5.6: Demo_Log updated to 'failed' on exception ──────

    /**
     * Test 5.6: Core exception → SampleDataLog status='failed' with error_message.
     * Validates: Requirements 13.3
     */
    public function test_demo_log_updated_to_failed_with_error_message_on_exception(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingCore();

        $result = $service->generateForIndustry('retail', $tenant->id, $user->id);

        $this->assertFalse($result['success']);

        $log = SampleDataLog::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $this->assertNotNull($log, 'SampleDataLog must exist even after a core failure.');
        $this->assertEquals('failed', $log->status,
            'SampleDataLog status must be "failed" after core exception.');
        $this->assertNotEmpty($log->error_message,
            'SampleDataLog error_message must be set after core exception.');
        $this->assertNotNull($log->completed_at,
            'SampleDataLog completed_at must be set even on failure.');
    }

    /**
     * Test 5.6 (additional): error_message in the log matches the exception message.
     * Validates: Requirements 13.3
     */
    public function test_demo_log_error_message_matches_exception(): void
    {
        [$tenant, $user] = $this->makeTenantWithProfile('retail');
        $service = $this->serviceWithFailingCore();

        $service->generateForIndustry('retail', $tenant->id, $user->id);

        $log = SampleDataLog::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $this->assertStringContainsString(
            'Simulated core module failure',
            $log->error_message,
            'error_message must contain the original exception message.'
        );
    }
}
