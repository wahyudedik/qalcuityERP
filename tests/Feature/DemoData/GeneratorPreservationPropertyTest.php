<?php

namespace Tests\Feature\DemoData;

use App\Models\OnboardingProfile;
use App\Services\SampleDataGeneratorService;
use Database\Seeders\SampleDataTemplateSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Generator Preservation Property Tests — Missing Migrations Audit Fix
 *
 * Task 2: Confirms that non-affected industries (retail, restaurant, services,
 * construction) continue to work correctly both BEFORE and AFTER fixes are applied.
 *
 * These tests use observation-first methodology: call generateForIndustry() and
 * assert that record counts meet the minimum requirements defined in the spec.
 *
 * On UNFIXED code: these tests PASS (confirms baseline behavior to preserve).
 * On FIXED code:   these tests PASS (confirms no regression was introduced).
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6
 */
class GeneratorPreservationPropertyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new SampleDataTemplateSeeder)->run();
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────

    private function generateForIndustry(string $industry): array
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

        $service = app(SampleDataGeneratorService::class);
        $result = $service->generateForIndustry($industry, $tenant->id, $user->id);

        return [$result, $tenant->id];
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Preservation — RetailGenerator
    // ─────────────────────────────────────────────────────────────

    /**
     * Property 2: Preservation — RetailGenerator produces ≥10 sales_orders.
     *
     * Verifies that RetailGenerator continues to work correctly and produces
     * the expected minimum number of sales orders.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function test_retail_generator_produces_minimum_sales_orders(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('retail');

        $this->assertTrue($result['success'],
            'generateForIndustry(retail) should succeed. Error: '.($result['error'] ?? 'none'));

        $salesOrderCount = DB::table('sales_orders')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();

        $this->assertGreaterThanOrEqual(10, $salesOrderCount,
            'Property 2 (retail): RetailGenerator should produce ≥10 sales_orders. '.
            "Got $salesOrderCount. This indicates a regression in RetailGenerator.");
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Preservation — RestaurantGenerator
    // ─────────────────────────────────────────────────────────────

    /**
     * Property 2: Preservation — RestaurantGenerator produces ≥8 tables.
     *
     * Verifies that RestaurantGenerator continues to create the expected
     * minimum number of restaurant tables.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function test_restaurant_generator_produces_minimum_tables(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('restaurant');

        $this->assertTrue($result['success'],
            'generateForIndustry(restaurant) should succeed. Error: '.($result['error'] ?? 'none'));

        $tableCount = DB::table('restaurant_tables')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();

        $this->assertGreaterThanOrEqual(8, $tableCount,
            'Property 2 (restaurant): RestaurantGenerator should produce ≥8 tables. '.
            "Got $tableCount. This indicates a regression in RestaurantGenerator.");
    }

    /**
     * Property 2: Preservation — RestaurantGenerator produces ≥10 fb_orders.
     *
     * Verifies that RestaurantGenerator continues to create the expected
     * minimum number of F&B orders.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function test_restaurant_generator_produces_minimum_fb_orders(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('restaurant');

        $this->assertTrue($result['success'],
            'generateForIndustry(restaurant) should succeed. Error: '.($result['error'] ?? 'none'));

        $fbOrderCount = DB::table('fb_orders')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();

        $this->assertGreaterThanOrEqual(10, $fbOrderCount,
            'Property 2 (restaurant): RestaurantGenerator should produce ≥10 fb_orders. '.
            "Got $fbOrderCount. This indicates a regression in RestaurantGenerator.");
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Preservation — ServicesGenerator
    // ─────────────────────────────────────────────────────────────

    /**
     * Property 2: Preservation — ServicesGenerator produces ≥5 projects.
     *
     * Verifies that ServicesGenerator continues to create the expected
     * minimum number of projects.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function test_services_generator_produces_minimum_projects(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('services');

        $this->assertTrue($result['success'],
            'generateForIndustry(services) should succeed. Error: '.($result['error'] ?? 'none'));

        $projectCount = DB::table('projects')
            ->where('tenant_id', $tenantId)
            ->count();

        $this->assertGreaterThanOrEqual(5, $projectCount,
            'Property 2 (services): ServicesGenerator should produce ≥5 projects. '.
            "Got $projectCount. This indicates a regression in ServicesGenerator.");
    }

    /**
     * Property 2: Preservation — ServicesGenerator produces ≥5 project_invoices.
     *
     * Verifies that ServicesGenerator continues to create the expected
     * minimum number of project invoices.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function test_services_generator_produces_minimum_project_invoices(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('services');

        $this->assertTrue($result['success'],
            'generateForIndustry(services) should succeed. Error: '.($result['error'] ?? 'none'));

        if (! Schema::hasTable('project_invoices')) {
            $this->markTestSkipped('project_invoices table does not exist — skipping services invoice check.');
        }

        $projectInvoiceCount = DB::table('project_invoices')
            ->where('tenant_id', $tenantId)
            ->count();

        $this->assertGreaterThanOrEqual(5, $projectInvoiceCount,
            'Property 2 (services): ServicesGenerator should produce ≥5 project_invoices. '.
            "Got $projectInvoiceCount. This indicates a regression in ServicesGenerator.");
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Preservation — ConstructionGenerator
    // ─────────────────────────────────────────────────────────────

    /**
     * Property 2: Preservation — ConstructionGenerator produces ≥3 projects.
     *
     * Verifies that ConstructionGenerator continues to create the expected
     * minimum number of projects.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function test_construction_generator_produces_minimum_projects(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('construction');

        $this->assertTrue($result['success'],
            'generateForIndustry(construction) should succeed. Error: '.($result['error'] ?? 'none'));

        $projectCount = DB::table('projects')
            ->where('tenant_id', $tenantId)
            ->count();

        $this->assertGreaterThanOrEqual(3, $projectCount,
            'Property 2 (construction): ConstructionGenerator should produce ≥3 projects. '.
            "Got $projectCount. This indicates a regression in ConstructionGenerator.");
    }

    /**
     * Property 2: Preservation — ConstructionGenerator produces ≥5 purchase_orders.
     *
     * Verifies that ConstructionGenerator continues to create the expected
     * minimum number of purchase orders.
     *
     * Validates: Requirements 3.3, 3.4
     */
    public function test_construction_generator_produces_minimum_purchase_orders(): void
    {
        [$result, $tenantId] = $this->generateForIndustry('construction');

        $this->assertTrue($result['success'],
            'generateForIndustry(construction) should succeed. Error: '.($result['error'] ?? 'none'));

        $poCount = DB::table('purchase_orders')
            ->where('tenant_id', $tenantId)
            ->count();

        $this->assertGreaterThanOrEqual(5, $poCount,
            'Property 2 (construction): ConstructionGenerator should produce ≥5 purchase_orders. '.
            "Got $poCount. This indicates a regression in ConstructionGenerator.");
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: All preserved industries return success=true
    // ─────────────────────────────────────────────────────────────

    /**
     * Property 2: Preservation — All preserved industries complete without error.
     *
     * Validates: Requirements 3.5, 3.6
     */
    public function test_all_preserved_industries_generate_without_error(): void
    {
        $industries = ['retail', 'restaurant', 'services', 'construction'];

        foreach ($industries as $industry) {
            [$result, $tenantId] = $this->generateForIndustry($industry);

            $this->assertTrue($result['success'],
                "Property 2 (preservation): generateForIndustry('$industry') should succeed. ".
                'Error: '.($result['error'] ?? 'none'));

            $this->assertArrayHasKey('records_created', $result,
                "Property 2 (preservation): response for '$industry' missing 'records_created'.");

            $this->assertGreaterThan(0, $result['records_created'],
                "Property 2 (preservation): '$industry' should create >0 records total. ".
                "Got {$result['records_created']}.");
        }
    }
}
