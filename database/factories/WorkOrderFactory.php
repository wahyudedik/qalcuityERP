<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => 1, // Default tenant for testing
            'product_id' => 1, // Default product for testing
            'user_id' => 1, // Default user for testing
            'number' => 'WO-'.$this->faker->unique()->numerify('########'),
            'target_quantity' => $this->faker->randomFloat(3, 1, 1000),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'liter', 'm3', 'ton']),
            'status' => $this->faker->randomElement(WorkOrder::STATUSES),
            'material_cost' => $this->faker->randomFloat(2, 100, 10000),
            'labor_cost' => $this->faker->randomFloat(2, 50, 5000),
            'overhead_cost' => $this->faker->randomFloat(2, 20, 2000),
            'overhead_method' => $this->faker->randomElement(['percentage', 'fixed', 'per_hour']),
            'overhead_rate' => $this->faker->randomFloat(4, 0.05, 0.25),
            'calculated_overhead' => $this->faker->randomFloat(2, 20, 2000),
            'total_operation_hours' => $this->faker->randomFloat(2, 1, 100),
            'total_cost' => $this->faker->randomFloat(2, 200, 20000),
            'materials_reserved' => $this->faker->boolean(),
            'materials_consumed' => $this->faker->boolean(),
            'planned_start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'planned_end_date' => $this->faker->dateTimeBetween('+1 day', '+2 months'),
            'priority' => $this->faker->numberBetween(1, 4),
            'production_line' => $this->faker->randomElement(['Line A', 'Line B', 'Line C', 'Manual']),
            'scrap_quantity' => $this->faker->randomFloat(3, 0, 50),
            'scrap_cost' => $this->faker->randomFloat(2, 0, 500),
            'scrap_reason' => $this->faker->optional()->sentence(),
            'rework_quantity' => $this->faker->randomFloat(3, 0, 20),
            'rework_cost' => $this->faker->randomFloat(2, 0, 200),
            'progress_percent' => $this->faker->randomFloat(2, 0, 100),
            'progress_stage' => $this->faker->randomElement(['setup', 'processing', 'finishing', 'qc']),
            'efficiency_rate' => $this->faker->randomFloat(2, 70, 120),
            'schedule_variance' => $this->faker->randomFloat(2, -10, 10),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the work order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_PENDING,
            'progress_percent' => 0,
            'progress_stage' => null,
        ]);
    }

    /**
     * Indicate that the work order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'actual_start_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'progress_percent' => $this->faker->randomFloat(2, 10, 90),
            'progress_stage' => $this->faker->randomElement(['setup', 'processing', 'finishing']),
        ]);
    }

    /**
     * Indicate that the work order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_COMPLETED,
            'started_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'actual_start_date' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
            'actual_end_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'progress_percent' => 100,
            'progress_stage' => 'qc',
        ]);
    }

    /**
     * Indicate that the work order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_CANCELLED,
            'progress_percent' => $this->faker->randomFloat(2, 0, 50),
        ]);
    }

    /**
     * Indicate that the work order is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_ON_HOLD,
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'actual_start_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'progress_percent' => $this->faker->randomFloat(2, 5, 75),
            'progress_stage' => $this->faker->randomElement(['setup', 'processing']),
        ]);
    }
}
