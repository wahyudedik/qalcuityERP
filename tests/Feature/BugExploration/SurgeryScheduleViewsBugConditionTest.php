<?php

namespace Tests\Feature\BugExploration;

use App\Models\Doctor;
use App\Models\OperatingRoom;
use App\Models\Patient;
use App\Models\SurgerySchedule;
use App\Models\Tenant;
use App\Models\User;
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
 *   - healthcare.surgery-schedules.index
 *   - healthcare.surgery-schedules.create
 *   - healthcare.surgery-schedules.show
 *   - healthcare.surgery-schedule.edit (singular path)
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
     * Bug Condition — GET /healthcare/surgery-schedules (index) returns HTTP 200
     *
     * WILL FAIL on unfixed code:
     *   InvalidArgumentException: View [healthcare.surgery-schedules.index] not found.
     *   The controller calls view('healthcare.surgery-schedules.index') but the
     *   Blade file does not exist at resources/views/healthcare/surgery-schedules/index.blade.php
     *
     * Counterexample: GET /healthcare/surgery-schedules → 500 (View not found)
     *
     * Validates: Requirements 1.1
     */
    public function test_index_route_returns_200(): void
    {
        $response = $this->get('/healthcare/surgery-schedules');

        $response->assertStatus(200);
    }

    /**
     * @test
     * Bug Condition — GET /healthcare/surgery-schedules/create returns HTTP 200
     *
     * WILL FAIL on unfixed code:
     *   InvalidArgumentException: View [healthcare.surgery-schedules.create] not found.
     *   The controller calls view('healthcare.surgery-schedules.create') but the
     *   Blade file does not exist at resources/views/healthcare/surgery-schedules/create.blade.php
     *
     * Counterexample: GET /healthcare/surgery-schedules/create → 500 (View not found)
     *
     * Validates: Requirements 1.2
     */
    public function test_create_route_returns_200(): void
    {
        // Create prerequisite data for the create form dropdowns
        Patient::create([
            'tenant_id' => $this->tenant->id,
            'full_name' => 'Test Patient',
            'medical_record_number' => 'MRN-' . uniqid(),
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'is_active' => true,
        ]);

        Doctor::create([
            'tenant_id' => $this->tenant->id,
            'doctor_number' => 'DOC-' . uniqid(),
            'license_number' => 'LIC-' . uniqid(),
            'specialization' => 'Surgery',
            'status' => 'active',
            'is_active' => true,
        ]);

        OperatingRoom::create([
            'room_number' => 'OR-' . uniqid(),
            'room_name' => 'Operating Room 1',
            'type' => 'general',
            'status' => 'available',
            'is_active' => true,
        ]);

        $response = $this->get('/healthcare/surgery-schedules/create');

        $response->assertStatus(200);
    }

    /**
     * @test
     * Bug Condition — GET /healthcare/surgery-schedules/{id} (show) returns HTTP 200
     *
     * WILL FAIL on unfixed code:
     *   InvalidArgumentException: View [healthcare.surgery-schedules.show] not found.
     *   The controller calls view('healthcare.surgery-schedules.show') but the
     *   Blade file does not exist at resources/views/healthcare/surgery-schedules/show.blade.php
     *
     * Counterexample: GET /healthcare/surgery-schedules/1 → 500 (View not found)
     *
     * Validates: Requirements 1.3
     */
    public function test_show_route_returns_200(): void
    {
        $schedule = SurgerySchedule::create([
            'tenant_id' => $this->tenant->id,
            'surgery_number' => 'SRG-' . uniqid(),
            'surgery_type' => 'Appendectomy',
            'scheduled_date' => now()->addDays(3),
            'status' => 'scheduled',
        ]);

        $response = $this->get('/healthcare/surgery-schedules/' . $schedule->id);

        $response->assertStatus(200);
    }

    /**
     * @test
     * Bug Condition — GET /healthcare/surgery-schedules/{id}/edit returns HTTP 200
     *
     * WILL FAIL on unfixed code:
     *   InvalidArgumentException: View [healthcare.surgery-schedule.edit] not found.
     *   The controller calls view('healthcare.surgery-schedule.edit') (singular path) but the
     *   Blade file does not exist at resources/views/healthcare/surgery-schedule/edit.blade.php
     *
     * Counterexample: GET /healthcare/surgery-schedules/1/edit → 500 (View not found)
     *
     * Validates: Requirements 1.4
     */
    public function test_edit_route_returns_200(): void
    {
        $schedule = SurgerySchedule::create([
            'tenant_id' => $this->tenant->id,
            'surgery_number' => 'SRG-' . uniqid(),
            'surgery_type' => 'Cholecystectomy',
            'scheduled_date' => now()->addDays(5),
            'status' => 'scheduled',
        ]);

        $response = $this->get('/healthcare/surgery-schedules/' . $schedule->id . '/edit');

        $response->assertStatus(200);
    }

    /**
     * @test
     * Property-Based: Bug Condition — All four surgery schedule view routes fail
     *
     * Uses Eris to generate random route selections across the four affected routes
     * and verify that each returns HTTP 200 (expected behavior after fix).
     *
     * WILL FAIL on unfixed code:
     *   All four routes throw InvalidArgumentException because the Blade view files
     *   do not exist on disk.
     *
     * Counterexample: Any of the four routes returns non-200 status
     *
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     */
    public function test_pbt_all_surgery_schedule_view_routes_return_200(): void
    {
        // Create prerequisite data for all routes
        $patient = Patient::create([
            'tenant_id' => $this->tenant->id,
            'full_name' => 'PBT Patient',
            'medical_record_number' => 'MRN-PBT-' . uniqid(),
            'gender' => 'female',
            'birth_date' => '1985-05-15',
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'tenant_id' => $this->tenant->id,
            'doctor_number' => 'DOC-PBT-' . uniqid(),
            'license_number' => 'LIC-PBT-' . uniqid(),
            'specialization' => 'General Surgery',
            'status' => 'active',
            'is_active' => true,
        ]);

        $operatingRoom = OperatingRoom::create([
            'room_number' => 'OR-PBT-' . uniqid(),
            'room_name' => 'PBT Operating Room',
            'type' => 'general',
            'status' => 'available',
            'is_active' => true,
        ]);

        $schedule = SurgerySchedule::create([
            'tenant_id' => $this->tenant->id,
            'patient_id' => $patient->id,
            'surgeon_id' => $doctor->id,
            'operating_room_id' => $operatingRoom->id,
            'surgery_number' => 'SRG-PBT-' . uniqid(),
            'surgery_type' => 'Hernia Repair',
            'scheduled_date' => now()->addDays(7),
            'status' => 'scheduled',
            'priority' => 'elective',
        ]);

        // Route indices: 0=index, 1=create, 2=show, 3=edit
        $this
            ->forAll(
                Generators::choose(0, 3)
            )
            ->then(function (int $routeIndex) use ($schedule) {
                $routes = [
                    '/healthcare/surgery-schedules',
                    '/healthcare/surgery-schedules/create',
                    '/healthcare/surgery-schedules/' . $schedule->id,
                    '/healthcare/surgery-schedules/' . $schedule->id . '/edit',
                ];

                $routeNames = [
                    'healthcare.surgery-schedules.index',
                    'healthcare.surgery-schedules.create',
                    'healthcare.surgery-schedules.show',
                    'healthcare.surgery-schedules.edit',
                ];

                $response = $this->get($routes[$routeIndex]);

                $this->assertEquals(
                    200,
                    $response->getStatusCode(),
                    "Bug Condition: GET {$routes[$routeIndex]} should return HTTP 200 "
                        . "but returned {$response->getStatusCode()}. "
                        . "Route '{$routeNames[$routeIndex]}' view file is missing. "
                        . "Counterexample: GET {$routes[$routeIndex]} → {$response->getStatusCode()} "
                        . "(InvalidArgumentException: View not found)"
                );
            });
    }
}
