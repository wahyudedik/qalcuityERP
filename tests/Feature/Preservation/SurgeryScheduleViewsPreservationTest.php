<?php

namespace Tests\Feature\Preservation;

use App\Models\Tenant;
use App\Models\User;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Preservation Property Tests — Surgery Schedule Views Fix
 *
 * These tests verify behaviors that MUST NOT change after the fix is applied.
 * They MUST PASS on unfixed code to establish a baseline.
 *
 * Property 2: Preservation — Existing Healthcare Views and API Responses Unchanged
 *
 * For any input NOT involving the four missing surgery schedule views,
 * the fixed application SHALL produce exactly the same behavior as the original code.
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
 */
class SurgeryScheduleViewsPreservationTest extends TestCase
{
    use TestTrait;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Existing Surgeries Views Preservation (Requirement 3.1, 3.4)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — Existing surgeries index view file exists on disk
     *
     * The existing resources/views/healthcare/surgeries/index.blade.php must
     * continue to exist and be a valid Blade template after the fix.
     *
     * **Validates: Requirements 3.1, 3.4**
     */
    public function test_existing_surgeries_index_view_file_exists(): void
    {
        $viewPath = resource_path('views/healthcare/surgeries/index.blade.php');

        $this->assertFileExists(
            $viewPath,
            'Preservation: Existing surgeries index view must remain on disk at '
                . 'resources/views/healthcare/surgeries/index.blade.php'
        );

        $content = file_get_contents($viewPath);
        $this->assertStringContainsString(
            'x-app-layout',
            $content,
            'Preservation: Existing surgeries index view must use <x-app-layout> wrapper.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — Existing surgeries create view file exists on disk
     *
     * The existing resources/views/healthcare/surgeries/create.blade.php must
     * continue to exist and be a valid Blade template after the fix.
     *
     * **Validates: Requirements 3.1, 3.4**
     */
    public function test_existing_surgeries_create_view_file_exists(): void
    {
        $viewPath = resource_path('views/healthcare/surgeries/create.blade.php');

        $this->assertFileExists(
            $viewPath,
            'Preservation: Existing surgeries create view must remain on disk at '
                . 'resources/views/healthcare/surgeries/create.blade.php'
        );

        $content = file_get_contents($viewPath);
        $this->assertStringContainsString(
            'x-app-layout',
            $content,
            'Preservation: Existing surgeries create view must use <x-app-layout> wrapper.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — PBT: Existing surgeries view files are valid Blade templates
     *
     * For all existing surgeries view files, the file must exist, contain valid
     * Blade syntax markers, and use the established layout pattern.
     *
     * **Validates: Requirements 3.1, 3.4**
     */
    public function test_pbt_existing_surgeries_views_are_valid_blade_templates(): void
    {
        $viewFiles = [
            'views/healthcare/surgeries/index.blade.php',
            'views/healthcare/surgeries/create.blade.php',
            'views/healthcare/surgeries/edit.blade.php',
        ];

        $this
            ->forAll(
                Generators::choose(0, count($viewFiles) - 1)
            )
            ->then(function (int $fileIndex) use ($viewFiles) {
                $fullPath = resource_path($viewFiles[$fileIndex]);

                $this->assertFileExists(
                    $fullPath,
                    "Preservation: View file '{$viewFiles[$fileIndex]}' must exist on disk. "
                        . 'Existing surgeries views must not be removed or renamed by the fix.'
                );

                $content = file_get_contents($fullPath);

                $this->assertStringContainsString(
                    'x-app-layout',
                    $content,
                    "Preservation: View '{$viewFiles[$fileIndex]}' must use <x-app-layout> wrapper."
                );

                // Verify it's not empty
                $this->assertGreaterThan(
                    50,
                    strlen($content),
                    "Preservation: View '{$viewFiles[$fileIndex]}' must have meaningful content (not empty)."
                );
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JSON API Endpoints Preservation (Requirement 3.3)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — Start action returns JSON response (not a view)
     *
     * The SurgeryScheduleController::start() method returns response()->json()
     * and does not call view(). This behavior must be preserved after the fix.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_surgery_schedule_start_returns_json_not_view(): void
    {
        $controllerPath = app_path('Http/Controllers/Healthcare/SurgeryScheduleController.php');
        $content = file_get_contents($controllerPath);

        // Extract the start method body
        $this->assertMatchesRegularExpression(
            '/public\s+function\s+start\b.*?return\s+response\(\)->json\(/s',
            $content,
            'Preservation: SurgeryScheduleController::start() must return response()->json(). '
                . 'This JSON API endpoint must not be changed to return a view.'
        );

        // Verify it does NOT call view()
        // Extract just the start method
        preg_match('/public\s+function\s+start\b[^{]*\{(.*?)^\s{4}\}/ms', $content, $matches);
        $startMethodBody = $matches[1] ?? '';

        $this->assertStringNotContainsString(
            'return view(',
            $startMethodBody,
            'Preservation: SurgeryScheduleController::start() must NOT return a view. '
                . 'It must continue to return JSON.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — Complete action returns JSON response (not a view)
     *
     * The SurgeryScheduleController::complete() method returns response()->json()
     * and does not call view(). This behavior must be preserved after the fix.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_surgery_schedule_complete_returns_json_not_view(): void
    {
        $controllerPath = app_path('Http/Controllers/Healthcare/SurgeryScheduleController.php');
        $content = file_get_contents($controllerPath);

        // Extract the complete method body
        $this->assertMatchesRegularExpression(
            '/public\s+function\s+complete\b.*?return\s+response\(\)->json\(/s',
            $content,
            'Preservation: SurgeryScheduleController::complete() must return response()->json(). '
                . 'This JSON API endpoint must not be changed to return a view.'
        );

        // Verify it does NOT call view()
        preg_match('/public\s+function\s+complete\b[^{]*\{(.*?)^\s{4}\}/ms', $content, $matches);
        $completeMethodBody = $matches[1] ?? '';

        $this->assertStringNotContainsString(
            'return view(',
            $completeMethodBody,
            'Preservation: SurgeryScheduleController::complete() must NOT return a view. '
                . 'It must continue to return JSON.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — Destroy action returns JSON response (not a view)
     *
     * The SurgeryScheduleController::destroy() method returns response()->json()
     * and does not call view(). This behavior must be preserved after the fix.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_surgery_schedule_destroy_returns_json_not_view(): void
    {
        $controllerPath = app_path('Http/Controllers/Healthcare/SurgeryScheduleController.php');
        $content = file_get_contents($controllerPath);

        // Extract the destroy method body
        $this->assertMatchesRegularExpression(
            '/public\s+function\s+destroy\b.*?return\s+response\(\)->json\(/s',
            $content,
            'Preservation: SurgeryScheduleController::destroy() must return response()->json(). '
                . 'This JSON API endpoint must not be changed to return a view.'
        );

        // Verify it does NOT call view()
        preg_match('/public\s+function\s+destroy\b[^{]*\{(.*?)^\s{4}\}/ms', $content, $matches);
        $destroyMethodBody = $matches[1] ?? '';

        $this->assertStringNotContainsString(
            'return view(',
            $destroyMethodBody,
            'Preservation: SurgeryScheduleController::destroy() must NOT return a view. '
                . 'It must continue to return JSON.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — PBT: All JSON API actions use response()->json()
     *
     * For all surgery schedule API actions (start, complete, destroy, updateStatus),
     * the controller method must return response()->json() and must NOT return a view.
     * This ensures the fix (adding view files) does not accidentally change API behavior.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_pbt_json_api_actions_use_json_response(): void
    {
        $controllerPath = app_path('Http/Controllers/Healthcare/SurgeryScheduleController.php');
        $content = file_get_contents($controllerPath);

        $jsonActions = ['start', 'complete', 'destroy', 'updateStatus'];

        $this
            ->forAll(
                Generators::elements($jsonActions)
            )
            ->then(function (string $action) use ($content) {
                // Verify the method exists
                $this->assertMatchesRegularExpression(
                    '/public\s+function\s+' . preg_quote($action) . '\b/s',
                    $content,
                    "Preservation: SurgeryScheduleController::{$action}() method must exist."
                );

                // Verify it returns JSON
                $this->assertMatchesRegularExpression(
                    '/public\s+function\s+' . preg_quote($action) . '\b.*?response\(\)->json\(/s',
                    $content,
                    "Preservation: SurgeryScheduleController::{$action}() must return response()->json(). "
                        . 'JSON API endpoints must not be changed to return views.'
                );
            });
    }

    /**
     * @test
     * Property 2: Preservation — Surgery schedule routes for start/complete are registered
     *
     * The POST routes for surgery-schedules/{schedule}/start and
     * surgery-schedules/{schedule}/complete must be registered in the healthcare routes.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_surgery_schedule_api_routes_are_registered(): void
    {
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();

        $expectedRoutes = [
            'healthcare.surgery-schedules.start',
            'healthcare.surgery-schedules.complete',
            'healthcare.surgery-schedules.destroy',
        ];

        foreach ($expectedRoutes as $routeName) {
            $route = $routeCollection->getByName($routeName);

            $this->assertNotNull(
                $route,
                "Preservation: Route '{$routeName}' must be registered. "
                    . 'JSON API routes must remain available after the fix.'
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Other Healthcare Routes Preservation (Requirement 3.1)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — PBT: Other healthcare routes respond without 500 errors
     *
     * For all non-surgery-schedule healthcare routes, the response must not be
     * a 500 server error. These routes must remain functional after the fix.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_pbt_other_healthcare_routes_respond_correctly(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    '/healthcare/patients',
                    '/healthcare/appointments',
                    '/healthcare/doctors',
                    '/healthcare/inpatient/admissions',
                ])
            )
            ->then(function (string $route) {
                $response = $this->get($route);

                $this->assertNotEquals(
                    500,
                    $response->getStatusCode(),
                    "Preservation: Route '{$route}' must not return 500 error. "
                        . 'Other healthcare routes must remain functional after the fix.'
                );

                $this->assertContains(
                    $response->getStatusCode(),
                    [200, 302, 301, 403],
                    "Preservation: Route '{$route}' must return a valid response code (200, 301, 302, or 403), "
                        . "got: {$response->getStatusCode()}"
                );
            });
    }
}
