<?php

namespace Tests\Feature\BugExploration;

use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Traits\BelongsToTenant;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test — Missing SurgerySchedule Model
 *
 * EXPECTED: These tests MUST FAIL on unfixed code.
 * Failure confirms the bug exists:
 *
 *   The application throws a fatal error (`Class "App\Models\SurgerySchedule" not found`)
 *   when any code path attempts to resolve or instantiate `App\Models\SurgerySchedule`.
 *   The model file was never created despite the controller and migration existing.
 *
 * When these tests PASS after the fix is applied, it confirms the bug is resolved.
 *
 * **Validates: Requirements 1.1, 1.2, 1.3, 2.1, 2.2, 2.3, 2.4, 2.5**
 */
class SurgeryScheduleBugConditionTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     * Bug Condition — SurgerySchedule class must exist
     *
     * WILL FAIL on unfixed code:
     *   class_exists('App\Models\SurgerySchedule') returns false because
     *   the model file was never created.
     *
     * Counterexample: class_exists('App\Models\SurgerySchedule') === false
     *
     * Validates: Requirements 1.1, 1.2, 2.1, 2.2
     */
    public function test_surgery_schedule_class_exists(): void
    {
        $this->assertTrue(
            class_exists(SurgerySchedule::class),
            'Bug Condition: Class "App\Models\SurgerySchedule" not found. '
                .'The model file app/Models/SurgerySchedule.php does not exist. '
                .'Counterexample: class_exists("App\Models\SurgerySchedule") returns false.'
        );
    }

    /**
     * @test
     * Bug Condition — SurgerySchedule must be an Eloquent Model instance
     *
     * WILL FAIL on unfixed code:
     *   Attempting `new SurgerySchedule()` throws a fatal error because
     *   the class does not exist.
     *
     * Counterexample: `new App\Models\SurgerySchedule()` throws fatal error
     *
     * Validates: Requirements 1.1, 1.2, 2.2
     */
    public function test_surgery_schedule_is_eloquent_model(): void
    {
        $model = new SurgerySchedule;

        $this->assertInstanceOf(
            Model::class,
            $model,
            'Bug Condition: SurgerySchedule must be an instance of Eloquent Model. '
                .'Counterexample: new SurgerySchedule() is not an Eloquent Model or throws fatal error.'
        );
    }

    /**
     * @test
     * Bug Condition — SurgerySchedule must use BelongsToTenant trait
     *
     * WILL FAIL on unfixed code:
     *   Cannot check traits on a non-existent class.
     *
     * Counterexample: SurgerySchedule class does not exist, cannot verify tenant scoping
     *
     * Validates: Requirements 2.5
     */
    public function test_surgery_schedule_uses_belongs_to_tenant_trait(): void
    {
        $this->assertTrue(
            class_exists(SurgerySchedule::class),
            'Prerequisite: SurgerySchedule class must exist to check traits.'
        );

        $traits = class_uses_recursive(SurgerySchedule::class);

        $this->assertContains(
            BelongsToTenant::class,
            $traits,
            'Bug Condition: SurgerySchedule must use BelongsToTenant trait for tenant scoping. '
                .'Counterexample: Model does not use BelongsToTenant, tenant isolation will fail.'
        );
    }

    /**
     * @test
     * Bug Condition — SurgerySchedule query must target surgery_schedules table
     *
     * WILL FAIL on unfixed code:
     *   Cannot call SurgerySchedule::query() on a non-existent class.
     *
     * Counterexample: SurgerySchedule::query() throws fatal error (class not found)
     *
     * Validates: Requirements 2.2
     */
    public function test_surgery_schedule_targets_correct_table(): void
    {
        $model = new SurgerySchedule;

        $this->assertEquals(
            'surgery_schedules',
            $model->getTable(),
            'Bug Condition: SurgerySchedule must target the "surgery_schedules" table. '
                .'Counterexample: Model targets wrong table or class does not exist.'
        );
    }

    /**
     * @test
     * Bug Condition — SurgerySchedule must have relationship methods
     *
     * WILL FAIL on unfixed code:
     *   Cannot check methods on a non-existent class.
     *
     * Counterexample: SurgerySchedule class does not exist, relationships cannot be resolved
     *
     * Validates: Requirements 2.4
     */
    public function test_surgery_schedule_has_required_relationships(): void
    {
        $model = new SurgerySchedule;

        $requiredRelationships = ['patient', 'surgeon', 'operatingRoom', 'surgeryTeam', 'equipment'];

        foreach ($requiredRelationships as $relationship) {
            $this->assertTrue(
                method_exists($model, $relationship),
                "Bug Condition: SurgerySchedule must have '{$relationship}()' relationship method. "
                    ."Counterexample: Method '{$relationship}' does not exist on SurgerySchedule."
            );
        }
    }

    /**
     * @test
     * Bug Condition — OperatingRoom must have surgerySchedules() HasMany relationship
     *
     * WILL FAIL on unfixed code:
     *   The OperatingRoom model calls $this->surgerySchedules() in isAvailableAt()
     *   and getUsedMinutes(), but the relationship method is not defined OR the
     *   target model (SurgerySchedule) does not exist.
     *
     * Counterexample: OperatingRoom->surgerySchedules() throws error (method undefined or target class missing)
     *
     * Validates: Requirements 1.3, 2.3
     */
    public function test_operating_room_has_surgery_schedules_relationship(): void
    {
        $operatingRoom = new OperatingRoom;

        $this->assertTrue(
            method_exists($operatingRoom, 'surgerySchedules'),
            'Bug Condition: OperatingRoom must have surgerySchedules() method. '
                .'Counterexample: OperatingRoom->surgerySchedules() is undefined, '
                .'causing fatal error in isAvailableAt() and getUsedMinutes().'
        );

        // Verify it returns a HasMany relationship
        // This will fail if SurgerySchedule class doesn't exist (target model not found)
        $relationship = $operatingRoom->surgerySchedules();

        $this->assertInstanceOf(
            HasMany::class,
            $relationship,
            'Bug Condition: OperatingRoom::surgerySchedules() must return a HasMany relationship. '
                .'Counterexample: Relationship target class "App\Models\SurgerySchedule" not found.'
        );
    }

    /**
     * @test
     * Property-Based: Bug Condition — Random valid attributes should allow model instantiation
     *
     * Uses Eris to generate random valid attributes and verify that SurgerySchedule
     * can be instantiated with them (fill via constructor).
     *
     * WILL FAIL on unfixed code:
     *   Class "App\Models\SurgerySchedule" not found — cannot instantiate with any attributes.
     *
     * Counterexample: Any attempt to instantiate SurgerySchedule with valid attributes fails
     *
     * Validates: Requirements 1.1, 1.2, 2.1, 2.2
     */
    public function test_pbt_surgery_schedule_instantiation_with_random_attributes(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'Appendectomy',
                    'Cholecystectomy',
                    'Hernia Repair',
                    'Knee Replacement',
                    'Hip Replacement',
                    'Coronary Bypass',
                    'Cataract Surgery',
                    'Tonsillectomy',
                    'Cesarean Section',
                    'Spinal Fusion',
                    'Mastectomy',
                    'Thyroidectomy',
                ]),
                Generators::elements(['scheduled', 'in_progress', 'completed', 'cancelled']),
                Generators::elements(['emergency', 'urgent', 'elective']),
                Generators::elements(['open', 'laparoscopic', 'robotic', 'endoscopic']),
                Generators::choose(15, 720),
                Generators::choose(0, 5000)
            )
            ->then(function ($procedureName, $status, $priority, $surgeryType, $estimatedDuration, $bloodLoss) {
                $attributes = [
                    'procedure_name' => $procedureName,
                    'status' => $status,
                    'priority' => $priority,
                    'surgery_type' => $surgeryType,
                    'estimated_duration' => $estimatedDuration,
                    'blood_loss_ml' => $bloodLoss,
                ];

                // This will throw a fatal error on unfixed code because the class doesn't exist
                $model = new SurgerySchedule($attributes);

                $this->assertInstanceOf(
                    Model::class,
                    $model,
                    'SurgerySchedule should be instantiable with valid attributes. '
                        .'Counterexample: new SurgerySchedule('.json_encode($attributes).') throws fatal error.'
                );

                // Verify the model accepted the attributes (fillable check)
                $this->assertEquals(
                    $procedureName,
                    $model->procedure_name,
                    'SurgerySchedule should accept procedure_name as fillable attribute.'
                );
            });
    }
}
