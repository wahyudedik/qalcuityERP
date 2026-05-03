<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\BusinessFlowAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for BusinessFlowAnalyzer.
 *
 * Uses temporary fixture directories with model and service stubs
 * to test flow validation logic in isolation.
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.8
 */
class BusinessFlowAnalyzerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/bfa_test_' . uniqid();
        mkdir($this->tempDir . '/app/Models', 0777, true);
        mkdir($this->tempDir . '/app/Services', 0777, true);
        mkdir($this->tempDir . '/app/Http/Controllers', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_business_flow(): void
    {
        $analyzer = $this->makeAnalyzer();

        $this->assertSame('business_flow', $analyzer->category());
    }

    // ── Sales Flow (Requirement 3.1) ─────────────────────────────

    public function test_sales_flow_detects_missing_models(): void
    {
        // No models at all
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateSalesFlow();

        $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
        $missingModelNames = array_map(
            fn(AuditFinding $f) => $f->metadata['model'],
            $missingModelFindings
        );

        $this->assertContains('Quotation', $missingModelNames);
        $this->assertContains('SalesOrder', $missingModelNames);
        $this->assertContains('DeliveryOrder', $missingModelNames);
        $this->assertContains('Invoice', $missingModelNames);
        $this->assertContains('Payment', $missingModelNames);
        $this->assertContains('JournalEntry', $missingModelNames);

        foreach ($missingModelFindings as $f) {
            $this->assertSame(Severity::High, $f->severity);
            $this->assertSame('business_flow', $f->category);
            $this->assertSame('Sales', $f->metadata['flow']);
        }
    }

    public function test_sales_flow_no_missing_model_findings_when_all_exist(): void
    {
        $models = ['Quotation', 'SalesOrder', 'DeliveryOrder', 'Invoice', 'Payment', 'JournalEntry'];
        foreach ($models as $model) {
            $this->createModelStub($model);
        }

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateSalesFlow();

        $missingModelFindings = $this->filterByCheck($findings, 'flow_model_missing');
        $this->assertCount(0, $missingModelFindings);
    }

    public function test_sales_flow_detects_missing_relationships(): void
    {
        // Create models without relationship methods
        $models = ['Quotation', 'SalesOrder', 'DeliveryOrder', 'Invoice', 'Payment', 'JournalEntry'];
        foreach ($models as $model) {
            $this->createModelStub($model);
        }

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateSalesFlow();

        $relFindings = $this->filterByCheck($findings, 'flow_relationship_missing');
        $pairs = array_map(
            fn(AuditFinding $f) => $f->metadata['child_model'] . '->' . $f->metadata['expected_method'],
            $relFindings
        );

        $this->assertContains('SalesOrder->quotation', $pairs);
        $this->assertContains('DeliveryOrder->salesOrder', $pairs);
        $this->assertContains('Invoice->salesOrder', $pairs);
        $this->assertContains('Payment->invoice', $pairs);

        foreach ($relFindings as $f) {
            $this->assertSame(Severity::High, $f->severity);
        }
    }

    public function test_sales_flow_no_relationship_findings_when_all_defined(): void
    {
        $this->createModelStub('Quotation');
        $this->createModelWithRelationship('SalesOrder', 'quotation');
        $this->createModelWithRelationship('DeliveryOrder', 'salesOrder');
        $this->createModelWithRelationship('Invoice', 'salesOrder');
        $this->createModelWithRelationship('Payment', 'invoice');
        $this->createModelStub('JournalEntry');

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateSalesFlow();

        $relFindings = $this->filterByCheck($findings, 'flow_relationship_missing');
        $this->assertCount(0, $relFindings);
    }

    public function test_sales_flow_detects_missing_service_methods(): void
    {
        // No services at all
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateSalesFlow();

        $serviceFindings = $this->filterByCheck($findings, 'flow_service_missing');
        $serviceNames = array_map(
            fn(AuditFinding $f) => $f->metadata['service'],
            $serviceFindings
        );

        $this->assertContains('TransactionStateMachine', $serviceNames);
        $this->assertContains('GlPostingService', $serviceNames);

        foreach ($serviceFindings as $f) {
            $this->assertSame(Severity::Critical, $f->severity);
        }
    }

    public function test_sales_flow_detects_missing_methods_in_existing_services(): void
    {
        // Create services without the required methods
        $this->createServiceStub('TransactionStateMachine', []);
        $this->createServiceStub('GlPostingService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateSalesFlow();

        $methodFindings = $this->filterByCheck($findings, 'flow_service_method_missing');
        $methods = array_map(
            fn(AuditFinding $f) => $f->metadata['service'] . '::' . $f->metadata['method'],
            $methodFindings
        );

        // State machine methods
        $this->assertContains('TransactionStateMachine::postSalesOrder', $methods);
        $this->assertContains('TransactionStateMachine::postInvoice', $methods);
        $this->assertContains('TransactionStateMachine::cancelSalesOrder', $methods);
        $this->assertContains('TransactionStateMachine::cancelInvoice', $methods);

        // GL posting methods
        $this->assertContains('GlPostingService::postSalesOrder', $methods);
        $this->assertContains('GlPostingService::postInvoiceCreated', $methods);
        $this->assertContains('GlPostingService::postInvoicePayment', $methods);
        $this->assertContains('GlPostingService::postSalesPayment', $methods);

        foreach ($methodFindings as $f) {
            $this->assertSame(Severity::High, $f->severity);
        }
    }

    // ── Purchasing Flow (Requirement 3.2) ────────────────────────

    public function test_purchasing_flow_detects_missing_models(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePurchasingFlow();

        $missingModels = $this->filterByCheck($findings, 'flow_model_missing');
        $names = array_map(fn(AuditFinding $f) => $f->metadata['model'], $missingModels);

        $this->assertContains('PurchaseRequisition', $names);
        $this->assertContains('Rfq', $names);
        $this->assertContains('PurchaseOrder', $names);
        $this->assertContains('GoodsReceipt', $names);
        $this->assertContains('Invoice', $names);
        $this->assertContains('Payment', $names);
        $this->assertContains('JournalEntry', $names);
    }

    public function test_purchasing_flow_detects_missing_relationships(): void
    {
        $models = ['PurchaseRequisition', 'Rfq', 'PurchaseOrder', 'GoodsReceipt', 'Invoice', 'Payment', 'JournalEntry'];
        foreach ($models as $model) {
            $this->createModelStub($model);
        }

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePurchasingFlow();

        $relFindings = $this->filterByCheck($findings, 'flow_relationship_missing');
        $pairs = array_map(
            fn(AuditFinding $f) => $f->metadata['child_model'] . '->' . $f->metadata['expected_method'],
            $relFindings
        );

        $this->assertContains('Rfq->purchaseRequisition', $pairs);
        $this->assertContains('PurchaseOrder->rfq', $pairs);
        $this->assertContains('GoodsReceipt->purchaseOrder', $pairs);
    }

    public function test_purchasing_flow_detects_missing_service_methods(): void
    {
        $this->createServiceStub('TransactionStateMachine', []);
        $this->createServiceStub('GlPostingService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePurchasingFlow();

        $methodFindings = $this->filterByCheck($findings, 'flow_service_method_missing');
        $methods = array_map(
            fn(AuditFinding $f) => $f->metadata['service'] . '::' . $f->metadata['method'],
            $methodFindings
        );

        $this->assertContains('TransactionStateMachine::postPurchaseOrder', $methods);
        $this->assertContains('TransactionStateMachine::cancelPurchaseOrder', $methods);
        $this->assertContains('GlPostingService::postPurchaseReceived', $methods);
        $this->assertContains('GlPostingService::postPurchasePayment', $methods);
    }

    // ── Payroll Flow (Requirement 3.3) ───────────────────────────

    public function test_payroll_flow_detects_missing_models(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePayrollFlow();

        $missingModels = $this->filterByCheck($findings, 'flow_model_missing');
        $names = array_map(fn(AuditFinding $f) => $f->metadata['model'], $missingModels);

        $this->assertContains('Attendance', $names);
        $this->assertContains('Overtime', $names);
        $this->assertContains('SalaryComponent', $names);
        $this->assertContains('PayrollRun', $names);
        $this->assertContains('Payslip', $names);
        $this->assertContains('JournalEntry', $names);
    }

    public function test_payroll_flow_checks_payroll_calculation_service_methods(): void
    {
        $this->createServiceStub('PayrollCalculationService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePayrollFlow();

        $methodFindings = $this->filterByCheck($findings, 'flow_service_method_missing');
        $methods = array_map(
            fn(AuditFinding $f) => $f->metadata['method'],
            array_filter($methodFindings, fn(AuditFinding $f) => $f->metadata['service'] === 'PayrollCalculationService')
        );

        $this->assertContains('calculateBpjsKesehatan', $methods);
        $this->assertContains('calculateBpjsKetenagakerjaan', $methods);
        $this->assertContains('calculatePph21', $methods);
        $this->assertContains('calculateNetSalary', $methods);
    }

    public function test_payroll_flow_no_method_findings_when_all_defined(): void
    {
        $this->createServiceStub('PayrollCalculationService', [
            'calculateBpjsKesehatan',
            'calculateBpjsKetenagakerjaan',
            'calculatePph21',
            'calculateNetSalary',
        ]);
        // Create models to avoid model-missing findings
        foreach (['Attendance', 'Overtime', 'SalaryComponent', 'PayrollRun', 'Payslip', 'JournalEntry'] as $m) {
            $this->createModelStub($m);
        }
        // PayrollRun needs employee relationship
        $this->createModelWithRelationship('PayrollRun', 'employee');

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePayrollFlow();

        $methodFindings = $this->filterByCheck($findings, 'flow_service_method_missing');
        $payrollMethodFindings = array_filter(
            $methodFindings,
            fn(AuditFinding $f) => $f->metadata['service'] === 'PayrollCalculationService'
        );
        $this->assertCount(0, $payrollMethodFindings);
    }

    // ── Inventory Flow (Requirement 3.4) ─────────────────────────

    public function test_inventory_flow_detects_missing_models(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateInventoryFlow();

        $missingModels = $this->filterByCheck($findings, 'flow_model_missing');
        $names = array_map(fn(AuditFinding $f) => $f->metadata['model'], $missingModels);

        $this->assertContains('Product', $names);
        $this->assertContains('StockMovement', $names);
        $this->assertContains('StockTransfer', $names);
        $this->assertContains('StockOpnameSession', $names);
    }

    public function test_inventory_flow_checks_inventory_costing_service_methods(): void
    {
        $this->createServiceStub('InventoryCostingService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateInventoryFlow();

        $methodFindings = $this->filterByCheck($findings, 'flow_service_method_missing');
        $methods = array_map(
            fn(AuditFinding $f) => $f->metadata['method'],
            array_filter($methodFindings, fn(AuditFinding $f) => $f->metadata['service'] === 'InventoryCostingService')
        );

        $this->assertContains('recordStockIn', $methods);
        $this->assertContains('recordStockOut', $methods);
        $this->assertContains('getCurrentCost', $methods);
        $this->assertContains('valuationReport', $methods);
    }

    public function test_inventory_flow_no_costing_findings_when_all_methods_exist(): void
    {
        $this->createServiceStub('InventoryCostingService', [
            'recordStockIn',
            'recordStockOut',
            'getCurrentCost',
            'valuationReport',
        ]);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateInventoryFlow();

        $methodFindings = array_filter(
            $this->filterByCheck($findings, 'flow_service_method_missing'),
            fn(AuditFinding $f) => $f->metadata['service'] === 'InventoryCostingService'
        );
        $this->assertCount(0, $methodFindings);
    }

    // ── POS Flow (Requirement 3.5) ───────────────────────────────

    public function test_pos_flow_detects_missing_models(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePosFlow();

        $missingModels = $this->filterByCheck($findings, 'flow_model_missing');
        $names = array_map(fn(AuditFinding $f) => $f->metadata['model'], $missingModels);

        $this->assertContains('CashierSession', $names);
        $this->assertContains('PosSale', $names);
        $this->assertContains('PosPayment', $names);
        $this->assertContains('PosReceipt', $names);
    }

    public function test_pos_flow_checks_cashier_session_fields(): void
    {
        // CashierSession without status/opening_balance fields
        $this->createModelStub('CashierSession', "protected \$fillable = ['name'];");
        $this->createModelStub('PosSale');
        $this->createModelStub('PosPayment');
        $this->createModelStub('PosReceipt');
        $this->createServiceStub('GlPostingService', ['postPosSession']);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePosFlow();

        $sessionFindings = $this->filterByCheck($findings, 'pos_session_fields');
        $this->assertCount(1, $sessionFindings);
        $this->assertSame(Severity::Medium, $sessionFindings[0]->severity);
        $this->assertSame('POS', $sessionFindings[0]->metadata['flow']);
    }

    public function test_pos_flow_no_session_field_finding_when_status_present(): void
    {
        $this->createModelStub('CashierSession', "protected \$fillable = ['status', 'opening_balance'];");
        $this->createModelStub('PosSale');
        $this->createModelStub('PosPayment');
        $this->createModelStub('PosReceipt');
        $this->createServiceStub('GlPostingService', ['postPosSession']);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePosFlow();

        $sessionFindings = $this->filterByCheck($findings, 'pos_session_fields');
        $this->assertCount(0, $sessionFindings);
    }

    public function test_pos_flow_checks_gl_posting_service(): void
    {
        $this->createServiceStub('GlPostingService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validatePosFlow();

        $methodFindings = array_filter(
            $this->filterByCheck($findings, 'flow_service_method_missing'),
            fn(AuditFinding $f) => $f->metadata['service'] === 'GlPostingService'
        );
        $methods = array_map(fn(AuditFinding $f) => $f->metadata['method'], $methodFindings);

        $this->assertContains('postPosSession', $methods);
    }

    // ── Approval Flow (Requirement 3.6) ──────────────────────────

    public function test_approval_flow_detects_missing_models(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateApprovalFlow();

        $missingModels = $this->filterByCheck($findings, 'flow_model_missing');
        $names = array_map(fn(AuditFinding $f) => $f->metadata['model'], $missingModels);

        $this->assertContains('ApprovalWorkflow', $names);
        $this->assertContains('ApprovalRequest', $names);
    }

    public function test_approval_flow_checks_workflow_engine_methods(): void
    {
        $this->createServiceStub('WorkflowEngine', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateApprovalFlow();

        $methodFindings = array_filter(
            $this->filterByCheck($findings, 'flow_service_method_missing'),
            fn(AuditFinding $f) => $f->metadata['service'] === 'WorkflowEngine'
        );
        $methods = array_map(fn(AuditFinding $f) => $f->metadata['method'], $methodFindings);

        $this->assertContains('fireEvent', $methods);
        $this->assertContains('executeScheduled', $methods);
    }

    public function test_approval_flow_checks_approval_services_exist(): void
    {
        // No services at all
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateApprovalFlow();

        $serviceFindings = $this->filterByCheck($findings, 'approval_service_missing');
        $serviceNames = array_map(fn(AuditFinding $f) => $f->metadata['service'], $serviceFindings);

        $this->assertContains('WorkflowEngine', $serviceNames);
        $this->assertContains('DocumentApprovalService', $serviceNames);
        $this->assertContains('PoApprovalService', $serviceNames);

        foreach ($serviceFindings as $f) {
            $this->assertSame(Severity::Medium, $f->severity);
        }
    }

    public function test_approval_flow_no_service_missing_when_all_exist(): void
    {
        $this->createServiceStub('WorkflowEngine', ['fireEvent', 'executeScheduled']);
        $this->createServiceStub('DocumentApprovalService', []);
        $this->createServiceStub('PoApprovalService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateApprovalFlow();

        $serviceFindings = $this->filterByCheck($findings, 'approval_service_missing');
        $this->assertCount(0, $serviceFindings);
    }

    public function test_approval_flow_checks_approval_request_relationship(): void
    {
        $this->createModelStub('ApprovalWorkflow');
        $this->createModelStub('ApprovalRequest'); // no workflow() method
        $this->createServiceStub('WorkflowEngine', ['fireEvent', 'executeScheduled']);
        $this->createServiceStub('DocumentApprovalService', []);
        $this->createServiceStub('PoApprovalService', []);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->validateApprovalFlow();

        $relFindings = $this->filterByCheck($findings, 'flow_relationship_missing');
        $this->assertGreaterThanOrEqual(1, count($relFindings));

        $pair = $relFindings[0]->metadata['child_model'] . '->' . $relFindings[0]->metadata['expected_method'];
        $this->assertSame('ApprovalRequest->workflow', $pair);
    }

    // ── Empty Directories ────────────────────────────────────────

    public function test_empty_directories_return_findings_for_missing_models(): void
    {
        $analyzer = $this->makeAnalyzer();

        // Each flow should return findings (missing models/services) but not crash
        $salesFindings = $analyzer->validateSalesFlow();
        $purchasingFindings = $analyzer->validatePurchasingFlow();
        $payrollFindings = $analyzer->validatePayrollFlow();
        $inventoryFindings = $analyzer->validateInventoryFlow();
        $posFindings = $analyzer->validatePosFlow();
        $approvalFindings = $analyzer->validateApprovalFlow();

        // All should return arrays (not throw)
        $this->assertIsArray($salesFindings);
        $this->assertIsArray($purchasingFindings);
        $this->assertIsArray($payrollFindings);
        $this->assertIsArray($inventoryFindings);
        $this->assertIsArray($posFindings);
        $this->assertIsArray($approvalFindings);

        // Each should have at least model-missing findings
        $this->assertNotEmpty($salesFindings);
        $this->assertNotEmpty($purchasingFindings);
        $this->assertNotEmpty($payrollFindings);
        $this->assertNotEmpty($inventoryFindings);
        $this->assertNotEmpty($posFindings);
        $this->assertNotEmpty($approvalFindings);
    }

    public function test_analyze_aggregates_all_flow_findings(): void
    {
        $analyzer = $this->makeAnalyzer();
        $allFindings = $analyzer->analyze();

        $this->assertIsArray($allFindings);
        $this->assertNotEmpty($allFindings);

        // All findings should be AuditFinding instances
        foreach ($allFindings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
        }

        // All findings should have business_flow category
        foreach ($allFindings as $finding) {
            $this->assertSame('business_flow', $finding->category);
        }
    }

    // ── Finding Structure Validation ─────────────────────────────

    public function test_finding_structure_has_required_fields(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertNotEmpty($finding->category);
            $this->assertInstanceOf(Severity::class, $finding->severity);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertIsArray($finding->metadata);
        }
    }

    public function test_finding_metadata_contains_flow_key(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        // Most findings should have a 'flow' key in metadata
        $flowFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => isset($f->metadata['flow'])
        );

        $this->assertNotEmpty($flowFindings);

        $validFlows = ['Sales', 'Purchasing', 'Payroll', 'Inventory', 'POS', 'Approval'];
        foreach ($flowFindings as $f) {
            $this->assertContains($f->metadata['flow'], $validFlows);
        }
    }

    public function test_finding_metadata_contains_check_key(): void
    {
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $validChecks = [
            'flow_model_missing',
            'flow_relationship_missing',
            'flow_service_missing',
            'flow_service_method_missing',
            'approval_service_missing',
            'pos_session_fields',
        ];

        foreach ($findings as $f) {
            if (isset($f->metadata['check'])) {
                $this->assertContains($f->metadata['check'], $validChecks);
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function makeAnalyzer(): BusinessFlowAnalyzer
    {
        return new BusinessFlowAnalyzer(
            modelPath: $this->tempDir . '/app/Models',
            servicePath: $this->tempDir . '/app/Services',
            controllerPath: $this->tempDir . '/app/Http/Controllers',
            basePath: $this->tempDir,
        );
    }

    private function createModelStub(string $name, string $extraContent = ''): void
    {
        $content = "<?php\nnamespace App\\Models;\nclass {$name} {\n    {$extraContent}\n}\n";
        file_put_contents($this->tempDir . "/app/Models/{$name}.php", $content);
    }

    private function createModelWithRelationship(string $name, string $relationshipMethod): void
    {
        $content = <<<PHP
<?php
namespace App\Models;
class {$name} {
    public function {$relationshipMethod}()
    {
        return \$this->belongsTo(Related::class);
    }
}
PHP;
        file_put_contents($this->tempDir . "/app/Models/{$name}.php", $content);
    }

    private function createServiceStub(string $name, array $methods): void
    {
        $methodDefs = '';
        foreach ($methods as $method) {
            $methodDefs .= "    public function {$method}() {}\n";
        }
        $content = "<?php\nnamespace App\\Services;\nclass {$name} {\n{$methodDefs}}\n";
        file_put_contents($this->tempDir . "/app/Services/{$name}.php", $content);
    }

    /**
     * Filter findings by the 'check' metadata key.
     *
     * @param AuditFinding[] $findings
     * @return AuditFinding[]
     */
    private function filterByCheck(array $findings, string $check): array
    {
        return array_values(array_filter(
            $findings,
            fn(AuditFinding $f) => ($f->metadata['check'] ?? null) === $check
        ));
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
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
