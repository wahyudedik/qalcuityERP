<?php

namespace Tests\Feature\BugExploration;

use App\Http\Controllers\OnboardingController;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ModuleRecommendationService;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test — Module Toggle Tenant Bug
 *
 * EXPECTED: These tests MUST FAIL on unfixed code.
 * Failure confirms the bugs exist:
 *
 *   Bug 1: complete() saves selected_modules to OnboardingProfile but NOT to
 *           tenants.enabled_modules. After onboarding, tenant.enabled_modules
 *           remains null (all modules active).
 *
 *   Bug 2: OnboardingController receives industry keys like 'restaurant',
 *           'manufacturing', 'services', but ModuleRecommendationService::recommend()
 *           uses different keys: 'fnb', 'manufacture', 'service'. No mapping exists,
 *           so industry-based recommendations always fall back to 'default'.
 *
 * When these tests PASS after the fix is applied, it confirms both bugs are resolved.
 *
 * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5
 */
class ModuleToggleTenantBugConditionTest extends TestCase
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

    /**
     * @test
     * Bug 1 — complete() does NOT save selected_modules to tenants.enabled_modules
     *
     * WILL FAIL on unfixed code:
     *   tenant.enabled_modules remains null after complete() even when
     *   selected_modules = ['pos', 'hrm'] is passed.
     *
     * Counterexample: complete({selected_modules: ['pos', 'hrm'], industry: 'retail', ...})
     *   → tenant.enabled_modules = null (expected: ['pos', 'hrm'])
     *
     * Validates: Requirements 1.1, 1.2
     */
    public function test_bug1_complete_saves_selected_modules_to_tenant_enabled_modules(): void
    {
        $controller = app(OnboardingController::class);

        $request = Request::create('/onboarding/complete', 'POST', [
            'industry'        => 'retail',
            'business_size'   => 'small',
            'selected_modules' => ['pos', 'hrm'],
        ]);
        $request->setUserResolver(fn () => $this->user);

        // Simulate validation by merging into a proper Request
        $request = new Request([], [
            'industry'        => 'retail',
            'business_size'   => 'small',
            'selected_modules' => ['pos', 'hrm'],
        ]);
        $request->setUserResolver(fn () => $this->user);

        try {
            $controller->complete($request);
        } catch (\Throwable $e) {
            // Redirect exceptions are fine — we only care about DB state
        }

        $this->tenant->refresh();

        // On unfixed code: enabled_modules remains null
        // This assertion WILL FAIL on unfixed code (null !== ['pos', 'hrm'])
        $this->assertEquals(
            ['pos', 'hrm'],
            $this->tenant->enabled_modules,
            "Bug 1: tenant.enabled_modules should be ['pos', 'hrm'] after complete() " .
            "with selected_modules=['pos', 'hrm'], but got: " .
            json_encode($this->tenant->enabled_modules) .
            ". Counterexample: tenant.enabled_modules remains null after complete()."
        );
    }

    /**
     * @test
     * Bug 2a — recommend('restaurant') returns default modules, not F&B modules
     *
     * WILL FAIL on unfixed code:
     *   ModuleRecommendationService::recommend('restaurant') falls through to
     *   the default case because 'restaurant' is not a recognized key.
     *   The correct key is 'fnb'.
     *
     * Counterexample: recommend('restaurant')
     *   → ['pos', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'reports']
     *     (default modules, not F&B-specific modules)
     *
     * Validates: Requirements 1.3
     */
    public function test_bug2a_recommend_restaurant_returns_non_default_modules(): void
    {
        $service = app(ModuleRecommendationService::class);

        $defaultModules = $service->recommend('__nonexistent__')['modules'];
        $restaurantResult = $service->recommend('restaurant');

        // On unfixed code: recommend('restaurant') returns default modules
        // because 'restaurant' key is not recognized — falls to default case
        $this->assertNotEquals(
            $defaultModules,
            $restaurantResult['modules'],
            "Bug 2a: recommend('restaurant') returned default modules: " .
            json_encode($restaurantResult['modules']) .
            ". Expected F&B-specific modules (not default). " .
            "Counterexample: 'restaurant' key not mapped to 'fnb', falls to default."
        );
    }

    /**
     * @test
     * Bug 2b — recommend('manufacturing') returns default modules, not manufacture modules
     *
     * WILL FAIL on unfixed code:
     *   ModuleRecommendationService::recommend('manufacturing') falls through to
     *   the default case because 'manufacturing' is not a recognized key.
     *   The correct key is 'manufacture'.
     *
     * Counterexample: recommend('manufacturing')
     *   → default modules (not manufacture-specific modules)
     *
     * Validates: Requirements 1.4
     */
    public function test_bug2b_recommend_manufacturing_returns_non_default_modules(): void
    {
        $service = app(ModuleRecommendationService::class);

        $defaultModules = $service->recommend('__nonexistent__')['modules'];
        $manufacturingResult = $service->recommend('manufacturing');

        // On unfixed code: recommend('manufacturing') returns default modules
        // because 'manufacturing' key is not recognized — falls to default case
        $this->assertNotEquals(
            $defaultModules,
            $manufacturingResult['modules'],
            "Bug 2b: recommend('manufacturing') returned default modules: " .
            json_encode($manufacturingResult['modules']) .
            ". Expected manufacture-specific modules (not default). " .
            "Counterexample: 'manufacturing' key not mapped to 'manufacture', falls to default."
        );
    }

    /**
     * @test
     * Bug 2c — recommend('services') returns default modules, not service modules
     *
     * WILL FAIL on unfixed code:
     *   ModuleRecommendationService::recommend('services') falls through to
     *   the default case because 'services' is not a recognized key.
     *   The correct key is 'service'.
     *
     * Counterexample: recommend('services')
     *   → default modules (not service-specific modules)
     *
     * Validates: Requirements 1.5
     */
    public function test_bug2c_recommend_services_returns_non_default_modules(): void
    {
        $service = app(ModuleRecommendationService::class);

        $defaultModules = $service->recommend('__nonexistent__')['modules'];
        $servicesResult = $service->recommend('services');

        // On unfixed code: recommend('services') returns default modules
        // because 'services' key is not recognized — falls to default case
        $this->assertNotEquals(
            $defaultModules,
            $servicesResult['modules'],
            "Bug 2c: recommend('services') returned default modules: " .
            json_encode($servicesResult['modules']) .
            ". Expected service-specific modules (not default). " .
            "Counterexample: 'services' key not mapped to 'service', falls to default."
        );
    }
}
