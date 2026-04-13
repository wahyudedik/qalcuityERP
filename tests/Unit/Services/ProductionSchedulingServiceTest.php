<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\WorkOrder;
use App\Services\Manufacturing\ProductionSchedulingService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Production Scheduling Service Unit Tests
 * 
 * TASK-2.22: Unit tests for manufacturing services
 */
class ProductionSchedulingServiceTest extends TestCase
{
    private ProductionSchedulingService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ProductionSchedulingService::class);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function schedule_work_order_success(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(5);

        $result = $this->service->scheduleWorkOrder(
            $workOrder->id,
            $this->user->tenant_id,
            $startDate,
            $endDate,
            3, // Normal priority
            'Line A'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals($workOrder->id, $result['work_order']->id);

        // Verify work order updated
        $workOrder->refresh();
        $this->assertEquals($startDate->format('Y-m-d'), $workOrder->planned_start_date->format('Y-m-d'));
        $this->assertEquals($endDate->format('Y-m-d'), $workOrder->planned_end_date->format('Y-m-d'));
        $this->assertEquals(3, $workOrder->priority);
        $this->assertEquals('Line A', $workOrder->production_line);
    }

    /** @test */
    public function schedule_work_order_with_conflicts(): void
    {
        $workOrder1 = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now()->addDays(1),
            'planned_end_date' => Carbon::now()->addDays(5),
            'production_line' => 'Line A',
        ]);

        $workOrder2 = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $result = $this->service->scheduleWorkOrder(
            $workOrder2->id,
            $this->user->tenant_id,
            Carbon::now()->addDays(2),
            Carbon::now()->addDays(4),
            1, // Urgent - should allow conflicts
            'Line A'
        );

        // Urgent priority should allow scheduling despite conflicts
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['conflicts']);
    }

    /** @test */
    public function detect_scheduling_conflicts(): void
    {
        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now()->addDays(1),
            'planned_end_date' => Carbon::now()->addDays(5),
            'production_line' => 'Line A',
        ]);

        $conflicts = $this->service->detectSchedulingConflicts(
            $this->user->tenant_id,
            Carbon::now()->addDays(2),
            Carbon::now()->addDays(4),
            null,
            'Line A'
        );

        $this->assertNotEmpty($conflicts);
        $this->assertGreaterThanOrEqual(1, count($conflicts));
    }

    /** @test */
    public function detect_no_conflicts(): void
    {
        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now()->addDays(1),
            'planned_end_date' => Carbon::now()->addDays(5),
            'production_line' => 'Line A',
        ]);

        $conflicts = $this->service->detectSchedulingConflicts(
            $this->user->tenant_id,
            Carbon::now()->addDays(10),
            Carbon::now()->addDays(15),
            null,
            'Line A'
        );

        $this->assertEmpty($conflicts);
    }

    /** @test */
    public function get_scheduled_work_orders(): void
    {
        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now()->addDays(1),
            'planned_end_date' => Carbon::now()->addDays(5),
        ]);

        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'planned_start_date' => Carbon::now()->addDays(3),
            'planned_end_date' => Carbon::now()->addDays(7),
        ]);

        $scheduled = $this->service->getScheduledWorkOrders(
            $this->user->tenant_id,
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );

        $this->assertCount(2, $scheduled);
    }

    /** @test */
    public function reschedule_overdue_work_orders(): void
    {
        $overdue = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now()->subDays(10),
            'planned_end_date' => Carbon::now()->subDays(5),
        ]);

        $result = $this->service->rescheduleOverdue($this->user->tenant_id);

        $this->assertGreaterThan(0, $result['rescheduled']);

        $overdue->refresh();
        $this->assertGreaterThan(
            Carbon::now()->subDays(5)->timestamp,
            $overdue->planned_end_date->timestamp
        );
    }

    /** @test */
    public function optimize_schedule(): void
    {
        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'priority' => 1, // Urgent
            'planned_start_date' => Carbon::now()->addDays(5),
            'planned_end_date' => Carbon::now()->addDays(10),
        ]);

        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'priority' => 4, // Low
            'planned_start_date' => Carbon::now()->addDays(1),
            'planned_end_date' => Carbon::now()->addDays(3),
        ]);

        $result = $this->service->optimizeSchedule($this->user->tenant_id);

        $this->assertArrayHasKey('optimizations', $result);
    }

    /** @test */
    public function capacity_utilization(): void
    {
        WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now(),
            'planned_end_date' => Carbon::now()->addDays(5),
            'production_line' => 'Line A',
        ]);

        $utilization = $this->service->getCapacityUtilization(
            $this->user->tenant_id,
            Carbon::now(),
            Carbon::now()->addDays(7)
        );

        $this->assertIsArray($utilization);
        $this->assertArrayHasKey('by_line', $utilization);
        $this->assertArrayHasKey('overall', $utilization);
    }

    /** @test */
    public function work_order_is_overdue(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_end_date' => Carbon::now()->subDays(1),
        ]);

        $this->assertTrue($workOrder->isOverdue());
    }

    /** @test */
    public function work_order_not_overdue(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_end_date' => Carbon::now()->addDays(5),
        ]);

        $this->assertFalse($workOrder->isOverdue());
    }

    /** @test */
    public function work_order_get_gantt_data(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'planned_start_date' => Carbon::now()->addDays(1),
            'planned_end_date' => Carbon::now()->addDays(5),
            'progress_percent' => 50.00,
            'priority' => 2,
        ]);

        $ganttData = $workOrder->getGanttData();

        $this->assertArrayHasKey('id', $ganttData);
        $this->assertArrayHasKey('number', $ganttData);
        $this->assertArrayHasKey('product_name', $ganttData);
        $this->assertArrayHasKey('start', $ganttData);
        $this->assertArrayHasKey('end', $ganttData);
        $this->assertArrayHasKey('progress', $ganttData);
        $this->assertArrayHasKey('status', $ganttData);
        $this->assertArrayHasKey('priority', $ganttData);
        $this->assertArrayHasKey('is_overdue', $ganttData);
    }
}
