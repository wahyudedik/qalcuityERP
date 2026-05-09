<?php

namespace Tests\Feature\Preservation;

use App\Models\Doctor;
use App\Models\OperatingRoom;
use App\Models\Patient;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Database\Migrations\Migration;
use Tests\TestCase;

/**
 * Preservation Property Tests — Missing SurgerySchedule Model
 *
 * These tests verify behaviors that MUST NOT change after the fix is applied.
 * They MUST PASS on unfixed code to establish a baseline.
 *
 * Property 2: Preservation — Existing Models and Routes Unchanged
 *
 * For any input NOT involving the SurgerySchedule model, the fixed application
 * SHALL produce exactly the same behavior as the original code.
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
 */
class SurgerySchedulePreservationTest extends TestCase
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
    // Patient Model Preservation (Requirement 3.1)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — Patient model instantiation with valid attributes works correctly
     *
     * For all valid Patient attributes, the Patient model can be instantiated
     * and its attributes are accessible. This behavior must not change after the fix.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_pbt_patient_model_instantiation_with_random_attributes(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'John Doe',
                    'Jane Smith',
                    'Ahmad Rizki',
                    'Siti Nurhaliza',
                    'Budi Santoso',
                ]),
                Generators::elements(['male', 'female']),
                Generators::elements(['A', 'B', 'AB', 'O']),
                Generators::elements(['active', 'inactive', 'deceased']),
                Generators::elements(['single', 'married', 'divorced', 'widowed'])
            )
            ->then(function ($fullName, $gender, $bloodType, $status, $maritalStatus) {
                $attributes = [
                    'tenant_id' => $this->tenant->id,
                    'full_name' => $fullName,
                    'gender' => $gender,
                    'blood_type' => $bloodType,
                    'status' => $status,
                    'marital_status' => $maritalStatus,
                    'birth_date' => '1990-05-15',
                ];

                $patient = new Patient($attributes);

                $this->assertInstanceOf(
                    Patient::class,
                    $patient,
                    'Patient model must be instantiable with valid attributes.'
                );

                $this->assertEquals(
                    $fullName,
                    $patient->full_name,
                    'Patient full_name attribute must be accessible after instantiation.'
                );

                $this->assertEquals(
                    $gender,
                    $patient->gender,
                    'Patient gender attribute must be accessible after instantiation.'
                );

                $this->assertEquals(
                    $bloodType,
                    $patient->blood_type,
                    'Patient blood_type attribute must be accessible after instantiation.'
                );
            });
    }

    /**
     * @test
     * Property 2: Preservation — Patient model uses BelongsToTenant trait
     *
     * The Patient model must use the BelongsToTenant trait for tenant scoping.
     * This behavior must not change after the fix.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_patient_model_uses_belongs_to_tenant_trait(): void
    {
        $traits = class_uses_recursive(Patient::class);

        $this->assertContains(
            BelongsToTenant::class,
            $traits,
            'Requirement 3.1: Patient model must use BelongsToTenant trait for tenant scoping.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — Patient model relationships are defined correctly
     *
     * The Patient model must have its expected relationship methods defined.
     * This behavior must not change after the fix.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_patient_model_relationships_exist(): void
    {
        $patient = new Patient;

        $expectedRelationships = [
            'registeredBy',
            'primaryDoctor',
            'visits',
            'appointments',
            'medicalRecords',
            'admissions',
            'medicalBills',
        ];

        foreach ($expectedRelationships as $relationship) {
            $this->assertTrue(
                method_exists($patient, $relationship),
                "Requirement 3.1: Patient model must have '{$relationship}()' relationship method."
            );
        }
    }

    /**
     * @test
     * Property 2: Preservation — Patient model scopes work correctly
     *
     * For all valid search terms and blood types, Patient scopes produce
     * query builders without errors.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_pbt_patient_scopes_produce_valid_queries(): void
    {
        $this
            ->forAll(
                Generators::elements(['John', 'MR-2024', '08123', 'Ahmad']),
                Generators::elements(['A', 'B', 'AB', 'O'])
            )
            ->then(function ($searchTerm, $bloodType) {
                // Scopes should produce valid query builders without errors
                $searchQuery = Patient::search($searchTerm);
                $this->assertNotNull($searchQuery, 'Patient::search() must return a query builder.');

                $bloodTypeQuery = Patient::bloodType($bloodType);
                $this->assertNotNull($bloodTypeQuery, 'Patient::bloodType() must return a query builder.');

                $activeQuery = Patient::active();
                $this->assertNotNull($activeQuery, 'Patient::active() must return a query builder.');
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OperatingRoom Model Preservation (Requirement 3.2)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — OperatingRoom model instantiation and attribute access works
     *
     * For all valid OperatingRoom attributes, the model can be instantiated
     * and casts produce correct results.
     *
     * **Validates: Requirements 3.2**
     */
    public function test_pbt_operating_room_instantiation_and_casts(): void
    {
        $this
            ->forAll(
                Generators::elements(['OR-001', 'OR-002', 'OR-003', 'OR-004']),
                Generators::elements(['Main OR 1', 'Cardiac Suite', 'Neuro OR', 'Pediatric OR']),
                Generators::elements(['general', 'cardiac', 'orthopedic', 'neurological', 'pediatric', 'emergency', 'hybrid']),
                Generators::choose(1, 5),
                Generators::elements([true, false]),
                Generators::elements([true, false]),
                Generators::elements(['available', 'occupied', 'cleaning', 'maintenance', 'closed'])
            )
            ->then(function ($roomNumber, $roomName, $type, $capacity, $hasLaminarFlow, $isActive, $status) {
                $attributes = [
                    'room_number' => $roomNumber,
                    'room_name' => $roomName,
                    'type' => $type,
                    'capacity' => $capacity,
                    'has_laminar_flow' => $hasLaminarFlow,
                    'is_active' => $isActive,
                    'status' => $status,
                    'equipment' => ['scalpel', 'monitor'],
                    'specializations' => ['cardiac', 'general'],
                ];

                $room = new OperatingRoom($attributes);

                $this->assertInstanceOf(
                    OperatingRoom::class,
                    $room,
                    'OperatingRoom model must be instantiable with valid attributes.'
                );

                $this->assertEquals(
                    $roomName,
                    $room->room_name,
                    'OperatingRoom room_name attribute must be accessible.'
                );

                $this->assertEquals(
                    $type,
                    $room->type,
                    'OperatingRoom type attribute must be accessible.'
                );

                // Verify integer cast for capacity
                $this->assertIsInt(
                    $room->capacity,
                    'OperatingRoom capacity must be cast to integer.'
                );

                $this->assertEquals(
                    $capacity,
                    $room->capacity,
                    'OperatingRoom capacity value must be preserved after cast.'
                );
            });
    }

    /**
     * @test
     * Property 2: Preservation — OperatingRoom scopes produce valid queries
     *
     * The OperatingRoom model scopes (available, type, hasSpecialization) must
     * produce valid query builders without errors.
     *
     * **Validates: Requirements 3.2**
     */
    public function test_pbt_operating_room_scopes_work_correctly(): void
    {
        $this
            ->forAll(
                Generators::elements(['general', 'cardiac', 'orthopedic', 'neurological', 'pediatric', 'emergency', 'hybrid']),
                Generators::elements(['cardiac', 'general', 'orthopedic', 'neurological', 'pediatric'])
            )
            ->then(function ($type, $specialization) {
                // Available scope
                $availableQuery = OperatingRoom::available();
                $this->assertNotNull($availableQuery, 'OperatingRoom::available() must return a query builder.');

                // Type scope
                $typeQuery = OperatingRoom::type($type);
                $this->assertNotNull($typeQuery, 'OperatingRoom::type() must return a query builder.');

                // HasSpecialization scope
                $specQuery = OperatingRoom::hasSpecialization($specialization);
                $this->assertNotNull($specQuery, 'OperatingRoom::hasSpecialization() must return a query builder.');
            });
    }

    /**
     * @test
     * Property 2: Preservation — OperatingRoom array casts work correctly
     *
     * The OperatingRoom model's JSON/array casts (equipment, specializations,
     * availability_schedule) must work correctly.
     *
     * **Validates: Requirements 3.2**
     */
    public function test_pbt_operating_room_array_casts(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    ['scalpel', 'monitor', 'ventilator'],
                    ['defibrillator', 'anesthesia_machine'],
                    ['laser', 'microscope'],
                    [],
                ]),
                Generators::elements([
                    ['cardiac', 'general'],
                    ['orthopedic', 'neurological'],
                    ['pediatric'],
                    [],
                ])
            )
            ->then(function ($equipment, $specializations) {
                $room = new OperatingRoom([
                    'room_number' => 'OR-TEST',
                    'room_name' => 'Test Room',
                    'type' => 'general',
                    'equipment' => $equipment,
                    'specializations' => $specializations,
                ]);

                // Array casts should preserve the data
                $this->assertEquals(
                    $equipment,
                    $room->equipment,
                    'OperatingRoom equipment cast must preserve array data.'
                );

                $this->assertEquals(
                    $specializations,
                    $room->specializations,
                    'OperatingRoom specializations cast must preserve array data.'
                );
            });
    }

    /**
     * @test
     * Property 2: Preservation — OperatingRoom boolean casts work correctly
     *
     * **Validates: Requirements 3.2**
     */
    public function test_pbt_operating_room_boolean_casts(): void
    {
        $this
            ->forAll(
                Generators::elements([true, false, 1, 0]),
                Generators::elements([true, false, 1, 0]),
                Generators::elements([true, false, 1, 0]),
                Generators::elements([true, false, 1, 0])
            )
            ->then(function ($hasLaminarFlow, $hasHybridImaging, $isAvailable247, $isActive) {
                $room = new OperatingRoom([
                    'room_number' => 'OR-BOOL',
                    'room_name' => 'Bool Test',
                    'type' => 'general',
                    'has_laminar_flow' => $hasLaminarFlow,
                    'has_hybrid_imaging' => $hasHybridImaging,
                    'is_available_247' => $isAvailable247,
                    'is_active' => $isActive,
                ]);

                $this->assertIsBool(
                    $room->has_laminar_flow,
                    'OperatingRoom has_laminar_flow must be cast to boolean.'
                );

                $this->assertIsBool(
                    $room->has_hybrid_imaging,
                    'OperatingRoom has_hybrid_imaging must be cast to boolean.'
                );

                $this->assertIsBool(
                    $room->is_available_247,
                    'OperatingRoom is_available_247 must be cast to boolean.'
                );

                $this->assertIsBool(
                    $room->is_active,
                    'OperatingRoom is_active must be cast to boolean.'
                );
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Doctor Model Preservation (Requirement 3.1)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — Doctor model instantiation and relationships work correctly
     *
     * For all valid Doctor attributes, the model can be instantiated and
     * its relationships are defined.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_pbt_doctor_model_instantiation_with_random_attributes(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'General Practice',
                    'Cardiology',
                    'Dermatology',
                    'Pediatrics',
                    'Orthopedics',
                    'Neurology',
                ]),
                Generators::elements(['active', 'inactive', 'on_leave']),
                Generators::elements([true, false]),
                Generators::elements([true, false])
            )
            ->then(function ($specialization, $status, $acceptingPatients, $availableForTelemedicine) {
                $attributes = [
                    'tenant_id' => $this->tenant->id,
                    'specialization' => $specialization,
                    'status' => $status,
                    'accepting_patients' => $acceptingPatients,
                    'available_for_telemedicine' => $availableForTelemedicine,
                    'license_number' => 'LIC-'.uniqid(),
                ];

                $doctor = new Doctor($attributes);

                $this->assertInstanceOf(
                    Doctor::class,
                    $doctor,
                    'Doctor model must be instantiable with valid attributes.'
                );

                $this->assertEquals(
                    $specialization,
                    $doctor->specialization,
                    'Doctor specialization attribute must be accessible.'
                );

                $this->assertEquals(
                    $status,
                    $doctor->status,
                    'Doctor status attribute must be accessible.'
                );
            });
    }

    /**
     * @test
     * Property 2: Preservation — Doctor model uses BelongsToTenant trait
     *
     * **Validates: Requirements 3.1**
     */
    public function test_doctor_model_uses_belongs_to_tenant_trait(): void
    {
        $traits = class_uses_recursive(Doctor::class);

        $this->assertContains(
            BelongsToTenant::class,
            $traits,
            'Requirement 3.1: Doctor model must use BelongsToTenant trait for tenant scoping.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — Doctor model relationships are defined correctly
     *
     * **Validates: Requirements 3.1**
     */
    public function test_doctor_model_relationships_exist(): void
    {
        $doctor = new Doctor;

        $expectedRelationships = [
            'user',
            'appointments',
            'visits',
            'medicalRecords',
            'schedules',
        ];

        foreach ($expectedRelationships as $relationship) {
            $this->assertTrue(
                method_exists($doctor, $relationship),
                "Requirement 3.1: Doctor model must have '{$relationship}()' relationship method."
            );
        }
    }

    /**
     * @test
     * Property 2: Preservation — Doctor model scopes produce valid queries
     *
     * **Validates: Requirements 3.1**
     */
    public function test_pbt_doctor_scopes_produce_valid_queries(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'General Practice',
                    'Cardiology',
                    'Dermatology',
                    'Pediatrics',
                    'Orthopedics',
                ]),
                Generators::elements(['Dr. Smith', 'LIC-001', 'Cardiology'])
            )
            ->then(function ($specialization, $searchTerm) {
                $activeQuery = Doctor::active();
                $this->assertNotNull($activeQuery, 'Doctor::active() must return a query builder.');

                $specQuery = Doctor::specialization($specialization);
                $this->assertNotNull($specQuery, 'Doctor::specialization() must return a query builder.');

                $acceptingQuery = Doctor::acceptingPatients();
                $this->assertNotNull($acceptingQuery, 'Doctor::acceptingPatients() must return a query builder.');

                $teleQuery = Doctor::telemedicine();
                $this->assertNotNull($teleQuery, 'Doctor::telemedicine() must return a query builder.');

                $searchQuery = Doctor::search($searchTerm);
                $this->assertNotNull($searchQuery, 'Doctor::search() must return a query builder.');
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Healthcare Routes Preservation (Requirement 3.3)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — Non-surgery healthcare routes return valid responses
     *
     * Other healthcare routes (admissions, appointments) must continue to
     * return expected HTTP responses without interference from the fix.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_pbt_non_surgery_healthcare_routes_respond(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    '/healthcare/patients',
                    '/healthcare/appointments',
                    '/healthcare/inpatient/admissions',
                    '/healthcare/doctors',
                ])
            )
            ->then(function ($route) {
                $response = $this->get($route);

                // Routes should return a valid HTTP response (not a 500 server error)
                $this->assertNotEquals(
                    500,
                    $response->getStatusCode(),
                    "Requirement 3.3: Route '{$route}' must not return 500 error. "
                        .'Non-surgery healthcare routes must remain functional.'
                );

                // Should return 200 (success) or 302 (redirect, e.g., to login or dashboard)
                $this->assertContains(
                    $response->getStatusCode(),
                    [200, 302, 301, 403],
                    "Requirement 3.3: Route '{$route}' must return a valid response code (200, 301, 302, or 403), "
                        ."got: {$response->getStatusCode()}"
                );
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Migration Integrity Preservation (Requirement 3.4)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Property 2: Preservation — Hospital resource migration file exists and is valid
     *
     * The existing hospital resource migration file must exist and be loadable
     * without errors. This verifies migration integrity is preserved.
     *
     * **Validates: Requirements 3.4**
     */
    public function test_hospital_resource_migration_file_exists_and_is_valid(): void
    {
        $migrationPath = database_path('migrations/2026_04_08_000032_create_hospital_resource_tables.php');

        $this->assertFileExists(
            $migrationPath,
            'Requirement 3.4: Hospital resource migration file must exist.'
        );

        // Verify the migration file is loadable (valid PHP)
        $migration = require $migrationPath;

        $this->assertInstanceOf(
            Migration::class,
            $migration,
            'Requirement 3.4: Migration file must return a valid Migration instance.'
        );

        // Verify the migration has up() and down() methods
        $this->assertTrue(
            method_exists($migration, 'up'),
            'Requirement 3.4: Migration must have an up() method.'
        );

        $this->assertTrue(
            method_exists($migration, 'down'),
            'Requirement 3.4: Migration must have a down() method.'
        );
    }

    /**
     * @test
     * Property 2: Preservation — Migration file defines surgery_schedules table creation
     *
     * The migration file content must reference the surgery_schedules table creation.
     * This verifies the existing migration schema is not altered by the fix.
     *
     * **Validates: Requirements 3.4**
     */
    public function test_migration_defines_surgery_schedules_table(): void
    {
        $migrationPath = database_path('migrations/2026_04_08_000032_create_hospital_resource_tables.php');
        $content = file_get_contents($migrationPath);

        // Verify the migration creates the surgery_schedules table
        $this->assertStringContainsString(
            'surgery_schedules',
            $content,
            'Requirement 3.4: Migration must reference surgery_schedules table.'
        );

        // Verify key columns are defined in the migration
        $expectedColumnReferences = [
            'patient_id',
            'surgeon_id',
            'operating_room_id',
            'surgery_number',
            'scheduled_date',
            'procedure_name',
            'status',
            'priority',
        ];

        foreach ($expectedColumnReferences as $column) {
            $this->assertStringContainsString(
                $column,
                $content,
                "Requirement 3.4: Migration must define '{$column}' column for surgery_schedules."
            );
        }
    }

    /**
     * @test
     * Property 2: Preservation — Migration file defines operating_rooms table creation
     *
     * **Validates: Requirements 3.4**
     */
    public function test_migration_defines_operating_rooms_table(): void
    {
        $migrationPath = database_path('migrations/2026_04_08_000032_create_hospital_resource_tables.php');
        $content = file_get_contents($migrationPath);

        // Verify the migration creates the operating_rooms table
        $this->assertStringContainsString(
            'operating_rooms',
            $content,
            'Requirement 3.4: Migration must reference operating_rooms table.'
        );

        // Verify key columns are defined
        $expectedColumnReferences = [
            'room_number',
            'room_name',
            'type',
            'capacity',
            'equipment',
            'specializations',
            'has_laminar_flow',
            'has_hybrid_imaging',
            'is_active',
        ];

        foreach ($expectedColumnReferences as $column) {
            $this->assertStringContainsString(
                $column,
                $content,
                "Requirement 3.4: Migration must define '{$column}' column for operating_rooms."
            );
        }
    }
}
