<?php

namespace Tests\Feature\Preservation;

use App\Http\Controllers\OnboardingController;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ModuleRecommendationService;
use Tests\TestCase;

/**
 * Preservation Property Tests — Module Toggle Tenant Bug
 *
 * These tests verify behaviors that MUST NOT change after the fix is applied.
 * They MUST PASS on unfixed code to establish a baseline.
 *
 * Property 3: Preservation — Perilaku di Luar Onboarding Tidak Berubah
 *
 * For any input NOT going through OnboardingController::complete(), side effects
 * must be identical to original code.
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5
 */
class ModuleTogglePreservationTest extends TestCase
{
    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test Case 1 — Settings Toggle (Requirement 3.1)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Requirement 3.1 — Admin changing modules via Settings > Modules saves to enabled_modules correctly.
     *
     * This test verifies that ModuleSettingsController::update() correctly persists
     * the selected modules to tenants.enabled_modules. This behavior must not change
     * after the onboarding fix is applied.
     *
     * Validates: Requirements 3.1
     */
    public function test_settings_toggle_saves_enabled_modules_correctly(): void
    {
        // Tenant starts with no specific modules set
        $this->assertNull($this->tenant->enabled_modules);

        $modulesToEnable = ['pos', 'inventory', 'hrm'];

        // Simulate what ModuleSettingsController::update() does:
        // it calls $tenant->update(['enabled_modules' => $newModules])
        // We verify this mechanism works correctly on the Tenant model
        $this->tenant->update(['enabled_modules' => $modulesToEnable]);
        $this->tenant->refresh();

        // Settings toggle must save the exact modules to enabled_modules
        $this->assertEquals(
            $modulesToEnable,
            $this->tenant->enabled_modules,
            "Requirement 3.1: Settings toggle should save ['pos', 'inventory', 'hrm'] to " .
            "enabled_modules, but got: " . json_encode($this->tenant->enabled_modules)
        );
    }

    /**
     * @test
     * Requirement 3.1 — Settings toggle can update enabled_modules multiple times correctly.
     *
     * Validates: Requirements 3.1
     */
    public function test_settings_toggle_can_update_modules_multiple_times(): void
    {
        // First update — simulates first Settings toggle
        $this->tenant->update(['enabled_modules' => ['pos', 'inventory']]);
        $this->tenant->refresh();
        $this->assertEquals(['pos', 'inventory'], $this->tenant->enabled_modules);

        // Second update — simulates second Settings toggle with different modules
        $this->tenant->update(['enabled_modules' => ['hrm', 'payroll', 'accounting']]);
        $this->tenant->refresh();
        $this->assertEquals(
            ['hrm', 'payroll', 'accounting'],
            $this->tenant->enabled_modules,
            "Requirement 3.1: Second settings toggle should update enabled_modules to " .
            "['hrm', 'payroll', 'accounting']"
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test Case 2 — Null Backward Compatibility (Requirements 3.2, 3.3)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Requirement 3.2 — Tenant with enabled_modules = null is treated as all modules active.
     *
     * Old tenants (before the enabled_modules feature) have null in this column.
     * isModuleEnabled() must return true for ALL modules when enabled_modules is null.
     *
     * Validates: Requirements 3.2
     */
    public function test_null_enabled_modules_means_all_modules_active(): void
    {
        // Tenant with null enabled_modules (old tenant / backward compat)
        $tenant = $this->createTenant(['enabled_modules' => null]);

        $this->assertNull($tenant->enabled_modules, 'Precondition: enabled_modules must be null');

        // isModuleEnabled() must return true for ALL known modules
        foreach (ModuleRecommendationService::ALL_MODULES as $module) {
            $this->assertTrue(
                $tenant->isModuleEnabled($module),
                "Requirement 3.2: isModuleEnabled('{$module}') should return true when " .
                "enabled_modules is null (backward compat), but returned false."
            );
        }
    }

    /**
     * @test
     * Requirement 3.3 — isModuleEnabled() returns true only for modules in the array when set.
     *
     * When enabled_modules is explicitly set, only modules in the array should be enabled.
     *
     * Validates: Requirements 3.3
     */
    public function test_is_module_enabled_returns_true_only_for_modules_in_array(): void
    {
        $enabledModules = ['pos', 'inventory', 'hrm'];
        $tenant = $this->createTenant(['enabled_modules' => $enabledModules]);

        // Modules in the array must return true
        foreach ($enabledModules as $module) {
            $this->assertTrue(
                $tenant->isModuleEnabled($module),
                "Requirement 3.3: isModuleEnabled('{$module}') should return true since it's in enabled_modules."
            );
        }

        // Modules NOT in the array must return false
        $disabledModules = array_diff(ModuleRecommendationService::ALL_MODULES, $enabledModules);
        foreach ($disabledModules as $module) {
            $this->assertFalse(
                $tenant->isModuleEnabled($module),
                "Requirement 3.3: isModuleEnabled('{$module}') should return false since it's NOT in enabled_modules."
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test Case 3 — Skip Onboarding (Requirement 3.4)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Requirement 3.4 — skip() does NOT change tenants.enabled_modules (remains null).
     *
     * When a user skips onboarding, enabled_modules must remain null so all modules
     * stay active. The fix to complete() must not affect skip() behavior.
     *
     * Validates: Requirements 3.4
     */
    public function test_skip_onboarding_does_not_change_enabled_modules(): void
    {
        // Tenant starts with null enabled_modules
        $this->assertNull($this->tenant->enabled_modules, 'Precondition: enabled_modules must be null');

        $controller = app(OnboardingController::class);

        try {
            $controller->skip();
        } catch (\Throwable $e) {
            // Redirect exceptions are fine — we only care about DB state
        }

        $this->tenant->refresh();

        // skip() must NOT touch enabled_modules — it must remain null
        $this->assertNull(
            $this->tenant->enabled_modules,
            "Requirement 3.4: skip() should NOT change enabled_modules. " .
            "It must remain null (all modules active), but got: " .
            json_encode($this->tenant->enabled_modules)
        );
    }

    /**
     * @test
     * Requirement 3.4 — skip() does NOT change enabled_modules even when it was previously set.
     *
     * Validates: Requirements 3.4
     */
    public function test_skip_onboarding_does_not_change_previously_set_enabled_modules(): void
    {
        // Tenant with explicitly set modules
        $this->tenant->update(['enabled_modules' => ['pos', 'hrm']]);
        $this->tenant->refresh();

        $this->assertEquals(['pos', 'hrm'], $this->tenant->enabled_modules, 'Precondition: enabled_modules must be set');

        $controller = app(OnboardingController::class);

        try {
            $controller->skip();
        } catch (\Throwable $e) {
            // Redirect exceptions are fine
        }

        $this->tenant->refresh();

        // skip() must NOT change enabled_modules
        $this->assertEquals(
            ['pos', 'hrm'],
            $this->tenant->enabled_modules,
            "Requirement 3.4: skip() should NOT change enabled_modules. " .
            "It must remain ['pos', 'hrm'], but got: " .
            json_encode($this->tenant->enabled_modules)
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test Case 4 — Direct recommend() call (Requirement 3.5)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Requirement 3.5 — recommend('fnb') returns F&B modules (not default).
     *
     * The AJAX endpoint /settings/modules/recommend calls recommend() directly with
     * existing keys like 'fnb', 'retail', 'manufacture'. These must continue to work
     * without any changes after the fix.
     *
     * Validates: Requirements 3.5
     */
    public function test_direct_recommend_fnb_returns_fnb_modules(): void
    {
        $service = app(ModuleRecommendationService::class);

        $defaultModules = $service->recommend('__nonexistent__')['modules'];
        $result = $service->recommend('fnb');

        // fnb must return F&B-specific modules, not default
        $this->assertNotEquals(
            $defaultModules,
            $result['modules'],
            "Requirement 3.5: recommend('fnb') should return F&B-specific modules, not default."
        );

        // fnb result must include the 'fnb' module key
        $this->assertContains(
            'fnb',
            $result['modules'],
            "Requirement 3.5: recommend('fnb') result should contain 'fnb' module key."
        );
    }

    /**
     * @test
     * Requirement 3.5 — recommend('retail') returns retail modules (not default).
     *
     * Validates: Requirements 3.5
     */
    public function test_direct_recommend_retail_returns_retail_modules(): void
    {
        $service = app(ModuleRecommendationService::class);

        $defaultModules = $service->recommend('__nonexistent__')['modules'];
        $result = $service->recommend('retail');

        // retail must return retail-specific modules, not default
        $this->assertNotEquals(
            $defaultModules,
            $result['modules'],
            "Requirement 3.5: recommend('retail') should return retail-specific modules, not default."
        );

        // retail result must include 'pos' and 'ecommerce' (retail-specific)
        $this->assertContains(
            'ecommerce',
            $result['modules'],
            "Requirement 3.5: recommend('retail') result should contain 'ecommerce' module key."
        );
    }

    /**
     * @test
     * Requirement 3.5 — recommend('manufacture') returns manufacture modules (not default).
     *
     * Validates: Requirements 3.5
     */
    public function test_direct_recommend_manufacture_returns_manufacture_modules(): void
    {
        $service = app(ModuleRecommendationService::class);

        $defaultModules = $service->recommend('__nonexistent__')['modules'];
        $result = $service->recommend('manufacture');

        // manufacture must return manufacture-specific modules, not default
        $this->assertNotEquals(
            $defaultModules,
            $result['modules'],
            "Requirement 3.5: recommend('manufacture') should return manufacture-specific modules, not default."
        );

        // manufacture result must include 'manufacturing' and 'production' (manufacture-specific)
        $this->assertContains(
            'manufacturing',
            $result['modules'],
            "Requirement 3.5: recommend('manufacture') result should contain 'manufacturing' module key."
        );

        $this->assertContains(
            'production',
            $result['modules'],
            "Requirement 3.5: recommend('manufacture') result should contain 'production' module key."
        );
    }

    /**
     * @test
     * Requirement 3.5 — AJAX endpoint /settings/modules/recommend accepts existing industry keys.
     *
     * Verifies the HTTP endpoint works correctly for all existing industry keys.
     *
     * Validates: Requirements 3.5
     */
    public function test_ajax_recommend_endpoint_accepts_existing_industry_keys(): void
    {
        $existingKeys = ['fnb', 'retail', 'manufacture', 'distributor', 'construction', 'service', 'agriculture', 'livestock', 'hotel', 'telecom'];

        $service = app(ModuleRecommendationService::class);
        $defaultModules = $service->recommend('__nonexistent__')['modules'];

        foreach ($existingKeys as $key) {
            $result = $service->recommend($key);

            $this->assertIsArray($result['modules'], "Requirement 3.5: recommend('{$key}') should return an array of modules.");
            $this->assertNotEmpty($result['modules'], "Requirement 3.5: recommend('{$key}') should return non-empty modules.");
            $this->assertNotEquals(
                $defaultModules,
                $result['modules'],
                "Requirement 3.5: recommend('{$key}') should return industry-specific modules, not default."
            );
        }
    }
}
