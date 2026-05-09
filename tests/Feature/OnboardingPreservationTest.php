<?php

namespace Tests\Feature;

use App\Models\OnboardingProfile;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SampleDataGeneratorService;
use Tests\TestCase;

/**
 * Preservation Property Tests — Non-Buggy Input Behavior Unchanged
 *
 * These tests verify that the fix does NOT break existing behaviors
 * that were working before the bug was introduced.
 *
 * All tests MUST PASS on fixed code.
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6
 */
class OnboardingPreservationTest extends TestCase
{
    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
    }

    /**
     * @test
     * Preservation 3.4 — Invalid industry values must still return HTTP 422.
     *
     * The fix must NOT change validation behavior for invalid inputs.
     *
     * Validates: Requirement 3.4
     */
    public function test_invalid_industry_returns_422(): void
    {
        $this->actingAs($this->user);

        // 'mining' is not in the allowed enum
        $response = $this->postJson('/onboarding/save-industry', [
            'industry' => 'mining',
            'business_size' => 'small',
        ]);
        $response->assertStatus(422);

        // 'xyz' is also not valid
        $response2 = $this->postJson('/onboarding/save-industry', [
            'industry' => 'xyz',
            'business_size' => 'small',
        ]);
        $response2->assertStatus(422);
    }

    /**
     * @test
     * Preservation 3.5 — Unauthenticated request to /onboarding/save-industry must redirect to login.
     *
     * The auth middleware must remain active after the fix.
     *
     * Validates: Requirement 3.5
     */
    public function test_unauthenticated_request_redirects_to_login(): void
    {
        // No actingAs — unauthenticated
        $response = $this->postJson('/onboarding/save-industry', [
            'industry' => 'retail',
            'business_size' => 'small',
        ]);

        // JSON requests get 401, regular requests get redirect to login
        $response->assertStatus(401);
    }

    /**
     * @test
     * Preservation 3.1 — User with completed onboarding must NOT be redirected to wizard.
     *
     * When tenant->onboarding_completed is true, GET /dashboard should NOT redirect
     * to the onboarding wizard. The user should reach the dashboard directly.
     *
     * Validates: Requirement 3.1
     */
    public function test_completed_onboarding_user_goes_to_dashboard_not_wizard(): void
    {
        // Mark tenant as onboarding completed
        $this->tenant->update(['onboarding_completed' => true]);

        // Also create a profile with completed_at set
        OnboardingProfile::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'industry' => 'retail',
            'business_size' => 'small',
            'completed_at' => now(),
        ]);

        $this->actingAs($this->user);

        // GET /onboarding/wizard should return 200 (not redirect back to wizard in a loop)
        $wizardResponse = $this->get('/onboarding/wizard');
        $wizardResponse->assertStatus(200);

        // GET /dashboard should NOT redirect to onboarding.index (wizard flow)
        $dashResponse = $this->get('/dashboard');

        // If it redirects, it must NOT be to the onboarding wizard
        if ($dashResponse->getStatusCode() === 302) {
            $location = $dashResponse->headers->get('Location');
            $this->assertStringNotContainsString(
                '/onboarding',
                $location,
                "Dashboard should NOT redirect to onboarding when onboarding is completed. Redirected to: {$location}"
            );
        } else {
            $dashResponse->assertStatus(200);
        }
    }

    /**
     * @test
     * Preservation 3.6 — generateSampleData() for 'retail' must return success with records_created > 0.
     *
     * The seeder fix must ensure sample data generation works for supported industries.
     *
     * Validates: Requirement 3.6
     */
    public function test_generate_sample_data_for_retail_returns_success(): void
    {
        $service = app(SampleDataGeneratorService::class);

        $result = $service->generateForIndustry(
            'retail',
            $this->tenant->id,
            $this->user->id
        );

        $this->assertTrue(
            $result['success'],
            'generateForIndustry(retail) should return success: true. Got: '.json_encode($result)
        );

        $this->assertGreaterThan(
            0,
            $result['records_created'],
            'generateForIndustry(retail) should create at least 1 record'
        );
    }
}
