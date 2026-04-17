<?php

namespace Tests\Feature\DemoData;

use App\Models\OnboardingProfile;
use App\Services\SampleDataGeneratorService;
use Database\Seeders\SampleDataTemplateSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test — Missing Migrations Audit Fix
 *
 * Task 1: Confirms that all affected generators successfully insert data
 * after the fix migrations are applied.
 *
 * On UNFIXED code: these assertions FAIL (confirms bugs exist).
 * On FIXED code: these assertions PASS (confirms bugs are resolved).
 *
 * Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6
 */
class GeneratorBugConditionExplorationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new SampleDataTemplateSeeder())->run();
    }

    /**
     * Property 1: Bug Condition — HealthcareGenerator::seedDoctors() succeeds.
     *
     * On unfixed code: doctors table has no tenant_id → SQLSTATE[42S22] → generatedData['doctors'] = 0
     * On fixed code: tenant_id column exists → insert succeeds → generatedData['doctors'] > 0
     *
     * Validates: Requirements 2.1, 2.4
     */
    public function test_healthcare_generator_seeds_doctors_successfully(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        OnboardingProfile::create([
            'tenant_id'             => $tenant->id,
            'user_id'               => $user->id,
            'industry'              => 'healthcare',
            'business_size'         => 'small',
            'sample_data_generated' => false,
        ]);

        $service = app(SampleDataGeneratorService::class);
        $result  = $service->generateForIndustry('healthcare', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'generateForIndustry(healthcare) should succeed. Error: ' . ($result['error'] ?? 'none'));

        $industryData = $result['generated_data']['industry'] ?? [];
        $doctorCount  = $industryData['doctors'] ?? 0;

        $this->assertGreaterThan(0, $doctorCount,
            "HealthcareGenerator::seedDoctors() should create >0 doctors. " .
            "Got $doctorCount. This indicates the tenant_id column is still missing from doctors table.");
    }

    /**
     * Property 1: Bug Condition — HealthcareGenerator::seedPatients() succeeds.
     *
     * On unfixed code: patients table has no tenant_id → SQLSTATE[42S22] → generatedData['patients'] = 0
     * On fixed code: tenant_id column exists → insert succeeds → generatedData['patients'] > 0
     *
     * Validates: Requirements 2.2, 2.4
     */
    public function test_healthcare_generator_seeds_patients_successfully(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        OnboardingProfile::create([
            'tenant_id'             => $tenant->id,
            'user_id'               => $user->id,
            'industry'              => 'healthcare',
            'business_size'         => 'small',
            'sample_data_generated' => false,
        ]);

        $service = app(SampleDataGeneratorService::class);
        $result  = $service->generateForIndustry('healthcare', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'generateForIndustry(healthcare) should succeed. Error: ' . ($result['error'] ?? 'none'));

        $industryData  = $result['generated_data']['industry'] ?? [];
        $patientCount  = $industryData['patients'] ?? 0;

        $this->assertGreaterThan(0, $patientCount,
            "HealthcareGenerator::seedPatients() should create >0 patients. " .
            "Got $patientCount. This indicates the tenant_id column is still missing from patients table.");
    }

    /**
     * Property 1: Bug Condition — HotelGenerator::seedHousekeepingTasks() succeeds.
     *
     * On unfixed code: enum mismatch ('regular_cleaning'/'turndown_service' not in enum)
     *   OR missing actual_duration column → exception → generatedData['housekeeping_tasks'] = 0
     * On fixed code: enum expanded + actual_duration added → insert succeeds → count > 0
     *
     * Validates: Requirements 2.5, 2.6
     */
    public function test_hotel_generator_seeds_housekeeping_tasks_successfully(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        OnboardingProfile::create([
            'tenant_id'             => $tenant->id,
            'user_id'               => $user->id,
            'industry'              => 'hotel',
            'business_size'         => 'small',
            'sample_data_generated' => false,
        ]);

        $service = app(SampleDataGeneratorService::class);
        $result  = $service->generateForIndustry('hotel', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'generateForIndustry(hotel) should succeed. Error: ' . ($result['error'] ?? 'none'));

        $industryData         = $result['generated_data']['industry'] ?? [];
        $housekeepingCount    = $industryData['housekeeping_tasks'] ?? 0;

        $this->assertGreaterThan(0, $housekeepingCount,
            "HotelGenerator::seedHousekeepingTasks() should create >0 tasks. " .
            "Got $housekeepingCount. This indicates enum mismatch or missing actual_duration column.");
    }

    /**
     * Property 1: Bug Condition — ManufacturingGenerator::seedQualityChecks() succeeds.
     *
     * On unfixed code: inspector_id is NOT NULL without default, generator doesn't pass it
     *   → SQLSTATE[HY000]: Field 'inspector_id' doesn't have a default value → count = 0
     * On fixed code: inspector_id is nullable → insert succeeds → count > 0
     *
     * Validates: Requirements 2.5, 2.6
     */
    public function test_manufacturing_generator_seeds_quality_checks_successfully(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        OnboardingProfile::create([
            'tenant_id'             => $tenant->id,
            'user_id'               => $user->id,
            'industry'              => 'manufacturing',
            'business_size'         => 'small',
            'sample_data_generated' => false,
        ]);

        $service = app(SampleDataGeneratorService::class);
        $result  = $service->generateForIndustry('manufacturing', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'generateForIndustry(manufacturing) should succeed. Error: ' . ($result['error'] ?? 'none'));

        $industryData       = $result['generated_data']['industry'] ?? [];
        $qualityCheckCount  = $industryData['quality_checks'] ?? 0;

        $this->assertGreaterThan(0, $qualityCheckCount,
            "ManufacturingGenerator::seedQualityChecks() should create >0 quality checks. " .
            "Got $qualityCheckCount. This indicates inspector_id is still NOT NULL without default.");
    }

    /**
     * Property 1: Bug Condition — AgricultureGenerator::seedCropCycles() succeeds.
     *
     * On unfixed code: crop_cycles table has schema from first migration (2026_03_31_000008)
     *   which lacks columns like area_hectares, growth_stage → SQLSTATE[42S22] → count = 0
     * On fixed code: missing columns added → insert succeeds → count > 0
     *
     * Validates: Requirements 2.5, 2.6
     */
    public function test_agriculture_generator_seeds_crop_cycles_successfully(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        OnboardingProfile::create([
            'tenant_id'             => $tenant->id,
            'user_id'               => $user->id,
            'industry'              => 'agriculture',
            'business_size'         => 'small',
            'sample_data_generated' => false,
        ]);

        $service = app(SampleDataGeneratorService::class);
        $result  = $service->generateForIndustry('agriculture', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'generateForIndustry(agriculture) should succeed. Error: ' . ($result['error'] ?? 'none'));

        $industryData    = $result['generated_data']['industry'] ?? [];
        $cropCycleCount  = $industryData['crop_cycles'] ?? 0;

        $this->assertGreaterThan(0, $cropCycleCount,
            "AgricultureGenerator::seedCropCycles() should create >0 crop cycles. " .
            "Got $cropCycleCount. This indicates missing columns (area_hectares, growth_stage, etc.) in crop_cycles table.");
    }

    /**
     * Property 1: Bug Condition — test_property_11 for healthcare runs without markTestSkipped().
     *
     * Verifies that after fix, the healthcare industry generates enough doctors and appointments
     * for the property 11 assertions to pass without being skipped.
     *
     * Validates: Requirements 2.3, 2.4
     */
    public function test_property_11_healthcare_runs_without_skip(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        OnboardingProfile::create([
            'tenant_id'             => $tenant->id,
            'user_id'               => $user->id,
            'industry'              => 'healthcare',
            'business_size'         => 'small',
            'sample_data_generated' => false,
        ]);

        $service = app(SampleDataGeneratorService::class);
        $result  = $service->generateForIndustry('healthcare', $tenant->id, $user->id);

        $this->assertTrue($result['success'],
            'generateForIndustry(healthcare) should succeed.');

        // Verify doctors table has tenant_id column (no longer causes markTestSkipped)
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('doctors', 'tenant_id'),
            'doctors table should have tenant_id column after fix migration.'
        );

        // Verify ≥3 doctors exist for this tenant (property 11 minimum)
        $doctorCount = DB::table('doctors')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->count();

        $this->assertGreaterThanOrEqual(3, $doctorCount,
            "Property 11 (healthcare): expected ≥3 doctors, got $doctorCount.");

        // Verify ≥10 appointments exist for this tenant (property 11 minimum)
        $appointmentCount = DB::table('appointments')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->count();

        $this->assertGreaterThanOrEqual(10, $appointmentCount,
            "Property 11 (healthcare): expected ≥10 appointments, got $appointmentCount.");
    }
}
