<?php

namespace Tests\Feature\Preservation;

use App\Models\Tenant;
use App\Models\User;
use App\Services\ModuleRecommendationService;
use Tests\TestCase;

/**
 * Preservation Tests — Package Module Validation
 *
 * These tests MUST PASS on unfixed code.
 * They establish the baseline behaviors that must not regress after the fix is applied.
 *
 * Preserved behaviors:
 *   3.1 Tenant with enabled_modules = null can access all modules (backward compat)
 *   3.2 Tenant activating allowed modules has them saved correctly
 *   3.3 Super-admin has full access (CheckTenantActive skips super_admin)
 *   3.4 Professional/Enterprise can activate advanced modules
 *   3.5 isModuleEnabled() respects the enabled_modules array
 *   3.6 Expired tenant is redirected to subscription.expired by CheckTenantActive
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6
 */
class PackageModuleValidationPreservationTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 2.1 — Null enabled_modules backward compatibility
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Preservation 3.1:
     * Tenant with enabled_modules = null (legacy tenant) must be able to access all modules.
     * null means "all modules active" — this backward compat must not be broken by the fix.
     *
     * MUST PASS on unfixed code (PRESERVE).
     *
     * Validates: Requirements 3.1
     */
    public function test_null_enabled_modules_backward_compatibility(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'pro',
            'enabled_modules' => null,
        ]);
        $user = $this->createAdminUser($tenant);

        // isModuleEnabled returns true for any module when enabled_modules is null
        $this->assertTrue(
            $tenant->isModuleEnabled('manufacturing'),
            "Preservation 3.1: isModuleEnabled('manufacturing') must return true when enabled_modules is null."
        );

        $this->assertTrue(
            $tenant->isModuleEnabled('fleet'),
            "Preservation 3.1: isModuleEnabled('fleet') must return true when enabled_modules is null."
        );

        $this->assertTrue(
            $tenant->isModuleEnabled('pos'),
            "Preservation 3.1: isModuleEnabled('pos') must return true when enabled_modules is null."
        );

        // enabledModules() returns ALL_MODULES when enabled_modules is null
        $allModules = ModuleRecommendationService::ALL_MODULES;
        $enabledModules = $tenant->enabledModules();

        $this->assertEquals(
            sort($allModules),
            sort($enabledModules),
            "Preservation 3.1: enabledModules() must return all modules from ALL_MODULES when enabled_modules is null."
        );

        $this->assertCount(
            count($allModules),
            $enabledModules,
            "Preservation 3.1: enabledModules() must return exactly " . count($allModules) . " modules when enabled_modules is null."
        );

        // Tenant with null enabled_modules can access fleet route without 403
        // (on unfixed code there's no plan middleware, so it won't 403)
        $this->actingAs($user);
        $response = $this->get('/fleet');

        $this->assertNotEquals(
            403,
            $response->getStatusCode(),
            "Preservation 3.1: Tenant with null enabled_modules must NOT get 403 on /fleet. " .
            "Got: HTTP " . $response->getStatusCode()
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 2.2 — Allowed module activation succeeds
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Preservation 3.2:
     * Tenant with plan `business` activating modules that are allowed for business plan
     * must succeed — the modules must be saved to enabled_modules correctly.
     *
     * MUST PASS on unfixed code (PRESERVE).
     *
     * Validates: Requirements 3.2
     */
    public function test_allowed_module_activation_succeeds(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'business',
            'enabled_modules' => ['pos'], // start with something to avoid null path
        ]);
        $user = $this->createAdminUser($tenant);

        $this->actingAs($user);

        // These modules are expected to be allowed for business plan
        $modulesToActivate = ['pos', 'crm', 'helpdesk', 'subscription_billing'];

        // withoutExceptionHandling to avoid view rendering issues
        // update() calls back()->with('success', ...) on success → HTTP 302
        $response = $this->withoutExceptionHandling()->put('/settings/modules', [
            'modules' => $modulesToActivate,
        ]);

        // Must succeed with HTTP 302 (redirect back)
        $response->assertStatus(302);

        // Verify enabled_modules was updated correctly
        $tenant->refresh();
        $savedModules = $tenant->enabled_modules ?? [];

        foreach ($modulesToActivate as $module) {
            $this->assertContains(
                $module,
                $savedModules,
                "Preservation 3.2: Module '{$module}' should be saved to enabled_modules for business plan tenant."
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 2.3 — Super-admin has full access
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Preservation 3.3:
     * Super-admin must not be blocked by CheckTenantActive middleware.
     * The middleware explicitly skips super_admin and affiliate roles.
     * Even if the tenant's subscription is expired, super-admin must not be redirected.
     *
     * Note: The fleet route has role:admin,manager,gudang middleware, so super_admin
     * won't pass that role check. Instead, we test that CheckTenantActive does NOT
     * redirect super-admin to subscription.expired — we use an expired tenant to
     * confirm the middleware skip works for super_admin.
     *
     * MUST PASS on unfixed code (PRESERVE).
     *
     * Validates: Requirements 3.3
     */
    public function test_super_admin_not_blocked_by_check_tenant_active(): void
    {
        // Create a tenant that is expired — CheckTenantActive would normally redirect
        $tenant = $this->createTenant([
            'plan'            => 'pro',
            'plan_expires_at' => now()->subDays(10), // expired
            'is_active'       => true,
        ]);

        // Create super-admin user
        $superAdmin = User::create([
            'tenant_id'         => $tenant->id,
            'name'              => 'Super Admin Test',
            'email'             => 'superadmin-' . uniqid() . '@test.com',
            'password'          => bcrypt('password'),
            'role'              => 'super_admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($superAdmin);

        // Verify the tenant is actually expired (canAccess returns false)
        $this->assertFalse(
            $tenant->canAccess(),
            "Preservation 3.3: Test setup — tenant should be expired for this test to be meaningful."
        );

        // Super-admin accessing a route should NOT be redirected to subscription.expired
        // We use /settings/modules (GET) which is accessible to admin role — but super_admin
        // bypasses CheckTenantActive entirely, so it won't redirect to subscription.expired
        $response = $this->get('/settings/modules');

        $this->assertNotEquals(
            route('subscription.expired'),
            $response->headers->get('Location'),
            "Preservation 3.3: Super-admin must NOT be redirected to subscription.expired " .
            "even when tenant is expired. CheckTenantActive must skip super_admin role."
        );

        // Should not redirect to subscription.expired route
        if ($response->isRedirect()) {
            $location = $response->headers->get('Location');
            $expiredUrl = route('subscription.expired');
            $this->assertStringNotContainsString(
                'subscription/expired',
                $location ?? '',
                "Preservation 3.3: Super-admin redirect location must not be subscription/expired. Got: {$location}"
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 2.4 — Professional/Enterprise can activate advanced modules
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Preservation 3.4:
     * Tenant with plan `professional` must be able to activate advanced modules
     * like manufacturing, fleet, and wms — these are allowed for professional plan.
     *
     * MUST PASS on unfixed code (PRESERVE).
     *
     * Validates: Requirements 3.4
     */
    public function test_professional_plan_can_activate_advanced_modules(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'professional',
            'enabled_modules' => ['pos'], // start with something to avoid null path
        ]);
        $user = $this->createAdminUser($tenant);

        $this->actingAs($user);

        $advancedModules = ['manufacturing', 'fleet', 'wms'];

        // withoutExceptionHandling to avoid view rendering issues
        $response = $this->withoutExceptionHandling()->put('/settings/modules', [
            'modules' => $advancedModules,
        ]);

        // Must succeed with HTTP 302 (redirect back with success)
        $response->assertStatus(302);

        // Verify all advanced modules were saved
        $tenant->refresh();
        $savedModules = $tenant->enabled_modules ?? [];

        foreach ($advancedModules as $module) {
            $this->assertContains(
                $module,
                $savedModules,
                "Preservation 3.4: Advanced module '{$module}' should be saved for professional plan tenant."
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 2.5 — isModuleEnabled respects enabled_modules array
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Preservation 3.5:
     * When enabled_modules is a specific array, isModuleEnabled() must return true
     * only for modules in that array, and false for modules not in it.
     *
     * MUST PASS on unfixed code (PRESERVE).
     *
     * Validates: Requirements 3.5
     */
    public function test_is_module_enabled_respects_enabled_modules_array(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'pro',
            'enabled_modules' => ['pos', 'inventory'],
        ]);

        // pos is in the array — must return true
        $this->assertTrue(
            $tenant->isModuleEnabled('pos'),
            "Preservation 3.5: isModuleEnabled('pos') must return true when 'pos' is in enabled_modules."
        );

        // inventory is in the array — must return true
        $this->assertTrue(
            $tenant->isModuleEnabled('inventory'),
            "Preservation 3.5: isModuleEnabled('inventory') must return true when 'inventory' is in enabled_modules."
        );

        // manufacturing is NOT in the array — must return false
        $this->assertFalse(
            $tenant->isModuleEnabled('manufacturing'),
            "Preservation 3.5: isModuleEnabled('manufacturing') must return false when 'manufacturing' is NOT in enabled_modules."
        );

        // fleet is NOT in the array — must return false
        $this->assertFalse(
            $tenant->isModuleEnabled('fleet'),
            "Preservation 3.5: isModuleEnabled('fleet') must return false when 'fleet' is NOT in enabled_modules."
        );

        // crm is NOT in the array — must return false
        $this->assertFalse(
            $tenant->isModuleEnabled('crm'),
            "Preservation 3.5: isModuleEnabled('crm') must return false when 'crm' is NOT in enabled_modules."
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 2.6 — Expired tenant redirected by CheckTenantActive
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Preservation 3.6:
     * Tenant with an expired plan (plan_expires_at in the past, plan != 'trial')
     * must be redirected to subscription.expired route by CheckTenantActive middleware.
     * This behavior must not be broken by the fix.
     *
     * MUST PASS on unfixed code (PRESERVE).
     *
     * Validates: Requirements 3.6
     */
    public function test_expired_tenant_redirected_by_check_tenant_active(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'pro',
            'plan_expires_at' => now()->subDays(5), // expired 5 days ago
            'is_active'       => true,
        ]);
        $user = $this->createAdminUser($tenant);

        // Verify the tenant is actually expired
        $this->assertTrue(
            $tenant->isPlanExpired(),
            "Preservation 3.6: Test setup — tenant plan should be expired."
        );
        $this->assertFalse(
            $tenant->canAccess(),
            "Preservation 3.6: Test setup — tenant canAccess() should return false."
        );

        $this->actingAs($user);

        // Attempt to access any route — should be redirected to subscription.expired
        $response = $this->get('/fleet');

        // CheckTenantActive must redirect to subscription.expired
        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertStringContainsString(
            'subscription/expired',
            $location ?? '',
            "Preservation 3.6: Expired tenant must be redirected to subscription/expired route. " .
            "Got redirect to: {$location}"
        );
    }
}
