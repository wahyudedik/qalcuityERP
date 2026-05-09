<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\QcInspection;
use App\Models\QcTestTemplate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Tests\TestCase;

/**
 * QC Models Unit Tests
 *
 * TASK-2.22: Unit tests for QC models
 */
class QcModelsTest extends TestCase
{
    private User $user;

    private WorkOrder $workOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant first
        $tenant = Tenant::factory()->create();

        // Create test user with the tenant
        $this->user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        // Create product with the same tenant
        $product = Product::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        // Create work order with same tenant, user, and product
        $this->workOrder = WorkOrder::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test QC Inspection auto-numbering
     */
    public function test_qc_inspection_auto_numbering(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 10,
            'status' => 'pending',
        ]);

        $this->assertMatchesRegularExpression('/^QCI-\d{8}-\d{4}$/', $inspection->inspection_number);
    }

    /**
     * Test QC Inspection auto-assign inspector
     */
    public function test_qc_inspection_auto_assign_inspector(): void
    {
        $this->actingAs($this->user);

        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'stage' => 'final',
            'sample_size' => 10,
            'status' => 'pending',
        ]);

        $this->assertEquals($this->user->id, $inspection->inspector_id);
    }

    /**
     * Test calculate pass rate
     */
    public function test_calculate_pass_rate(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 95,
            'sample_failed' => 5,
            'status' => 'in_progress',
        ]);

        $passRate = $inspection->calculatePassRate();

        $this->assertEquals(95.0, $passRate);
    }

    /**
     * Test calculate pass rate with zero sample
     */
    public function test_calculate_pass_rate_zero_sample(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 0,
            'sample_passed' => 0,
            'sample_failed' => 0,
            'status' => 'pending',
        ]);

        $passRate = $inspection->calculatePassRate();

        $this->assertEquals(0, $passRate);
    }

    /**
     * Test determine grade A (>=98%)
     */
    public function test_determine_grade_a(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 99,
            'sample_failed' => 1,
            'status' => 'in_progress',
        ]);

        $grade = $inspection->determineGrade();

        $this->assertEquals('A', $grade);
    }

    /**
     * Test determine grade B (>=95%)
     */
    public function test_determine_grade_b(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 96,
            'sample_failed' => 4,
            'status' => 'in_progress',
        ]);

        $grade = $inspection->determineGrade();

        $this->assertEquals('B', $grade);
    }

    /**
     * Test determine grade F (<85%)
     */
    public function test_determine_grade_f(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 80,
            'sample_failed' => 20,
            'status' => 'in_progress',
        ]);

        $grade = $inspection->determineGrade();

        $this->assertEquals('F', $grade);
    }

    /**
     * Test pass inspection
     */
    public function test_pass_inspection(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 99,
            'sample_failed' => 1,
            'status' => 'in_progress',
        ]);

        $inspection->pass('Excellent quality');

        $this->assertEquals('passed', $inspection->status);
        $this->assertEquals('A', $inspection->grade);
        $this->assertNotNull($inspection->inspected_at);

        // Verify work order updated
        $this->workOrder->refresh();
        $this->assertEquals('passed', $this->workOrder->quality_status);
        $this->assertEquals('A', $this->workOrder->quality_grade);
    }

    /**
     * Test fail inspection
     */
    public function test_fail_inspection(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 70,
            'sample_failed' => 30,
            'status' => 'in_progress',
        ]);

        $inspection->fail('Major defects found', 'Cracks, discoloration');

        $this->assertEquals('failed', $inspection->status);
        $this->assertEquals('F', $inspection->grade);
        $this->assertEquals('Major defects found', $inspection->corrective_action);

        // Verify work order updated
        $this->workOrder->refresh();
        $this->assertEquals('failed', $this->workOrder->quality_status);
    }

    /**
     * Test conditional pass
     */
    public function test_conditional_pass(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 92,
            'sample_failed' => 8,
            'status' => 'in_progress',
        ]);

        $inspection->conditionalPass('Minor surface defects acceptable');

        $this->assertEquals('conditional_pass', $inspection->status);
        $this->assertEquals('C', $inspection->grade);

        // Verify work order updated
        $this->workOrder->refresh();
        $this->assertEquals('conditional', $this->workOrder->quality_status);
    }

    /**
     * Test record test results
     */
    public function test_record_test_results(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 10,
            'status' => 'pending',
        ]);

        $results = [
            ['parameter' => 'Strength', 'value' => 35, 'passed' => true],
            ['parameter' => 'Slump', 'value' => 120, 'passed' => true],
            ['parameter' => 'Air Content', 'value' => 5.2, 'passed' => false],
        ];

        $inspection->recordTestResults($results);

        $this->assertEquals($results, $inspection->test_results);
        $this->assertEquals(2, $inspection->sample_passed);
        $this->assertEquals(1, $inspection->sample_failed);
        $this->assertEquals(3, $inspection->sample_size);
    }

    /**
     * Test isPassed method
     */
    public function test_is_passed(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 99,
            'status' => 'passed',
        ]);

        $this->assertTrue($inspection->isPassed());
    }

    /**
     * Test isFailed method
     */
    public function test_is_failed(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 100,
            'sample_passed' => 70,
            'status' => 'failed',
        ]);

        $this->assertTrue($inspection->isFailed());
    }

    /**
     * Test stage label accessor
     */
    public function test_stage_label(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'in-process',
            'sample_size' => 10,
            'status' => 'pending',
        ]);

        $this->assertEquals('In-Process', $inspection->stage_label);
    }

    /**
     * Test status color accessor
     */
    public function test_status_color(): void
    {
        $inspection = QcInspection::create([
            'tenant_id' => $this->user->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'inspector_id' => $this->user->id,
            'stage' => 'final',
            'sample_size' => 10,
            'status' => 'passed',
        ]);

        $this->assertEquals('green', $inspection->status_color);
    }

    /** @test */
    public function qc_test_template_calculate_sample_size_sqrt(): void
    {
        $template = QcTestTemplate::create([
            'tenant_id' => $this->user->tenant_id,
            'name' => 'Concrete Test',
            'stage' => 'final',
            'test_parameters' => [
                ['name' => 'Strength', 'min' => 30, 'max' => 50, 'unit' => 'MPa'],
            ],
            'sample_size_formula' => 1, // sqrt formula
            'acceptance_quality_limit' => 2.5,
            'is_active' => true,
        ]);

        $sampleSize = $template->calculateSampleSize(100);

        $this->assertEquals(10, $sampleSize); // sqrt(100) = 10
    }

    /** @test */
    public function qc_test_template_calculate_sample_size_10_percent(): void
    {
        $template = QcTestTemplate::create([
            'tenant_id' => $this->user->tenant_id,
            'name' => 'Steel Test',
            'stage' => 'incoming',
            'test_parameters' => [
                ['name' => 'Tensile Strength', 'min' => 400, 'max' => 600, 'unit' => 'MPa'],
            ],
            'sample_size_formula' => 2, // 10% formula
            'acceptance_quality_limit' => 1.5,
            'is_active' => true,
        ]);

        $sampleSize = $template->calculateSampleSize(200);

        $this->assertEquals(20, $sampleSize); // 200 * 0.1 = 20
    }

    /** @test */
    public function qc_test_template_validate_results(): void
    {
        $template = QcTestTemplate::create([
            'tenant_id' => $this->user->tenant_id,
            'name' => 'Quality Check',
            'stage' => 'final',
            'test_parameters' => [
                ['name' => 'Strength', 'min' => 30, 'max' => 50, 'unit' => 'MPa', 'critical' => true],
                ['name' => 'Slump', 'min' => 100, 'max' => 150, 'unit' => 'mm', 'critical' => false],
            ],
            'sample_size_formula' => 1,
            'acceptance_quality_limit' => 2.5,
            'is_active' => true,
        ]);

        $results = [
            ['parameter' => 'Strength', 'value' => 35],
            ['parameter' => 'Slump', 'value' => 120],
        ];

        $validation = $template->validateResults($results);

        $this->assertTrue($validation['all_passed']);
        $this->assertEquals(2, $validation['passed_count']);
        $this->assertEquals(0, $validation['failed_count']);
    }

    /** @test */
    public function qc_test_template_validate_results_with_failure(): void
    {
        $template = QcTestTemplate::create([
            'tenant_id' => $this->user->tenant_id,
            'name' => 'Quality Check',
            'stage' => 'final',
            'test_parameters' => [
                ['name' => 'Strength', 'min' => 30, 'max' => 50, 'unit' => 'MPa', 'critical' => true],
            ],
            'sample_size_formula' => 1,
            'acceptance_quality_limit' => 2.5,
            'is_active' => true,
        ]);

        $results = [
            ['parameter' => 'Strength', 'value' => 25], // Below minimum
        ];

        $validation = $template->validateResults($results);

        $this->assertFalse($validation['all_passed']);
        $this->assertEquals(0, $validation['passed_count']);
        $this->assertEquals(1, $validation['failed_count']);
        $this->assertStringContainsString('below minimum', $validation['results'][0]['error']);
    }

    /** @test */
    public function qc_test_template_create_inspection(): void
    {
        $template = QcTestTemplate::create([
            'tenant_id' => $this->user->tenant_id,
            'name' => 'Final Inspection Template',
            'stage' => 'final',
            'test_parameters' => [
                ['name' => 'Strength', 'min' => 30, 'max' => 50, 'unit' => 'MPa'],
            ],
            'sample_size_formula' => 1,
            'acceptance_quality_limit' => 2.5,
            'is_active' => true,
        ]);

        $inspection = $template->createInspection(
            $this->workOrder,
            100, // lot size
            'final'
        );

        $this->assertInstanceOf(QcInspection::class, $inspection);
        $this->assertEquals($this->workOrder->id, $inspection->work_order_id);
        $this->assertEquals($template->id, $inspection->template_id);
        $this->assertEquals(10, $inspection->sample_size); // sqrt(100)
        $this->assertEquals('pending', $inspection->status);
    }

    /** @test */
    public function qc_test_template_stage_label(): void
    {
        $template = QcTestTemplate::create([
            'tenant_id' => $this->user->tenant_id,
            'name' => 'Incoming Test',
            'stage' => 'incoming',
            'test_parameters' => [],
            'sample_size_formula' => 1,
            'acceptance_quality_limit' => 2.5,
            'is_active' => true,
        ]);

        $this->assertEquals('Incoming Material', $template->stage_label);
    }
}
