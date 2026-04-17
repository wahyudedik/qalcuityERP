<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test — Package Module Validation
 *
 * EXPECTED: These tests MUST FAIL on unfixed code.
 * Failure confirms the bugs exist:
 *
 *   Bug 1.1: ModuleSettingsController::update() does NOT validate modules against plan.
 *            Tenant with starter plan can activate manufacturing module freely.
 *
 *   Bug 1.2: TenantController::updatePlan() does NOT sync enabled_modules after plan downgrade.
 *            enabled_modules retains disallowed modules (fleet, wms) after downgrade to starter.
 *
 *   Bug 1.3: No middleware validates plan access for module routes.
 *            Tenant with starter plan can access /fleet route without 403.
 *
 *   Bug 1.4: TenantController::updatePlan() uses legacy slug validation (trial,basic,pro,enterprise).
 *            Updating plan to 'starter' fails validation on unfixed code.
 *
 * When these tests PASS after the fix is applied, it confirms all bugs are resolved.
 *
 * Validates: Requirements 1.1, 1.2, 1.3, 1.4
 */
class PackageModuleValidationBugTest extends TestCase
{
    private Tenant $tenant;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 1.1 — Starter plan activates manufacturing module
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Bug Condition 1.1:
     * Tenant with plan `starter` sends PUT /settings/modules with modules=['pos','manufacturing'].
     * EXPECTED (after fix): HTTP 422 with validation error mentioning manufacturing not allowed.
     *
     * WILL FAIL on unfixed code:
     *   Request succeeds (HTTP 302 redirect back with success) and manufacturing is saved
     *   to enabled_modules without any plan validation.
     *
     * Counterexample: tenant{plan='starter'} + PUT /settings/modules{modules=['pos','manufacturing']}
     *   → HTTP 302 (success), enabled_modules=['pos','manufacturing'] (BUG: manufacturing saved)
     *
     * Document: "Tenant with starter plan can activate manufacturing module without validation"
     *
     * Validates: Requirements 1.1
     */
    public function test_starter_plan_cannot_activate_manufacturing_module(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'starter',
            'enabled_modules' => ['pos'], // start with just pos to avoid cleanup service issues
        ]);
        $user   = $this->createAdminUser($tenant);

        $this->actingAs($user);

        // The update() method calls back()->with('success', ...) on success (BUG path)
        $response = $this->put('/settings/modules', [
            'modules' => ['pos', 'manufacturing'],
        ]);

        // On unfixed code: returns HTTP 302 (redirect back with success)
        // and manufacturing is saved to enabled_modules — BUG
        // After fix: should return HTTP 422 with validation error

        // Verify manufacturing was NOT saved to enabled_modules
        $tenant->refresh();
        $this->assertNotContains(
            'manufacturing',
            $tenant->enabled_modules ?? [],
            "Bug 1.1: Tenant with starter plan should NOT be able to activate manufacturing module. " .
            "Counterexample: enabled_modules=" . json_encode($tenant->enabled_modules) .
            " contains 'manufacturing' which is not allowed for starter plan."
        );

        // The response should be a validation error (422), not a success redirect (302)
        $this->assertNotEquals(
            302,
            $response->getStatusCode(),
            "Bug 1.1: PUT /settings/modules with manufacturing should NOT succeed (302 redirect). " .
            "Expected HTTP 422 validation error. Got: " . $response->getStatusCode()
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 1.2 — Plan downgrade does not sync enabled_modules
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Bug Condition 1.2:
     * Tenant with plan `professional` and enabled_modules=['pos','fleet','wms'].
     * Super-admin sends PATCH /super-admin/tenants/{id}/plan with plan='starter'.
     * EXPECTED (after fix): enabled_modules is updated to only contain modules allowed by starter.
     *
     * WILL FAIL on unfixed code:
     *   enabled_modules remains ['pos','fleet','wms'] — fleet and wms are NOT stripped.
     *   Additionally, the validation rule 'in:trial,basic,pro,enterprise' rejects 'starter'.
     *
     * Counterexample: tenant{plan='professional', enabled_modules=['pos','fleet','wms']}
     *   + PATCH plan='starter' → enabled_modules still=['pos','fleet','wms'] (BUG: fleet/wms not stripped)
     *
     * Document: "Plan downgrade does not strip disallowed modules from enabled_modules"
     *
     * Validates: Requirements 1.2
     */
    public function test_plan_downgrade_syncs_enabled_modules(): void
    {
        // Create a tenant with professional plan and advanced modules
        $tenant = $this->createTenant([
            'plan'            => 'professional',
            'enabled_modules' => ['pos', 'fleet', 'wms'],
        ]);

        // Create a super-admin user (no tenant_id needed for super_admin)
        $superAdmin = User::create([
            'tenant_id'         => $tenant->id, // super_admin still needs a tenant_id in this app
            'name'              => 'Super Admin Test',
            'email'             => 'superadmin-' . uniqid() . '@test.com',
            'password'          => bcrypt('password'),
            'role'              => 'super_admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($superAdmin);

        // Super-admin downgrades tenant plan to starter
        $response = $this->patch("/super-admin/tenants/{$tenant->id}/plan", [
            'plan' => 'starter',
        ]);

        // On unfixed code: validation fails with 422 because 'starter' is not in
        // 'in:trial,basic,pro,enterprise' — OR if it somehow passes, enabled_modules is not synced.
        // After fix: should redirect back with success (HTTP 302)
        $response->assertRedirect();

        // Verify enabled_modules was synced — fleet and wms should be removed
        $tenant->refresh();

        $this->assertNotContains(
            'fleet',
            $tenant->enabled_modules ?? [],
            "Bug 1.2: After downgrade to starter, 'fleet' should be removed from enabled_modules. " .
            "Counterexample: enabled_modules=" . json_encode($tenant->enabled_modules) .
            " still contains 'fleet' which is not allowed for starter plan."
        );

        $this->assertNotContains(
            'wms',
            $tenant->enabled_modules ?? [],
            "Bug 1.2: After downgrade to starter, 'wms' should be removed from enabled_modules. " .
            "Counterexample: enabled_modules=" . json_encode($tenant->enabled_modules) .
            " still contains 'wms' which is not allowed for starter plan."
        );

        // pos should still be present (allowed in starter)
        $this->assertContains(
            'pos',
            $tenant->enabled_modules ?? [],
            "Bug 1.2: 'pos' should remain in enabled_modules after downgrade to starter (it is allowed)."
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 1.3 — Route access without plan validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Bug Condition 1.3:
     * Tenant with plan `starter` and enabled_modules=['pos','inventory'].
     * Attempts to access GET /fleet (fleet module route).
     * EXPECTED (after fix): HTTP 403 with message about plan upgrade required.
     *
     * WILL FAIL on unfixed code:
     *   Route is accessible (no middleware blocks it based on plan).
     *   Returns HTTP 200 or redirect to login — NOT 403.
     *
     * Counterexample: tenant{plan='starter', enabled_modules=['pos','inventory']}
     *   + GET /fleet → HTTP 200 (BUG: no plan-level middleware blocks access)
     *
     * Document: "No middleware validates plan access for module routes"
     *
     * Validates: Requirements 1.3
     */
    public function test_starter_plan_cannot_access_fleet_route(): void
    {
        $tenant = $this->createTenant([
            'plan'            => 'starter',
            'enabled_modules' => ['pos', 'inventory'],
        ]);
        $user = $this->createAdminUser($tenant);

        $this->actingAs($user);

        // Attempt to access the fleet module index route
        $response = $this->get('/fleet');

        // On unfixed code: returns HTTP 200 (no plan-level middleware) — BUG
        // After fix: should return HTTP 403 with upgrade message
        $response->assertStatus(403);

        // The response should mention plan upgrade
        $responseContent = $response->getContent();
        $this->assertTrue(
            str_contains($responseContent, 'upgrade') ||
            str_contains($responseContent, 'paket') ||
            str_contains($responseContent, 'plan') ||
            $response->isClientError(),
            "Bug 1.3: Accessing /fleet with starter plan should return 403 with upgrade message. " .
            "Counterexample: GET /fleet with starter plan returned HTTP " . $response->getStatusCode() .
            " — no plan-level middleware blocks access."
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subtask 1.4 — Legacy plan slug validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Bug Condition 1.4:
     * Super-admin attempts to update tenant plan to 'starter'.
     * EXPECTED (after fix): Validation accepts 'starter', 'business', 'professional', 'enterprise'.
     *
     * WILL FAIL on unfixed code:
     *   Validation rule is 'in:trial,basic,pro,enterprise' — 'starter' is rejected with HTTP 422.
     *
     * Counterexample: PATCH /super-admin/tenants/{id}/plan{plan='starter'}
     *   → HTTP 422 validation error: "The selected plan is invalid." (BUG: legacy slug list)
     *
     * Document: "TenantController::updatePlan uses legacy slug validation (trial,basic,pro,enterprise)"
     *
     * Validates: Requirements 1.4
     */
    public function test_update_plan_accepts_new_plan_slugs(): void
    {
        $tenant = $this->createTenant(['plan' => 'professional']);

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

        // Test that 'starter' slug is accepted (not rejected by legacy validation)
        $response = $this->patch("/super-admin/tenants/{$tenant->id}/plan", [
            'plan' => 'starter',
        ]);

        // On unfixed code: returns HTTP 422 because 'starter' is not in
        // 'in:trial,basic,pro,enterprise' validation rule — BUG
        // After fix: should redirect (HTTP 302) with success
        $this->assertNotEquals(
            422,
            $response->getStatusCode(),
            "Bug 1.4: Updating plan to 'starter' should be accepted. " .
            "Counterexample: PATCH plan='starter' → HTTP 422 because validation rule uses " .
            "legacy slugs 'in:trial,basic,pro,enterprise' which does not include 'starter'."
        );

        // Also verify 'business' and 'professional' are accepted
        $responseBusiness = $this->patch("/super-admin/tenants/{$tenant->id}/plan", [
            'plan' => 'business',
        ]);
        $this->assertNotEquals(
            422,
            $responseBusiness->getStatusCode(),
            "Bug 1.4: Updating plan to 'business' should be accepted."
        );

        // Verify the plan was actually updated to starter
        $tenant->refresh();
        $this->assertContains(
            $tenant->plan,
            ['starter', 'business'],
            "Bug 1.4: Tenant plan should have been updated to 'starter' or 'business'. " .
            "Got: " . $tenant->plan
        );
    }
}
