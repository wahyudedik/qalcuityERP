<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\BusinessFlowAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-Based Tests for BusinessFlowAnalyzer.
 *
 * Feature: comprehensive-erp-audit
 *
 * These tests generate random combinations of model/service stubs
 * in temporary directories and verify the analyzer correctly detects
 * missing components (models, service methods, session fields, etc.).
 *
 * Each property test validates the ANALYZER's detection capabilities,
 * not the actual business logic of the ERP flows.
 */
class BusinessFlowAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/bfa_prop_test_'.uniqid();
        mkdir($this->tempDir.'/app/Models', 0777, true);
        mkdir($this->tempDir.'/app/Services', 0777, true);
        mkdir($this->tempDir.'/app/Http/Controllers', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Property 8: Sales Flow State Machine Validity ────────────

    /**
     * Property 8: Sales Flow State Machine Validity
     *
     * For any random combination of sales flow models (present/absent)
     * and TransactionStateMachine methods (present/absent), the
     * BusinessFlowAnalyzer SHALL correctly detect every missing model
     * and every missing state machine method.
     *
     * **Validates: Requirements 3.1**
     *
     * // Feature: comprehensive-erp-audit, Property 8: Sales Flow State Machine Validity
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_8_sales_flow_state_machine_validity(): void
    {
        $salesModels = ['Quotation', 'SalesOrder', 'DeliveryOrder', 'Invoice', 'Payment', 'JournalEntry'];
        $stateMethods = ['postSalesOrder', 'postInvoice', 'cancelSalesOrder', 'cancelInvoice'];

        $this->forAll(
            Generators::choose(0, (1 << count($salesModels)) - 1),
            Generators::choose(0, (1 << count($stateMethods)) - 1)
        )->then(function (int $modelBitmask, int $methodBitmask) use ($salesModels, $stateMethods) {
            // Clean up from previous iteration
            $this->cleanTempFiles();

            // Determine which models are present
            $presentModels = [];
            foreach ($salesModels as $i => $model) {
                if ($modelBitmask & (1 << $i)) {
                    $presentModels[] = $model;
                    $this->createModelStub($model);
                }
            }

            // Determine which state machine methods are present
            $presentMethods = [];
            foreach ($stateMethods as $i => $method) {
                if ($methodBitmask & (1 << $i)) {
                    $presentMethods[] = $method;
                }
            }

            // Always create the TransactionStateMachine service with selected methods
            $this->createServiceStub('TransactionStateMachine', $presentMethods);
            // Create GlPostingService with all methods to isolate state machine testing
            $this->createServiceStub('GlPostingService', [
                'postSalesOrder',
                'postInvoiceCreated',
                'postInvoicePayment',
                'postSalesPayment',
            ]);

            $analyzer = $this->makeAnalyzer();
            $findings = $analyzer->validateSalesFlow();

            // Verify missing models are detected
            $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
            $detectedMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['model'],
                $missingModelFindings
            );

            foreach ($salesModels as $model) {
                if (! in_array($model, $presentModels)) {
                    $this->assertContains(
                        $model,
                        $detectedMissing,
                        "Analyzer MUST detect missing model '{$model}'. "
                            .'Present models: '.implode(', ', $presentModels)
                    );
                } else {
                    $this->assertNotContains(
                        $model,
                        $detectedMissing,
                        "Analyzer must NOT flag present model '{$model}'."
                    );
                }
            }

            // Verify missing state machine methods are detected
            $methodFindings = array_filter(
                $this->filterByCheck($findings, 'flow_service_method_missing'),
                fn (AuditFinding $f) => $f->metadata['service'] === 'TransactionStateMachine'
            );
            $detectedMissingMethods = array_map(
                fn (AuditFinding $f) => $f->metadata['method'],
                $methodFindings
            );

            foreach ($stateMethods as $method) {
                if (! in_array($method, $presentMethods)) {
                    $this->assertContains(
                        $method,
                        $detectedMissingMethods,
                        "Analyzer MUST detect missing state machine method '{$method}'. "
                            .'Present methods: '.implode(', ', $presentMethods)
                    );
                } else {
                    $this->assertNotContains(
                        $method,
                        $detectedMissingMethods,
                        "Analyzer must NOT flag present method '{$method}'."
                    );
                }
            }

            // All missing-model findings must be High severity
            foreach ($missingModelFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
                $this->assertSame('Sales', $f->metadata['flow']);
            }

            // All missing-method findings must be High severity
            foreach ($methodFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
            }
        });
    }

    // ── Property 9: Purchasing Flow State Machine Validity ───────

    /**
     * Property 9: Purchasing Flow State Machine Validity
     *
     * For any random combination of purchasing flow models (present/absent)
     * and service methods (present/absent), the BusinessFlowAnalyzer SHALL
     * correctly detect every missing model and every missing service method.
     *
     * **Validates: Requirements 3.2**
     *
     * // Feature: comprehensive-erp-audit, Property 9: Purchasing Flow State Machine Validity
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_9_purchasing_flow_state_machine_validity(): void
    {
        $purchasingModels = [
            'PurchaseRequisition',
            'Rfq',
            'PurchaseOrder',
            'GoodsReceipt',
            'Invoice',
            'Payment',
            'JournalEntry',
        ];
        $stateMethods = ['postPurchaseOrder', 'cancelPurchaseOrder'];
        $glMethods = ['postPurchaseReceived', 'postPurchasePayment'];

        $this->forAll(
            Generators::choose(0, (1 << count($purchasingModels)) - 1),
            Generators::choose(0, (1 << count($stateMethods)) - 1),
            Generators::choose(0, (1 << count($glMethods)) - 1)
        )->then(function (int $modelBitmask, int $stateBitmask, int $glBitmask) use ($purchasingModels, $stateMethods, $glMethods) {
            $this->cleanTempFiles();

            // Create present models
            $presentModels = [];
            foreach ($purchasingModels as $i => $model) {
                if ($modelBitmask & (1 << $i)) {
                    $presentModels[] = $model;
                    $this->createModelStub($model);
                }
            }

            // Create TransactionStateMachine with selected methods
            $presentStateMethods = [];
            foreach ($stateMethods as $i => $method) {
                if ($stateBitmask & (1 << $i)) {
                    $presentStateMethods[] = $method;
                }
            }
            $this->createServiceStub('TransactionStateMachine', $presentStateMethods);

            // Create GlPostingService with selected methods
            $presentGlMethods = [];
            foreach ($glMethods as $i => $method) {
                if ($glBitmask & (1 << $i)) {
                    $presentGlMethods[] = $method;
                }
            }
            $this->createServiceStub('GlPostingService', $presentGlMethods);

            $analyzer = $this->makeAnalyzer();
            $findings = $analyzer->validatePurchasingFlow();

            // Verify missing models are detected
            $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
            $detectedMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['model'],
                $missingModelFindings
            );

            foreach ($purchasingModels as $model) {
                if (! in_array($model, $presentModels)) {
                    $this->assertContains(
                        $model,
                        $detectedMissing,
                        "Analyzer MUST detect missing purchasing model '{$model}'."
                    );
                } else {
                    $this->assertNotContains(
                        $model,
                        $detectedMissing,
                        "Analyzer must NOT flag present purchasing model '{$model}'."
                    );
                }
            }

            // Verify missing state machine methods
            $stateMethodFindings = array_filter(
                $this->filterByCheck($findings, 'flow_service_method_missing'),
                fn (AuditFinding $f) => $f->metadata['service'] === 'TransactionStateMachine'
            );
            $detectedStateMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['method'],
                $stateMethodFindings
            );

            foreach ($stateMethods as $method) {
                if (! in_array($method, $presentStateMethods)) {
                    $this->assertContains(
                        $method,
                        $detectedStateMissing,
                        "Analyzer MUST detect missing state method '{$method}'."
                    );
                } else {
                    $this->assertNotContains(
                        $method,
                        $detectedStateMissing,
                        "Analyzer must NOT flag present state method '{$method}'."
                    );
                }
            }

            // Verify missing GL methods
            $glMethodFindings = array_filter(
                $this->filterByCheck($findings, 'flow_service_method_missing'),
                fn (AuditFinding $f) => $f->metadata['service'] === 'GlPostingService'
            );
            $detectedGlMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['method'],
                $glMethodFindings
            );

            foreach ($glMethods as $method) {
                if (! in_array($method, $presentGlMethods)) {
                    $this->assertContains(
                        $method,
                        $detectedGlMissing,
                        "Analyzer MUST detect missing GL method '{$method}'."
                    );
                } else {
                    $this->assertNotContains(
                        $method,
                        $detectedGlMissing,
                        "Analyzer must NOT flag present GL method '{$method}'."
                    );
                }
            }

            // Severity checks
            foreach ($missingModelFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
                $this->assertSame('Purchasing', $f->metadata['flow']);
            }
        });
    }

    // ── Property 10: Payroll Calculation Correctness ─────────────

    /**
     * Property 10: Payroll Calculation Correctness
     *
     * For any random combination of payroll models (present/absent) and
     * PayrollCalculationService methods (present/absent), the
     * BusinessFlowAnalyzer SHALL correctly detect every missing model
     * and every missing calculation method.
     *
     * **Validates: Requirements 3.3**
     *
     * // Feature: comprehensive-erp-audit, Property 10: Payroll Calculation Correctness
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_10_payroll_calculation_correctness(): void
    {
        $payrollModels = [
            'Attendance',
            'Overtime',
            'SalaryComponent',
            'PayrollRun',
            'Payslip',
            'JournalEntry',
        ];
        $calcMethods = [
            'calculateBpjsKesehatan',
            'calculateBpjsKetenagakerjaan',
            'calculatePph21',
            'calculateNetSalary',
        ];

        $this->forAll(
            Generators::choose(0, (1 << count($payrollModels)) - 1),
            Generators::choose(0, (1 << count($calcMethods)) - 1)
        )->then(function (int $modelBitmask, int $methodBitmask) use ($payrollModels, $calcMethods) {
            $this->cleanTempFiles();

            // Create present models
            $presentModels = [];
            foreach ($payrollModels as $i => $model) {
                if ($modelBitmask & (1 << $i)) {
                    $presentModels[] = $model;
                    $this->createModelStub($model);
                }
            }

            // Create PayrollCalculationService with selected methods
            $presentMethods = [];
            foreach ($calcMethods as $i => $method) {
                if ($methodBitmask & (1 << $i)) {
                    $presentMethods[] = $method;
                }
            }
            $this->createServiceStub('PayrollCalculationService', $presentMethods);

            $analyzer = $this->makeAnalyzer();
            $findings = $analyzer->validatePayrollFlow();

            // Verify missing models are detected
            $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
            $detectedMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['model'],
                $missingModelFindings
            );

            foreach ($payrollModels as $model) {
                if (! in_array($model, $presentModels)) {
                    $this->assertContains(
                        $model,
                        $detectedMissing,
                        "Analyzer MUST detect missing payroll model '{$model}'."
                    );
                } else {
                    $this->assertNotContains(
                        $model,
                        $detectedMissing,
                        "Analyzer must NOT flag present payroll model '{$model}'."
                    );
                }
            }

            // Verify missing calculation methods
            $methodFindings = array_filter(
                $this->filterByCheck($findings, 'flow_service_method_missing'),
                fn (AuditFinding $f) => $f->metadata['service'] === 'PayrollCalculationService'
            );
            $detectedMissingMethods = array_map(
                fn (AuditFinding $f) => $f->metadata['method'],
                $methodFindings
            );

            foreach ($calcMethods as $method) {
                if (! in_array($method, $presentMethods)) {
                    $this->assertContains(
                        $method,
                        $detectedMissingMethods,
                        "Analyzer MUST detect missing calculation method '{$method}'."
                    );
                } else {
                    $this->assertNotContains(
                        $method,
                        $detectedMissingMethods,
                        "Analyzer must NOT flag present calculation method '{$method}'."
                    );
                }
            }

            // Severity checks
            foreach ($missingModelFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
                $this->assertSame('Payroll', $f->metadata['flow']);
            }
            foreach ($methodFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
            }
        });
    }

    // ── Property 11: Inventory Costing Correctness ───────────────

    /**
     * Property 11: Inventory Costing Correctness
     *
     * For any random combination of inventory models (present/absent) and
     * InventoryCostingService methods (present/absent), the
     * BusinessFlowAnalyzer SHALL correctly detect every missing model
     * and every missing costing method.
     *
     * **Validates: Requirements 3.4**
     *
     * // Feature: comprehensive-erp-audit, Property 11: Inventory Costing Correctness
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_11_inventory_costing_correctness(): void
    {
        $inventoryModels = ['Product', 'StockMovement', 'StockTransfer', 'StockOpnameSession'];
        $costingMethods = ['recordStockIn', 'recordStockOut', 'getCurrentCost', 'valuationReport'];

        $this->forAll(
            Generators::choose(0, (1 << count($inventoryModels)) - 1),
            Generators::choose(0, (1 << count($costingMethods)) - 1)
        )->then(function (int $modelBitmask, int $methodBitmask) use ($inventoryModels, $costingMethods) {
            $this->cleanTempFiles();

            // Create present models
            $presentModels = [];
            foreach ($inventoryModels as $i => $model) {
                if ($modelBitmask & (1 << $i)) {
                    $presentModels[] = $model;
                    $this->createModelStub($model);
                }
            }

            // Create InventoryCostingService with selected methods
            $presentMethods = [];
            foreach ($costingMethods as $i => $method) {
                if ($methodBitmask & (1 << $i)) {
                    $presentMethods[] = $method;
                }
            }
            $this->createServiceStub('InventoryCostingService', $presentMethods);

            $analyzer = $this->makeAnalyzer();
            $findings = $analyzer->validateInventoryFlow();

            // Verify missing models are detected
            $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
            $detectedMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['model'],
                $missingModelFindings
            );

            foreach ($inventoryModels as $model) {
                if (! in_array($model, $presentModels)) {
                    $this->assertContains(
                        $model,
                        $detectedMissing,
                        "Analyzer MUST detect missing inventory model '{$model}'."
                    );
                } else {
                    $this->assertNotContains(
                        $model,
                        $detectedMissing,
                        "Analyzer must NOT flag present inventory model '{$model}'."
                    );
                }
            }

            // Verify missing costing methods
            $methodFindings = array_filter(
                $this->filterByCheck($findings, 'flow_service_method_missing'),
                fn (AuditFinding $f) => $f->metadata['service'] === 'InventoryCostingService'
            );
            $detectedMissingMethods = array_map(
                fn (AuditFinding $f) => $f->metadata['method'],
                $methodFindings
            );

            foreach ($costingMethods as $method) {
                if (! in_array($method, $presentMethods)) {
                    $this->assertContains(
                        $method,
                        $detectedMissingMethods,
                        "Analyzer MUST detect missing costing method '{$method}'."
                    );
                } else {
                    $this->assertNotContains(
                        $method,
                        $detectedMissingMethods,
                        "Analyzer must NOT flag present costing method '{$method}'."
                    );
                }
            }

            // Severity checks
            foreach ($missingModelFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
                $this->assertSame('Inventory', $f->metadata['flow']);
            }
            foreach ($methodFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
            }
        });
    }

    // ── Property 12: POS Session Balance Correctness ─────────────

    /**
     * Property 12: POS Session Balance Correctness
     *
     * For any random POS model configuration with/without CashierSession
     * status fields, the BusinessFlowAnalyzer SHALL correctly detect
     * missing POS models and missing session tracking fields.
     *
     * **Validates: Requirements 3.5**
     *
     * // Feature: comprehensive-erp-audit, Property 12: POS Session Balance Correctness
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_12_pos_session_balance_correctness(): void
    {
        $posModels = ['CashierSession', 'PosSale', 'PosPayment', 'PosReceipt'];

        $this->forAll(
            Generators::choose(0, (1 << count($posModels)) - 1),
            Generators::bool(), // hasStatusField — whether CashierSession has status/opening_balance
            Generators::bool()  // hasGlMethod — whether GlPostingService has postPosSession
        )->then(function (int $modelBitmask, bool $hasStatusField, bool $hasGlMethod) use ($posModels) {
            $this->cleanTempFiles();

            // Create present models
            $presentModels = [];
            foreach ($posModels as $i => $model) {
                if ($modelBitmask & (1 << $i)) {
                    $presentModels[] = $model;

                    if ($model === 'CashierSession') {
                        // CashierSession gets special treatment for status fields
                        $fillable = $hasStatusField
                            ? "protected \$fillable = ['status', 'opening_balance', 'closing_balance'];"
                            : "protected \$fillable = ['name'];";
                        $this->createModelStub($model, $fillable);
                    } else {
                        $this->createModelStub($model);
                    }
                }
            }

            // Create GlPostingService
            $glMethods = $hasGlMethod ? ['postPosSession'] : [];
            $this->createServiceStub('GlPostingService', $glMethods);

            $analyzer = $this->makeAnalyzer();
            $findings = $analyzer->validatePosFlow();

            // Verify missing models are detected
            $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
            $detectedMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['model'],
                $missingModelFindings
            );

            foreach ($posModels as $model) {
                if (! in_array($model, $presentModels)) {
                    $this->assertContains(
                        $model,
                        $detectedMissing,
                        "Analyzer MUST detect missing POS model '{$model}'."
                    );
                } else {
                    $this->assertNotContains(
                        $model,
                        $detectedMissing,
                        "Analyzer must NOT flag present POS model '{$model}'."
                    );
                }
            }

            // Verify session field detection
            $sessionFindings = $this->filterByCheck($findings, 'pos_session_fields');
            $cashierSessionPresent = in_array('CashierSession', $presentModels);

            if (! $cashierSessionPresent) {
                // CashierSession not present → no session field finding (model missing is reported separately)
                $this->assertCount(
                    0,
                    $sessionFindings,
                    'No session field finding when CashierSession model is absent.'
                );
            } elseif ($hasStatusField) {
                // CashierSession present WITH status fields → no finding
                $this->assertCount(
                    0,
                    $sessionFindings,
                    'No session field finding when CashierSession has status/opening_balance.'
                );
            } else {
                // CashierSession present WITHOUT status fields → MUST produce finding
                $this->assertCount(
                    1,
                    $sessionFindings,
                    'Analyzer MUST detect missing session tracking fields.'
                );
                $this->assertSame(Severity::Medium, $sessionFindings[0]->severity);
                $this->assertSame('POS', $sessionFindings[0]->metadata['flow']);
            }

            // Verify GL method detection
            $glMethodFindings = array_filter(
                $this->filterByCheck($findings, 'flow_service_method_missing'),
                fn (AuditFinding $f) => $f->metadata['service'] === 'GlPostingService'
            );

            if ($hasGlMethod) {
                $missingGlMethods = array_map(
                    fn (AuditFinding $f) => $f->metadata['method'],
                    $glMethodFindings
                );
                $this->assertNotContains(
                    'postPosSession',
                    $missingGlMethods,
                    "Analyzer must NOT flag present GL method 'postPosSession'."
                );
            } else {
                $missingGlMethods = array_map(
                    fn (AuditFinding $f) => $f->metadata['method'],
                    $glMethodFindings
                );
                $this->assertContains(
                    'postPosSession',
                    $missingGlMethods,
                    "Analyzer MUST detect missing GL method 'postPosSession'."
                );
            }

            // Severity checks on model findings
            foreach ($missingModelFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
                $this->assertSame('POS', $f->metadata['flow']);
            }
        });
    }

    // ── Property 13: Accounting Period Lock Enforcement ──────────

    /**
     * Property 13: Accounting Period Lock Enforcement
     *
     * For any random approval flow configuration with/without
     * WorkflowEngine methods, the BusinessFlowAnalyzer SHALL correctly
     * detect missing approval models, missing approval services, and
     * missing workflow engine methods.
     *
     * **Validates: Requirements 3.8**
     *
     * // Feature: comprehensive-erp-audit, Property 13: Accounting Period Lock Enforcement
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_13_accounting_period_lock_enforcement(): void
    {
        $approvalModels = ['ApprovalWorkflow', 'ApprovalRequest'];
        $approvalServices = ['WorkflowEngine', 'DocumentApprovalService', 'PoApprovalService'];
        $engineMethods = ['fireEvent', 'executeScheduled'];

        $this->forAll(
            Generators::choose(0, (1 << count($approvalModels)) - 1),
            Generators::choose(0, (1 << count($approvalServices)) - 1),
            Generators::choose(0, (1 << count($engineMethods)) - 1)
        )->then(function (int $modelBitmask, int $serviceBitmask, int $methodBitmask) use ($approvalModels, $approvalServices, $engineMethods) {
            $this->cleanTempFiles();

            // Create present models
            $presentModels = [];
            foreach ($approvalModels as $i => $model) {
                if ($modelBitmask & (1 << $i)) {
                    $presentModels[] = $model;
                    $this->createModelStub($model);
                }
            }

            // Create present services
            $presentServices = [];
            foreach ($approvalServices as $i => $service) {
                if ($serviceBitmask & (1 << $i)) {
                    $presentServices[] = $service;

                    if ($service === 'WorkflowEngine') {
                        // WorkflowEngine gets selected methods
                        $presentEngineMethods = [];
                        foreach ($engineMethods as $j => $method) {
                            if ($methodBitmask & (1 << $j)) {
                                $presentEngineMethods[] = $method;
                            }
                        }
                        $this->createServiceStub($service, $presentEngineMethods);
                    } else {
                        $this->createServiceStub($service, []);
                    }
                }
            }

            $analyzer = $this->makeAnalyzer();
            $findings = $analyzer->validateApprovalFlow();

            // Verify missing models are detected
            $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
            $detectedMissing = array_map(
                fn (AuditFinding $f) => $f->metadata['model'],
                $missingModelFindings
            );

            foreach ($approvalModels as $model) {
                if (! in_array($model, $presentModels)) {
                    $this->assertContains(
                        $model,
                        $detectedMissing,
                        "Analyzer MUST detect missing approval model '{$model}'."
                    );
                } else {
                    $this->assertNotContains(
                        $model,
                        $detectedMissing,
                        "Analyzer must NOT flag present approval model '{$model}'."
                    );
                }
            }

            // Verify missing approval services are detected
            $missingServiceFindings = $this->filterByCheck($findings, 'approval_service_missing');
            $detectedMissingServices = array_map(
                fn (AuditFinding $f) => $f->metadata['service'],
                $missingServiceFindings
            );

            foreach ($approvalServices as $service) {
                if (! in_array($service, $presentServices)) {
                    $this->assertContains(
                        $service,
                        $detectedMissingServices,
                        "Analyzer MUST detect missing approval service '{$service}'."
                    );
                } else {
                    $this->assertNotContains(
                        $service,
                        $detectedMissingServices,
                        "Analyzer must NOT flag present approval service '{$service}'."
                    );
                }
            }

            // Verify missing WorkflowEngine methods (only if WorkflowEngine exists)
            $workflowEnginePresent = in_array('WorkflowEngine', $presentServices);

            if ($workflowEnginePresent) {
                $engineMethodFindings = array_filter(
                    $this->filterByCheck($findings, 'flow_service_method_missing'),
                    fn (AuditFinding $f) => $f->metadata['service'] === 'WorkflowEngine'
                );
                $detectedMissingEngineMethods = array_map(
                    fn (AuditFinding $f) => $f->metadata['method'],
                    $engineMethodFindings
                );

                $presentEngineMethods = [];
                foreach ($engineMethods as $j => $method) {
                    if ($methodBitmask & (1 << $j)) {
                        $presentEngineMethods[] = $method;
                    }
                }

                foreach ($engineMethods as $method) {
                    if (! in_array($method, $presentEngineMethods)) {
                        $this->assertContains(
                            $method,
                            $detectedMissingEngineMethods,
                            "Analyzer MUST detect missing engine method '{$method}'."
                        );
                    } else {
                        $this->assertNotContains(
                            $method,
                            $detectedMissingEngineMethods,
                            "Analyzer must NOT flag present engine method '{$method}'."
                        );
                    }
                }
            } else {
                // WorkflowEngine missing → should get a service-missing finding (Critical)
                // instead of individual method-missing findings
                $serviceMissingFindings = array_filter(
                    $this->filterByCheck($findings, 'flow_service_missing'),
                    fn (AuditFinding $f) => $f->metadata['service'] === 'WorkflowEngine'
                );
                $this->assertNotEmpty(
                    $serviceMissingFindings,
                    'Analyzer MUST report WorkflowEngine as missing service when not present.'
                );
                foreach ($serviceMissingFindings as $f) {
                    $this->assertSame(Severity::Critical, $f->severity);
                }
            }

            // Severity checks on model findings
            foreach ($missingModelFindings as $f) {
                $this->assertSame(Severity::High, $f->severity);
                $this->assertSame('Approval', $f->metadata['flow']);
            }

            // Severity checks on missing service findings
            foreach ($missingServiceFindings as $f) {
                $this->assertSame(Severity::Medium, $f->severity);
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeAnalyzer(): BusinessFlowAnalyzer
    {
        return new BusinessFlowAnalyzer(
            modelPath: $this->tempDir.'/app/Models',
            servicePath: $this->tempDir.'/app/Services',
            controllerPath: $this->tempDir.'/app/Http/Controllers',
            basePath: $this->tempDir,
        );
    }

    private function createModelStub(string $name, string $extraContent = ''): void
    {
        $content = "<?php\nnamespace App\\Models;\nclass {$name} {\n    {$extraContent}\n}\n";
        file_put_contents($this->tempDir."/app/Models/{$name}.php", $content);
    }

    private function createServiceStub(string $name, array $methods): void
    {
        $methodDefs = '';
        foreach ($methods as $method) {
            $methodDefs .= "    public function {$method}() {}\n";
        }
        $content = "<?php\nnamespace App\\Services;\nclass {$name} {\n{$methodDefs}}\n";
        file_put_contents($this->tempDir."/app/Services/{$name}.php", $content);
    }

    /**
     * Filter findings by the 'check' metadata key.
     *
     * @param  AuditFinding[]  $findings
     * @return AuditFinding[]
     */
    private function filterByCheck(array $findings, string $check): array
    {
        return array_values(array_filter(
            $findings,
            fn (AuditFinding $f) => ($f->metadata['check'] ?? null) === $check
        ));
    }

    /**
     * Clean up temporary model and service files between iterations.
     */
    private function cleanTempFiles(): void
    {
        $dirs = [
            $this->tempDir.'/app/Models',
            $this->tempDir.'/app/Services',
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $files = glob($dir.'/*.php');
            if ($files) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Recursively remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
