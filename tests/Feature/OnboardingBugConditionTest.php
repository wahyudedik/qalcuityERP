<?php

namespace Tests\Feature;

use App\Http\Controllers\OnboardingController;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SampleDataGeneratorService;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test — Save Industry HTTP 500 on Missing `skipped` Column
 *
 * EXPECTED: These tests MUST FAIL on unfixed code.
 * Failure confirms the bug exists:
 *   - SQLSTATE[42S22]: Column not found: 1054 Unknown column 'skipped' in 'field list'
 *   - SampleDataGeneratorService::getTemplates() returns [] for all industries
 *
 * When these tests PASS after the fix is applied, it confirms the bug is resolved.
 *
 * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5
 */
class OnboardingBugConditionTest extends TestCase
{
    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    /**
     * @test
     * Bug Condition 1.1 / 1.2 / 1.3:
     * POST /onboarding/save-industry with valid payload MUST return HTTP 200 JSON.
     *
     * WILL FAIL on unfixed code with:
     *   SQLSTATE[42S22]: Column not found: 1054 Unknown column 'skipped' in 'field list'
     *   → HTTP 500 HTML response instead of JSON
     *
     * Counterexample: saveIndustry({industry:'retail', business_size:'small'})
     *   returns HTTP 500 with HTML body instead of {"success": true, ...}
     *
     * Validates: Requirements 1.1, 1.2, 1.3
     */
    public function test_save_industry_returns_200_json_success(): void
    {
        $response = $this->postJson('/onboarding/save-industry', [
            'industry' => 'retail',
            'business_size' => 'small',
        ]);

        // On unfixed code this will fail: HTTP 500 with HTML body
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('next_step'));
    }

    /**
     * @test
     * Bug Condition 1.2 — skip() also writes `skipped` column:
     * POST /onboarding/skip MUST redirect to dashboard without SQL error.
     *
     * WILL FAIL on unfixed code with:
     *   SQLSTATE[42S22]: Column not found: 1054 Unknown column 'skipped' in 'field list'
     *
     * Validates: Requirements 1.1, 1.2
     */
    public function test_skip_onboarding_redirects_to_dashboard_without_sql_error(): void
    {
        // The skip() method writes `skipped = true` via updateOrCreate,
        // which triggers the same SQL error as saveIndustry().
        // We call the controller method directly via the route if registered,
        // otherwise invoke the controller directly.
        $controller = app(OnboardingController::class);

        $threwError = false;
        $errorMessage = '';
        $result = null;

        try {
            $result = $controller->skip();
        } catch (\Throwable $e) {
            $threwError = true;
            $errorMessage = $e->getMessage();
        }

        // On unfixed code this will fail with SQL error about 'skipped' column
        $this->assertFalse(
            $threwError,
            "Bug 1.2: skip() threw an error: {$errorMessage}. ".
            'Expected: redirect to dashboard without SQL error. '.
            "Counterexample: skip() → SQLSTATE[42S22]: Column not found: 1054 Unknown column 'skipped'"
        );

        // Verify it returns a redirect response
        $this->assertNotNull($result, 'skip() should return a redirect response');
        $this->assertEquals(302, $result->getStatusCode(), 'skip() should redirect (HTTP 302)');
    }

    /**
     * @test
     * Bug Condition 1.4 / 1.5:
     * SampleDataGeneratorService::getTemplates('retail') MUST return non-empty array.
     *
     * WILL FAIL on unfixed code because sample_data_templates table has no seeded data.
     * getTemplates() returns [] for all industries.
     *
     * Counterexample: getTemplates('retail') → [] (empty array, no templates available)
     *
     * Validates: Requirements 1.4, 1.5
     */
    public function test_get_templates_returns_non_empty_array_for_retail(): void
    {
        $service = app(SampleDataGeneratorService::class);
        $templates = $service->getTemplates('retail');

        // On unfixed code this will fail: returns [] because table is not seeded
        $this->assertNotEmpty(
            $templates,
            "Bug 1.4/1.5: SampleDataGeneratorService::getTemplates('retail') returned empty array. ".
            "Expected: at least one active template for 'retail' industry. ".
            "Counterexample: getTemplates('retail') → [] (sample_data_templates table not seeded)"
        );

        // Each template should be active
        foreach ($templates as $template) {
            $this->assertTrue(
                (bool) ($template['is_active'] ?? false),
                'Template should have is_active = true'
            );
        }
    }
}
