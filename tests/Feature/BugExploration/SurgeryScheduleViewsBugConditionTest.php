<?php

namespace Tests\Feature\BugExploration;

use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test — Missing Surgery Schedule Blade Views
 *
 * EXPECTED: These tests MUST FAIL on unfixed code.
 * Failure confirms the bug exists:
 *
 *   The SurgeryScheduleController references four Blade view files that do not exist:
 *   - healthcare.surgery-schedules.index → resources/views/healthcare/surgery-schedules/index.blade.php
 *   - healthcare.surgery-schedules.create → resources/views/healthcare/surgery-schedules/create.blade.php
 *   - healthcare.surgery-schedules.show → resources/views/healthcare/surgery-schedules/show.blade.php
 *   - healthcare.surgery-schedule.edit → resources/views/healthcare/surgery-schedule/edit.blade.php
 *
 *   Any GET request to these routes throws:
 *   InvalidArgumentException: View [healthcare.surgery-schedules.*] not found
 *
 * When these tests PASS after the fix is applied, it confirms the bug is resolved.
 *
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
 */
class SurgeryScheduleViewsBugConditionTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     * Bug Condition — Index view file must exist on disk
     *
     * WILL FAIL on unfixed code:
     *   The file resources/views/healthcare/surgery-schedules/index.blade.php does not exist.
     *   This causes InvalidArgumentException when the controller calls
     *   view('healthcare.surgery-schedules.index')
     *
     * Counterexample: View file healthcare/surgery-schedules/index.blade.php not found on disk
     *
     * Validates: Requirements 1.1
     */
    public function test_index_view_file_exists(): void
    {
        $viewPath = resource_path('views/healthcare/surgery-schedules/index.blade.php');

        $this->assertFileExists(
            $viewPath,
            'Bug Condition: View file resources/views/healthcare/surgery-schedules/index.blade.php does not exist. '
                . 'The SurgeryScheduleController::index() calls view("healthcare.surgery-schedules.index") '
                . 'which throws InvalidArgumentException: View [healthcare.surgery-schedules.index] not found. '
                . 'Counterexample: GET /healthcare/surgery-schedules → 500 (View not found)'
        );
    }

    /**
     * @test
     * Bug Condition — Create view file must exist on disk
     *
     * WILL FAIL on unfixed code:
     *   The file resources/views/healthcare/surgery-schedules/create.blade.php does not exist.
     *   This causes InvalidArgumentException when the controller calls
     *   view('healthcare.surgery-schedules.create')
     *
     * Counterexample: View file healthcare/surgery-schedules/create.blade.php not found on disk
     *
     * Validates: Requirements 1.2
     */
    public function test_create_view_file_exists(): void
    {
        $viewPath = resource_path('views/healthcare/surgery-schedules/create.blade.php');

        $this->assertFileExists(
            $viewPath,
            'Bug Condition: View file resources/views/healthcare/surgery-schedules/create.blade.php does not exist. '
                . 'The SurgeryScheduleController::create() calls view("healthcare.surgery-schedules.create") '
                . 'which throws InvalidArgumentException: View [healthcare.surgery-schedules.create] not found. '
                . 'Counterexample: GET /healthcare/surgery-schedules/create → 500 (View not found)'
        );
    }

    /**
     * @test
     * Bug Condition — Show view file must exist on disk
     *
     * WILL FAIL on unfixed code:
     *   The file resources/views/healthcare/surgery-schedules/show.blade.php does not exist.
     *   This causes InvalidArgumentException when the controller calls
     *   view('healthcare.surgery-schedules.show')
     *
     * Counterexample: View file healthcare/surgery-schedules/show.blade.php not found on disk
     *
     * Validates: Requirements 1.3
     */
    public function test_show_view_file_exists(): void
    {
        $viewPath = resource_path('views/healthcare/surgery-schedules/show.blade.php');

        $this->assertFileExists(
            $viewPath,
            'Bug Condition: View file resources/views/healthcare/surgery-schedules/show.blade.php does not exist. '
                . 'The SurgeryScheduleController::show() calls view("healthcare.surgery-schedules.show") '
                . 'which throws InvalidArgumentException: View [healthcare.surgery-schedules.show] not found. '
                . 'Counterexample: GET /healthcare/surgery-schedules/{id} → 500 (View not found)'
        );
    }

    /**
     * @test
     * Bug Condition — Edit view file must exist on disk (singular path)
     *
     * WILL FAIL on unfixed code:
     *   The file resources/views/healthcare/surgery-schedule/edit.blade.php does not exist.
     *   Note: The controller uses singular 'surgery-schedule' (not plural) for the edit view.
     *   This causes InvalidArgumentException when the controller calls
     *   view('healthcare.surgery-schedule.edit')
     *
     * Counterexample: View file healthcare/surgery-schedule/edit.blade.php not found on disk
     *
     * Validates: Requirements 1.4
     */
    public function test_edit_view_file_exists(): void
    {
        $viewPath = resource_path('views/healthcare/surgery-schedule/edit.blade.php');

        $this->assertFileExists(
            $viewPath,
            'Bug Condition: View file resources/views/healthcare/surgery-schedule/edit.blade.php does not exist. '
                . 'The SurgeryScheduleController::edit() calls view("healthcare.surgery-schedule.edit") '
                . '(note: singular "surgery-schedule" not plural) '
                . 'which throws InvalidArgumentException: View [healthcare.surgery-schedule.edit] not found. '
                . 'Counterexample: GET /healthcare/surgery-schedules/{id}/edit → 500 (View not found)'
        );
    }

    /**
     * @test
     * Bug Condition — Laravel view finder can resolve all four surgery schedule views
     *
     * WILL FAIL on unfixed code:
     *   The view finder throws InvalidArgumentException for each missing view.
     *
     * Counterexample: view()->getFinder()->find('healthcare.surgery-schedules.index') throws exception
     *
     * Validates: Requirements 1.1, 1.2, 1.3, 1.4
     */
    public function test_laravel_view_finder_resolves_all_views(): void
    {
        $views = [
            'healthcare.surgery-schedules.index',
            'healthcare.surgery-schedules.create',
            'healthcare.surgery-schedules.show',
            'healthcare.surgery-schedule.edit',
        ];

        $missingViews = [];

        foreach ($views as $viewName) {
            try {
                view()->getFinder()->find($viewName);
            } catch (\InvalidArgumentException $e) {
                $missingViews[] = $viewName . ' → ' . $e->getMessage();
            }
        }

        $this->assertEmpty(
            $missingViews,
            'Bug Condition: The following views cannot be resolved by Laravel view finder: '
                . "\n" . implode("\n", $missingViews)
                . "\n\nCounterexample: Controller calls view() with paths that have no corresponding Blade file."
        );
    }

    /**
     * @test
     * Property-Based: Bug Condition — All four surgery schedule view files must exist
     *
     * Uses Eris to generate random selections across the four affected view paths
     * and verify that each corresponding Blade file exists on disk.
     *
     * WILL FAIL on unfixed code:
     *   None of the four Blade view files exist on disk.
     *
     * Counterexample: Any of the four view files is missing from resources/views/
     *
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     */
    public function test_pbt_all_surgery_schedule_view_files_exist(): void
    {
        $viewPaths = [
            'views/healthcare/surgery-schedules/index.blade.php',
            'views/healthcare/surgery-schedules/create.blade.php',
            'views/healthcare/surgery-schedules/show.blade.php',
            'views/healthcare/surgery-schedule/edit.blade.php',
        ];

        $viewNames = [
            'healthcare.surgery-schedules.index',
            'healthcare.surgery-schedules.create',
            'healthcare.surgery-schedules.show',
            'healthcare.surgery-schedule.edit',
        ];

        $routeDescriptions = [
            'GET /healthcare/surgery-schedules (index)',
            'GET /healthcare/surgery-schedules/create (create)',
            'GET /healthcare/surgery-schedules/{id} (show)',
            'GET /healthcare/surgery-schedules/{id}/edit (edit)',
        ];

        // Route indices: 0=index, 1=create, 2=show, 3=edit
        $this
            ->forAll(
                Generators::choose(0, 3)
            )
            ->then(function (int $routeIndex) use ($viewPaths, $viewNames, $routeDescriptions) {
                $fullPath = resource_path($viewPaths[$routeIndex]);

                $this->assertFileExists(
                    $fullPath,
                    "Bug Condition: View file '{$viewPaths[$routeIndex]}' does not exist. "
                        . "Route: {$routeDescriptions[$routeIndex]} "
                        . "calls view('{$viewNames[$routeIndex]}') which throws "
                        . "InvalidArgumentException: View [{$viewNames[$routeIndex]}] not found. "
                        . "Counterexample: {$routeDescriptions[$routeIndex]} → 500 (View not found)"
                );
            });
    }
}
