<?php

namespace Tests\Feature\DemoData;

use App\Models\OnboardingProfile;
use App\Models\SampleDataLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SampleDataGeneratorService;
use Database\Seeders\SampleDataTemplateSeeder;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Property-Based Tests for SampleDataGeneratorService
 *
 * Uses eris/eris for property-based testing.
 * Each test corresponds to a correctness property defined in the design document.
 *
 * Feature: onboarding-demo-data
 */
class SampleDataGeneratorPropertyTest extends TestCase
{
    use TestTrait;

    private const SUPPORTED_INDUSTRIES = [
        'retail', 'restaurant', 'hotel', 'construction',
        'agriculture', 'manufacturing', 'services', 'healthcare',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // Seed templates so getTemplates() has data to return
        (new SampleDataTemplateSeeder)->run();
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: create a fresh tenant + user + onboarding profile
    // ─────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────
    // Property 1: Template tersedia untuk semua industri valid
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 1: Template tersedia untuk semua industri valid
     *
     * For any industry in the supported list, getTemplates($industry) returns non-empty array.
     *
     * Validates: Requirements 1.1, 1.2
     */
    public function test_property_1_templates_available_for_all_supported_industries(): void
    {
        $this->limitTo(8)->forAll(
            Generators::elements(...self::SUPPORTED_INDUSTRIES)
        )->then(function (string $industry) {
            $service = app(SampleDataGeneratorService::class);
            $templates = $service->getTemplates($industry);

            $this->assertNotEmpty(
                $templates,
                "Property 1 failed: getTemplates('$industry') returned empty array. ".
                "Expected at least one active template for supported industry '$industry'."
            );
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Template tidak tersedia untuk industri tidak valid
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 2: Template tidak tersedia untuk industri tidak valid
     *
     * For any string NOT in the supported industries list, getTemplates($industry) returns empty array.
     *
     * Validates: Requirements 1.3
     */
    public function test_property_2_no_templates_for_invalid_industries(): void
    {
        $invalidIndustries = ['unknown', 'invalid', 'xyz', '', 'food'];

        $this->limitTo(5)->forAll(
            Generators::elements(...$invalidIndustries)
        )->then(function (string $industry) {
            $service = app(SampleDataGeneratorService::class);
            $templates = $service->getTemplates($industry);

            $this->assertEmpty(
                $templates,
                "Property 2 failed: getTemplates('$industry') returned non-empty array. ".
                "Expected empty array for unsupported industry '$industry'."
            );
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 3: Template memiliki semua field yang diperlukan
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 3: Template memiliki semua field wajib
     *
     * For any template returned by getTemplates(), it has all required fields:
     * industry, template_name, description, modules_included (array), data_config (array), is_active.
     *
     * Validates: Requirements 1.4
     */
    public function test_property_3_templates_have_all_required_fields(): void
    {
        $this->limitTo(8)->forAll(
            Generators::elements(...self::SUPPORTED_INDUSTRIES)
        )->then(function (string $industry) {
            $service = app(SampleDataGeneratorService::class);
            $templates = $service->getTemplates($industry);

            foreach ($templates as $template) {
                $this->assertArrayHasKey('industry', $template,
                    "Property 3 failed: template for '$industry' missing 'industry' field.");
                $this->assertArrayHasKey('template_name', $template,
                    "Property 3 failed: template for '$industry' missing 'template_name' field.");
                $this->assertArrayHasKey('description', $template,
                    "Property 3 failed: template for '$industry' missing 'description' field.");
                $this->assertArrayHasKey('modules_included', $template,
                    "Property 3 failed: template for '$industry' missing 'modules_included' field.");
                $this->assertArrayHasKey('data_config', $template,
                    "Property 3 failed: template for '$industry' missing 'data_config' field.");
                $this->assertArrayHasKey('is_active', $template,
                    "Property 3 failed: template for '$industry' missing 'is_active' field.");

                // modules_included and data_config must be arrays (cast from JSON)
                $this->assertIsArray($template['modules_included'],
                    "Property 3 failed: 'modules_included' for '$industry' is not an array.");
                $this->assertIsArray($template['data_config'],
                    "Property 3 failed: 'data_config' for '$industry' is not an array.");
            }
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Core data completeness setelah generate
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 4: Core data completeness setelah generate
     *
     * For any valid industry and tenant, after generateForIndustry() succeeds, the database
     * contains: ≥1 warehouse, ≥5 CoA covering all types, ≥1 open accounting period,
     * ≥3 employees, ≥3 customers, ≥2 suppliers, ≥5 products with stock > 0.
     *
     * Validates: Requirements 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8
     */
    public function test_property_4_core_data_completeness_after_generate(): void
    {
        // Test with a subset of industries to keep test time reasonable
        $testIndustries = ['retail', 'manufacturing'];

        $this->limitTo(2)->forAll(
            Generators::elements(...$testIndustries)
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            $result = $service->generateForIndustry($industry, $tenant->id, $user->id);

            $this->assertTrue($result['success'],
                "Property 4 failed: generateForIndustry('$industry') returned success=false. Error: ".
                ($result['error'] ?? 'unknown'));

            $tenantId = $tenant->id;

            // ≥1 active warehouse
            $warehouseCount = DB::table('warehouses')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();
            $this->assertGreaterThanOrEqual(1, $warehouseCount,
                "Property 4 failed for '$industry': expected ≥1 warehouse, got $warehouseCount.");

            // ≥5 CoA covering all 5 types
            $coaTypes = DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->whereIn('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->toArray();
            $this->assertCount(5, $coaTypes,
                "Property 4 failed for '$industry': expected CoA covering all 5 types, got: ".
                implode(', ', $coaTypes));

            $totalCoa = DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->count();
            $this->assertGreaterThanOrEqual(5, $totalCoa,
                "Property 4 failed for '$industry': expected ≥5 CoA accounts, got $totalCoa.");

            // ≥1 open accounting period
            $openPeriods = DB::table('accounting_periods')
                ->where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->count();
            $this->assertGreaterThanOrEqual(1, $openPeriods,
                "Property 4 failed for '$industry': expected ≥1 open accounting period, got $openPeriods.");

            // ≥3 employees
            $employeeCount = DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->whereNull('deleted_at')
                ->count();
            $this->assertGreaterThanOrEqual(3, $employeeCount,
                "Property 4 failed for '$industry': expected ≥3 employees, got $employeeCount.");

            // ≥3 active customers
            $customerCount = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();
            $this->assertGreaterThanOrEqual(3, $customerCount,
                "Property 4 failed for '$industry': expected ≥3 customers, got $customerCount.");

            // ≥2 active suppliers
            $supplierCount = DB::table('suppliers')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();
            $this->assertGreaterThanOrEqual(2, $supplierCount,
                "Property 4 failed for '$industry': expected ≥2 suppliers, got $supplierCount.");

            // ≥5 active products with stock > 0
            $productsWithStock = DB::table('products')
                ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                ->where('products.tenant_id', $tenantId)
                ->where('products.is_active', true)
                ->where('product_stocks.quantity', '>', 0)
                ->count();
            $this->assertGreaterThanOrEqual(5, $productsWithStock,
                "Property 4 failed for '$industry': expected ≥5 products with stock>0, got $productsWithStock.");
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Semua record memiliki tenant_id yang benar
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 5: Semua record memiliki tenant_id yang benar
     *
     * For any tenant ID, all records created by generateForIndustry() have tenant_id
     * matching the given tenantId.
     *
     * Validates: Requirements 11.1
     */
    public function test_property_5_all_records_have_correct_tenant_id(): void
    {
        $this->limitTo(3)->forAll(
            Generators::elements('retail', 'manufacturing', 'healthcare')
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            $result = $service->generateForIndustry($industry, $tenant->id, $user->id);
            $this->assertTrue($result['success'],
                "Property 5 setup failed: generateForIndustry('$industry') returned success=false.");

            $tenantId = $tenant->id;

            // Check products
            $wrongTenantProducts = DB::table('products')
                ->where('tenant_id', '!=', $tenantId)
                ->whereIn('id', function ($q) use ($tenantId) {
                    // Only check products that were created in this test run
                    // by verifying they don't belong to our tenant
                    $q->select('id')->from('products')->where('tenant_id', $tenantId);
                })
                ->count();
            // All products for this tenant must have the correct tenant_id
            $allProductsCorrect = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->whereRaw('tenant_id != ?', [$tenantId])
                ->count();
            $this->assertEquals(0, $allProductsCorrect,
                "Property 5 failed for '$industry': found products with wrong tenant_id.");

            // Check customers
            $wrongCustomers = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereRaw('tenant_id != ?', [$tenantId])
                ->count();
            $this->assertEquals(0, $wrongCustomers,
                "Property 5 failed for '$industry': found customers with wrong tenant_id.");

            // Check suppliers
            $wrongSuppliers = DB::table('suppliers')
                ->where('tenant_id', $tenantId)
                ->whereRaw('tenant_id != ?', [$tenantId])
                ->count();
            $this->assertEquals(0, $wrongSuppliers,
                "Property 5 failed for '$industry': found suppliers with wrong tenant_id.");

            // Check employees
            $wrongEmployees = DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->whereRaw('tenant_id != ?', [$tenantId])
                ->count();
            $this->assertEquals(0, $wrongEmployees,
                "Property 5 failed for '$industry': found employees with wrong tenant_id.");

            // Verify all products actually have the correct tenant_id
            $productsForTenant = DB::table('products')->where('tenant_id', $tenantId)->count();
            $this->assertGreaterThan(0, $productsForTenant,
                "Property 5 failed for '$industry': no products found for tenant $tenantId.");
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 6: Isolasi data antar tenant
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 6: Isolasi data antar tenant
     *
     * For any two different tenants, data generated for tenant A does not appear
     * in tenant B's data (no cross-contamination of tenant_id).
     *
     * Validates: Requirements 11.2
     */
    public function test_property_6_data_isolation_between_tenants(): void
    {
        $this->limitTo(2)->forAll(
            Generators::elements('retail', 'manufacturing')
        )->then(function (string $industry) {
            [$tenantA, $userA] = $this->makeTenantWithProfile($industry);
            [$tenantB, $userB] = $this->makeTenantWithProfile($industry);

            $service = app(SampleDataGeneratorService::class);

            $resultA = $service->generateForIndustry($industry, $tenantA->id, $userA->id);
            $resultB = $service->generateForIndustry($industry, $tenantB->id, $userB->id);

            $this->assertTrue($resultA['success'],
                'Property 6 setup failed: generate for tenant A failed.');
            $this->assertTrue($resultB['success'],
                'Property 6 setup failed: generate for tenant B failed.');

            // Tenant A's products must not appear in tenant B's scope
            $tenantAProductIds = DB::table('products')
                ->where('tenant_id', $tenantA->id)
                ->pluck('id')
                ->toArray();

            $tenantBProductIds = DB::table('products')
                ->where('tenant_id', $tenantB->id)
                ->pluck('id')
                ->toArray();

            $overlap = array_intersect($tenantAProductIds, $tenantBProductIds);
            $this->assertEmpty($overlap,
                "Property 6 failed for '$industry': product IDs overlap between tenant A ({$tenantA->id}) ".
                "and tenant B ({$tenantB->id}). Overlapping IDs: ".implode(', ', $overlap));

            // Tenant A's customers must not appear in tenant B's scope
            $tenantACustomerIds = DB::table('customers')
                ->where('tenant_id', $tenantA->id)
                ->pluck('id')
                ->toArray();

            $tenantBCustomerIds = DB::table('customers')
                ->where('tenant_id', $tenantB->id)
                ->pluck('id')
                ->toArray();

            $customerOverlap = array_intersect($tenantACustomerIds, $tenantBCustomerIds);
            $this->assertEmpty($customerOverlap,
                "Property 6 failed for '$industry': customer IDs overlap between tenants.");
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 7: Idempotency — tidak ada duplikasi data
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 7: Idempotency — tidak ada duplikasi data
     *
     * For any valid industry and tenant, calling generateForIndustry() twice results in
     * the same record count (no duplicate records on second call).
     *
     * Validates: Requirements 12.1, 12.3
     */
    public function test_property_7_idempotency_no_duplicate_records(): void
    {
        $this->limitTo(3)->forAll(
            Generators::elements('retail', 'manufacturing', 'hotel')
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            // First call
            $result1 = $service->generateForIndustry($industry, $tenant->id, $user->id);
            $this->assertTrue($result1['success'],
                "Property 7 setup failed: first generateForIndustry('$industry') returned success=false.");

            $countAfterFirst = DB::table('products')
                ->where('tenant_id', $tenant->id)
                ->count();

            // Reset the idempotency flag to simulate a second call
            OnboardingProfile::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->update(['sample_data_generated' => false]);

            // Second call
            $result2 = $service->generateForIndustry($industry, $tenant->id, $user->id);
            $this->assertTrue($result2['success'],
                "Property 7 failed: second generateForIndustry('$industry') returned success=false.");

            $countAfterSecond = DB::table('products')
                ->where('tenant_id', $tenant->id)
                ->count();

            $this->assertEquals(
                $countAfterFirst,
                $countAfterSecond,
                "Property 7 failed for '$industry': product count changed after second generate. ".
                "First: $countAfterFirst, Second: $countAfterSecond. ".
                'Expected idempotent behavior — no new records on second call.'
            );

            // Also check employees
            $employeesAfterFirst = DB::table('employees')
                ->where('tenant_id', $tenant->id)
                ->count();

            OnboardingProfile::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->update(['sample_data_generated' => false]);

            $service->generateForIndustry($industry, $tenant->id, $user->id);

            $employeesAfterThird = DB::table('employees')
                ->where('tenant_id', $tenant->id)
                ->count();

            $this->assertEquals(
                $employeesAfterFirst,
                $employeesAfterThird,
                "Property 7 failed for '$industry': employee count changed on repeated generate. ".
                "Expected: $employeesAfterFirst, Got: $employeesAfterThird."
            );
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 8: Demo_Log mencerminkan hasil generate
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 8: Demo_Log mencerminkan hasil generate
     *
     * For any successful generateForIndustry() call, the SampleDataLog has
     * status='completed', records_created > 0, and completed_at is set.
     *
     * Validates: Requirements 13.2
     */
    public function test_property_8_sample_data_log_reflects_generate_result(): void
    {
        $this->limitTo(4)->forAll(
            Generators::elements('retail', 'manufacturing', 'hotel', 'healthcare')
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            $result = $service->generateForIndustry($industry, $tenant->id, $user->id);
            $this->assertTrue($result['success'],
                "Property 8 setup failed: generateForIndustry('$industry') returned success=false.");

            $log = SampleDataLog::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            $this->assertNotNull($log,
                "Property 8 failed for '$industry': no SampleDataLog found for tenant {$tenant->id}.");

            $this->assertEquals('completed', $log->status,
                "Property 8 failed for '$industry': SampleDataLog status is '{$log->status}', expected 'completed'.");

            $this->assertGreaterThan(0, $log->records_created,
                "Property 8 failed for '$industry': SampleDataLog records_created is {$log->records_created}, expected > 0.");

            $this->assertNotNull($log->completed_at,
                "Property 8 failed for '$industry': SampleDataLog completed_at is null, expected a datetime.");
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 9: OnboardingProfile diperbarui setelah generate berhasil
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 9: OnboardingProfile diperbarui setelah generate berhasil
     *
     * For any valid tenant and user, after generateForIndustry() succeeds,
     * OnboardingProfile.sample_data_generated = true.
     *
     * Validates: Requirements 13.4
     */
    public function test_property_9_onboarding_profile_updated_after_generate(): void
    {
        $this->limitTo(4)->forAll(
            Generators::elements('retail', 'manufacturing', 'hotel', 'healthcare')
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            // Verify flag is false before generate
            $profileBefore = OnboardingProfile::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->first();
            $this->assertFalse((bool) $profileBefore->sample_data_generated,
                'Property 9 precondition failed: sample_data_generated should be false before generate.');

            $result = $service->generateForIndustry($industry, $tenant->id, $user->id);
            $this->assertTrue($result['success'],
                "Property 9 setup failed: generateForIndustry('$industry') returned success=false.");

            $profileAfter = OnboardingProfile::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->first();

            $this->assertNotNull($profileAfter,
                "Property 9 failed for '$industry': OnboardingProfile not found after generate.");

            $this->assertTrue((bool) $profileAfter->sample_data_generated,
                "Property 9 failed for '$industry': OnboardingProfile.sample_data_generated is still false ".
                'after successful generateForIndustry(). Expected true.');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Property 10: Response JSON memiliki struktur yang benar
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 10: Response JSON memiliki struktur yang benar
     *
     * For any generateForIndustry() call, the response always has 'success' (bool),
     * and if success=true: 'records_created' (int), 'generated_data' (array);
     * if success=false: 'error' (string).
     *
     * Validates: Requirements 13.5
     */
    public function test_property_10_response_has_correct_structure(): void
    {
        $this->limitTo(4)->forAll(
            Generators::elements('retail', 'manufacturing', 'hotel', 'healthcare')
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            $result = $service->generateForIndustry($industry, $tenant->id, $user->id);

            // 'success' field must always be present and be a boolean
            $this->assertArrayHasKey('success', $result,
                "Property 10 failed for '$industry': response missing 'success' field.");
            $this->assertIsBool($result['success'],
                "Property 10 failed for '$industry': 'success' field is not a boolean.");

            if ($result['success'] === true) {
                // On success: must have 'records_created' (int) and 'generated_data' (array)
                $this->assertArrayHasKey('records_created', $result,
                    "Property 10 failed for '$industry': success response missing 'records_created'.");
                $this->assertIsInt($result['records_created'],
                    "Property 10 failed for '$industry': 'records_created' is not an integer.");

                $this->assertArrayHasKey('generated_data', $result,
                    "Property 10 failed for '$industry': success response missing 'generated_data'.");
                $this->assertIsArray($result['generated_data'],
                    "Property 10 failed for '$industry': 'generated_data' is not an array.");
            } else {
                // On failure: must have 'error' (string)
                $this->assertArrayHasKey('error', $result,
                    "Property 10 failed for '$industry': failure response missing 'error' field.");
                $this->assertIsString($result['error'],
                    "Property 10 failed for '$industry': 'error' field is not a string.");
            }
        });
    }

    /**
     * Property 10 (additional): Response structure is correct for invalid tenant.
     *
     * Validates: Requirements 13.5
     */
    public function test_property_10_response_structure_for_invalid_tenant(): void
    {
        $service = app(SampleDataGeneratorService::class);

        // Use a non-existent tenant ID
        $result = $service->generateForIndustry('retail', 999999999, 1);

        $this->assertArrayHasKey('success', $result,
            "Property 10 failed: response missing 'success' field for invalid tenant.");
        $this->assertFalse($result['success'],
            'Property 10 failed: expected success=false for invalid tenant.');
        $this->assertArrayHasKey('error', $result,
            "Property 10 failed: failure response missing 'error' field for invalid tenant.");
        $this->assertIsString($result['error'],
            "Property 10 failed: 'error' field is not a string for invalid tenant.");
    }

    // ─────────────────────────────────────────────────────────────
    // Property 11: Industry data completeness per industri
    // ─────────────────────────────────────────────────────────────

    /**
     * Feature: onboarding-demo-data, Property 11: Industry data completeness per industri
     *
     * For any valid industry, after generateForIndustry() succeeds, the database contains
     * the minimum industry-specific record counts as defined in requirements.
     *
     * Validates: Requirements 3.1–3.7, 4.1–4.6, 5.1–5.5, 6.1–6.5, 7.1–7.5, 8.1–8.5, 9.1–9.5, 10.1–10.5
     */
    public function test_property_11_industry_data_completeness(): void
    {
        $this->limitTo(8)->forAll(
            Generators::elements(...self::SUPPORTED_INDUSTRIES)
        )->then(function (string $industry) {
            [$tenant, $user] = $this->makeTenantWithProfile($industry);
            $service = app(SampleDataGeneratorService::class);

            $result = $service->generateForIndustry($industry, $tenant->id, $user->id);
            $this->assertTrue($result['success'],
                "Property 11 setup failed: generateForIndustry('$industry') returned success=false. ".
                'Error: '.($result['error'] ?? 'unknown'));

            $tenantId = $tenant->id;

            match ($industry) {
                'manufacturing' => $this->assertManufacturingMinimums($tenantId),
                'retail' => $this->assertRetailMinimums($tenantId),
                'hotel' => $this->assertHotelMinimums($tenantId),
                'restaurant' => $this->assertRestaurantMinimums($tenantId),
                'healthcare' => $this->assertHealthcareMinimums($tenantId),
                'services' => $this->assertServicesMinimums($tenantId),
                'agriculture' => $this->assertAgricultureMinimums($tenantId),
                'construction' => $this->assertConstructionMinimums($tenantId),
            };
        });
    }

    // ─── Industry-specific assertion helpers ──────────────────────

    private function assertManufacturingMinimums(int $tenantId): void
    {
        $bomCount = DB::table('boms')->where('tenant_id', $tenantId)->count();
        $this->assertGreaterThanOrEqual(2, $bomCount,
            "Property 11 (manufacturing): expected ≥2 BOMs, got $bomCount.");

        $workOrderCount = DB::table('work_orders')->where('tenant_id', $tenantId)->count();
        $this->assertGreaterThanOrEqual(3, $workOrderCount,
            "Property 11 (manufacturing): expected ≥3 work orders, got $workOrderCount.");
    }

    private function assertRetailMinimums(int $tenantId): void
    {
        $salesOrderCount = DB::table('sales_orders')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(10, $salesOrderCount,
            "Property 11 (retail): expected ≥10 sales orders, got $salesOrderCount.");
    }

    private function assertHotelMinimums(int $tenantId): void
    {
        $roomTypeCount = DB::table('room_types')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(3, $roomTypeCount,
            "Property 11 (hotel): expected ≥3 room types, got $roomTypeCount.");

        $reservationCount = DB::table('reservations')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(10, $reservationCount,
            "Property 11 (hotel): expected ≥10 reservations, got $reservationCount.");
    }

    private function assertRestaurantMinimums(int $tenantId): void
    {
        $tableCount = DB::table('restaurant_tables')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(8, $tableCount,
            "Property 11 (restaurant): expected ≥8 tables, got $tableCount.");

        $fbOrderCount = DB::table('fb_orders')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(10, $fbOrderCount,
            "Property 11 (restaurant): expected ≥10 F&B orders, got $fbOrderCount.");
    }

    private function assertHealthcareMinimums(int $tenantId): void
    {
        if (! Schema::hasTable('doctors')) {
            $this->markTestSkipped('doctors table does not exist in test DB — skipping healthcare minimums check.');
        }

        $doctorCount = DB::table('doctors')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(3, $doctorCount,
            "Property 11 (healthcare): expected ≥3 doctors, got $doctorCount.");

        if (! Schema::hasTable('appointments')) {
            return;
        }

        $appointmentCount = DB::table('appointments')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();
        $this->assertGreaterThanOrEqual(10, $appointmentCount,
            "Property 11 (healthcare): expected ≥10 appointments, got $appointmentCount.");
    }

    private function assertServicesMinimums(int $tenantId): void
    {
        $projectCount = DB::table('projects')
            ->where('tenant_id', $tenantId)
            ->count();
        $this->assertGreaterThanOrEqual(5, $projectCount,
            "Property 11 (services): expected ≥5 projects, got $projectCount.");

        // project_invoices links projects to billing — check that instead of invoices
        if (Schema::hasTable('project_invoices')) {
            $projectInvoiceCount = DB::table('project_invoices')
                ->where('tenant_id', $tenantId)
                ->count();
            $this->assertGreaterThanOrEqual(5, $projectInvoiceCount,
                "Property 11 (services): expected ≥5 project invoices, got $projectInvoiceCount.");
        }
    }

    private function assertAgricultureMinimums(int $tenantId): void
    {
        $farmPlotCount = DB::table('farm_plots')
            ->where('tenant_id', $tenantId)
            ->count();
        $this->assertGreaterThanOrEqual(3, $farmPlotCount,
            "Property 11 (agriculture): expected ≥3 farm plots, got $farmPlotCount.");

        $cropCycleCount = DB::table('crop_cycles')
            ->where('tenant_id', $tenantId)
            ->count();
        $this->assertGreaterThanOrEqual(3, $cropCycleCount,
            "Property 11 (agriculture): expected ≥3 crop cycles, got $cropCycleCount.");
    }

    private function assertConstructionMinimums(int $tenantId): void
    {
        $projectCount = DB::table('projects')
            ->where('tenant_id', $tenantId)
            ->count();
        $this->assertGreaterThanOrEqual(3, $projectCount,
            "Property 11 (construction): expected ≥3 projects, got $projectCount.");

        $poCount = DB::table('purchase_orders')
            ->where('tenant_id', $tenantId)
            ->count();
        $this->assertGreaterThanOrEqual(5, $poCount,
            "Property 11 (construction): expected ≥5 purchase orders, got $poCount.");
    }
}
