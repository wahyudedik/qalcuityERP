<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Validates end-to-end business flows by tracing model chains,
 * relationship links, service method existence, and state transitions.
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.8
 */
class BusinessFlowAnalyzer implements AnalyzerInterface
{
    private string $modelPath;

    private string $servicePath;

    private string $controllerPath;

    private string $basePath;

    // ── Sales Flow Chain (Requirement 3.1) ───────────────────────

    /**
     * Models required for the sales flow.
     */
    private const SALES_FLOW_MODELS = [
        'Quotation',
        'SalesOrder',
        'DeliveryOrder',
        'Invoice',
        'Payment',
        'JournalEntry',
    ];

    /**
     * Expected relationships linking the sales flow chain.
     * Format: [child_model => [relationship_method => parent_model]]
     */
    private const SALES_FLOW_RELATIONSHIPS = [
        'SalesOrder' => ['quotation' => 'Quotation'],
        'DeliveryOrder' => ['salesOrder' => 'SalesOrder'],
        'Invoice' => ['salesOrder' => 'SalesOrder'],
        'Payment' => ['invoice' => 'Invoice'],
    ];

    /**
     * GL posting methods expected for the sales flow.
     */
    private const SALES_GL_METHODS = [
        'postSalesOrder',
        'postInvoiceCreated',
        'postInvoicePayment',
        'postSalesPayment',
    ];

    /**
     * State machine methods expected for the sales flow.
     */
    private const SALES_STATE_METHODS = [
        'postSalesOrder',
        'postInvoice',
        'cancelSalesOrder',
        'cancelInvoice',
    ];

    // ── Purchasing Flow Chain (Requirement 3.2) ──────────────────

    private const PURCHASING_FLOW_MODELS = [
        'PurchaseRequisition',
        'Rfq',
        'PurchaseOrder',
        'GoodsReceipt',
        'Invoice',
        'Payment',
        'JournalEntry',
    ];

    private const PURCHASING_FLOW_RELATIONSHIPS = [
        'Rfq' => ['purchaseRequisition' => 'PurchaseRequisition'],
        'PurchaseOrder' => ['rfq' => 'Rfq'],
        'GoodsReceipt' => ['purchaseOrder' => 'PurchaseOrder'],
    ];

    private const PURCHASING_GL_METHODS = [
        'postPurchaseReceived',
        'postPurchasePayment',
    ];

    private const PURCHASING_STATE_METHODS = [
        'postPurchaseOrder',
        'cancelPurchaseOrder',
    ];

    // ── Payroll Flow Chain (Requirement 3.3) ─────────────────────

    private const PAYROLL_FLOW_MODELS = [
        'Attendance',
        'Overtime',
        'SalaryComponent',
        'PayrollRun',
        'Payslip',
        'JournalEntry',
    ];

    private const PAYROLL_CALCULATION_METHODS = [
        'calculateBpjsKesehatan',
        'calculateBpjsKetenagakerjaan',
        'calculatePph21',
        'calculateNetSalary',
    ];

    // ── Inventory Flow Chain (Requirement 3.4) ───────────────────

    private const INVENTORY_FLOW_MODELS = [
        'Product',
        'StockMovement',
        'StockTransfer',
        'StockOpnameSession',
    ];

    private const INVENTORY_COSTING_METHODS = [
        'recordStockIn',
        'recordStockOut',
        'getCurrentCost',
        'valuationReport',
    ];

    // ── POS Flow Chain (Requirement 3.5) ─────────────────────────

    private const POS_FLOW_MODELS = [
        'CashierSession',
        'PosSale',
        'PosPayment',
        'PosReceipt',
    ];

    private const POS_GL_METHODS = [
        'postPosSession',
    ];

    // ── Approval Flow Chain (Requirement 3.6) ────────────────────

    private const APPROVAL_FLOW_MODELS = [
        'ApprovalWorkflow',
        'ApprovalRequest',
    ];

    private const APPROVAL_SERVICES = [
        'WorkflowEngine',
        'DocumentApprovalService',
        'PoApprovalService',
    ];

    private const APPROVAL_ENGINE_METHODS = [
        'fireEvent',
        'executeScheduled',
    ];

    // ── Key Service Files ────────────────────────────────────────

    private const KEY_SERVICES = [
        'TransactionStateMachine' => 'TransactionStateMachine.php',
        'GlPostingService' => 'GlPostingService.php',
        'InventoryCostingService' => 'InventoryCostingService.php',
        'PayrollCalculationService' => 'PayrollCalculationService.php',
        'WorkflowEngine' => 'WorkflowEngine.php',
    ];

    public function __construct(
        ?string $modelPath = null,
        ?string $servicePath = null,
        ?string $controllerPath = null,
        ?string $basePath = null,
    ) {
        if ($basePath !== null) {
            $this->basePath = $basePath;
        } else {
            try {
                $this->basePath = base_path();
            } catch (\Throwable) {
                $this->basePath = getcwd();
            }
        }

        $this->modelPath = $modelPath ?? ($this->basePath.'/app/Models');
        $this->servicePath = $servicePath ?? ($this->basePath.'/app/Services');
        $this->controllerPath = $controllerPath ?? ($this->basePath.'/app/Http/Controllers');
    }

    /**
     * Run the full business flow analysis.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        array_push($findings, ...$this->validateSalesFlow());
        array_push($findings, ...$this->validatePurchasingFlow());
        array_push($findings, ...$this->validatePayrollFlow());
        array_push($findings, ...$this->validateInventoryFlow());
        array_push($findings, ...$this->validatePosFlow());
        array_push($findings, ...$this->validateApprovalFlow());

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'business_flow';
    }

    // ── Sales Flow Validation (Requirement 3.1) ──────────────────

    /**
     * Validate the sales flow chain:
     * Quotation → SalesOrder → DeliveryOrder → Invoice → Payment → JournalEntry
     *
     * Checks:
     * 1. All required models exist
     * 2. Models have proper relationships linking them
     * 3. TransactionStateMachine has sales state methods
     * 4. GlPostingService has sales GL posting methods
     *
     * @return AuditFinding[]
     */
    public function validateSalesFlow(): array
    {
        $findings = [];

        // 1. Check model existence
        array_push(
            $findings,
            ...$this->checkFlowModelsExist('Sales', self::SALES_FLOW_MODELS)
        );

        // 2. Check relationships
        array_push(
            $findings,
            ...$this->checkFlowRelationships('Sales', self::SALES_FLOW_RELATIONSHIPS)
        );

        // 3. Check state machine methods
        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Sales',
                'TransactionStateMachine',
                self::SALES_STATE_METHODS
            )
        );

        // 4. Check GL posting methods
        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Sales',
                'GlPostingService',
                self::SALES_GL_METHODS
            )
        );

        return $findings;
    }

    // ── Purchasing Flow Validation (Requirement 3.2) ─────────────

    /**
     * Validate the purchasing flow chain:
     * PurchaseRequisition → RFQ → PO → GoodsReceipt → Invoice → Payment → JournalEntry
     *
     * @return AuditFinding[]
     */
    public function validatePurchasingFlow(): array
    {
        $findings = [];

        array_push(
            $findings,
            ...$this->checkFlowModelsExist('Purchasing', self::PURCHASING_FLOW_MODELS)
        );

        array_push(
            $findings,
            ...$this->checkFlowRelationships('Purchasing', self::PURCHASING_FLOW_RELATIONSHIPS)
        );

        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Purchasing',
                'TransactionStateMachine',
                self::PURCHASING_STATE_METHODS
            )
        );

        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Purchasing',
                'GlPostingService',
                self::PURCHASING_GL_METHODS
            )
        );

        return $findings;
    }

    // ── Payroll Flow Validation (Requirement 3.3) ────────────────

    /**
     * Validate the payroll flow chain:
     * Attendance → Overtime → Salary Components → PayrollRun → Payslip → JournalEntry
     *
     * @return AuditFinding[]
     */
    public function validatePayrollFlow(): array
    {
        $findings = [];

        array_push(
            $findings,
            ...$this->checkFlowModelsExist('Payroll', self::PAYROLL_FLOW_MODELS)
        );

        // Check PayrollCalculationService methods
        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Payroll',
                'PayrollCalculationService',
                self::PAYROLL_CALCULATION_METHODS
            )
        );

        // Check that PayrollRun has relationship to Attendance or Employee
        $payrollRelationships = [
            'PayrollRun' => ['employee' => 'Employee'],
        ];
        array_push(
            $findings,
            ...$this->checkFlowRelationships('Payroll', $payrollRelationships)
        );

        return $findings;
    }

    // ── Inventory Flow Validation (Requirement 3.4) ──────────────

    /**
     * Validate the inventory flow chain:
     * Product → StockIn → Transfer → StockOut → StockOpname → Costing
     *
     * @return AuditFinding[]
     */
    public function validateInventoryFlow(): array
    {
        $findings = [];

        array_push(
            $findings,
            ...$this->checkFlowModelsExist('Inventory', self::INVENTORY_FLOW_MODELS)
        );

        // Check InventoryCostingService methods
        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Inventory',
                'InventoryCostingService',
                self::INVENTORY_COSTING_METHODS
            )
        );

        // Check StockMovement has relationship to Product and Warehouse
        $inventoryRelationships = [
            'StockMovement' => ['product' => 'Product'],
            'StockTransfer' => ['product' => 'Product'],
        ];
        array_push(
            $findings,
            ...$this->checkFlowRelationships('Inventory', $inventoryRelationships)
        );

        return $findings;
    }

    // ── POS Flow Validation (Requirement 3.5) ────────────────────

    /**
     * Validate the POS flow chain:
     * OpenSession → AddItems → Discount/Loyalty → Payment → Receipt → CloseSession
     *
     * @return AuditFinding[]
     */
    public function validatePosFlow(): array
    {
        $findings = [];

        array_push(
            $findings,
            ...$this->checkFlowModelsExist('POS', self::POS_FLOW_MODELS)
        );

        // Check GL posting for POS
        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'POS',
                'GlPostingService',
                self::POS_GL_METHODS
            )
        );

        // Check CashierSession has status field for open/close tracking
        $sessionFile = $this->findModelFile('CashierSession');
        if ($sessionFile !== null) {
            $source = @file_get_contents($sessionFile);
            if ($source !== false) {
                $hasStatus = (bool) preg_match(
                    '/[\'"]status[\'"]\s*(?:,|=>)|[\'"]opening_balance[\'"]\s*(?:,|=>)/i',
                    $source
                );
                if (! $hasStatus) {
                    $findings[] = new AuditFinding(
                        category: $this->category(),
                        severity: Severity::Medium,
                        title: 'POS flow: CashierSession missing session tracking fields',
                        description: 'CashierSession model does not appear to have status or opening_balance '
                            .'fields in its $fillable array, which are needed for session open/close tracking.',
                        file: $this->relativePath($sessionFile),
                        line: null,
                        recommendation: 'Ensure CashierSession has status, opening_balance, and closing_balance fields.',
                        metadata: [
                            'check' => 'pos_session_fields',
                            'flow' => 'POS',
                        ],
                    );
                }
            }
        }

        return $findings;
    }

    // ── Approval Flow Validation (Requirement 3.6) ───────────────

    /**
     * Validate the approval flow chain:
     * Request → Multi-level Approval → Action Execution
     *
     * @return AuditFinding[]
     */
    public function validateApprovalFlow(): array
    {
        $findings = [];

        array_push(
            $findings,
            ...$this->checkFlowModelsExist('Approval', self::APPROVAL_FLOW_MODELS)
        );

        // Check approval services exist
        foreach (self::APPROVAL_SERVICES as $serviceName) {
            $serviceFile = $this->findServiceFile($serviceName);
            if ($serviceFile === null) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "Approval flow: missing service {$serviceName}",
                    description: "The approval flow requires {$serviceName} but the service file was not found.",
                    file: null,
                    line: null,
                    recommendation: "Create {$serviceName} to handle approval workflow processing.",
                    metadata: [
                        'check' => 'approval_service_missing',
                        'flow' => 'Approval',
                        'service' => $serviceName,
                    ],
                );
            }
        }

        // Check WorkflowEngine methods
        array_push(
            $findings,
            ...$this->checkServiceMethods(
                'Approval',
                'WorkflowEngine',
                self::APPROVAL_ENGINE_METHODS
            )
        );

        // Check ApprovalRequest has relationship to ApprovalWorkflow
        $approvalRelationships = [
            'ApprovalRequest' => ['workflow' => 'ApprovalWorkflow'],
        ];
        array_push(
            $findings,
            ...$this->checkFlowRelationships('Approval', $approvalRelationships)
        );

        return $findings;
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Check that all required models in a flow chain exist.
     *
     * @param  string  $flowName  Human-readable flow name (e.g., 'Sales')
     * @param  string[]  $requiredModels  List of model class names
     * @return AuditFinding[]
     */
    private function checkFlowModelsExist(string $flowName, array $requiredModels): array
    {
        $findings = [];

        foreach ($requiredModels as $modelName) {
            $modelFile = $this->findModelFile($modelName);
            if ($modelFile === null) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "{$flowName} flow: missing model {$modelName}",
                    description: "The {$flowName} flow requires model {$modelName} but it was not found "
                        .'under the models directory. This breaks the end-to-end flow chain.',
                    file: null,
                    line: null,
                    recommendation: "Create the {$modelName} model to complete the {$flowName} flow.",
                    metadata: [
                        'check' => 'flow_model_missing',
                        'flow' => $flowName,
                        'model' => $modelName,
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Check that models have the expected relationships linking them in the flow.
     *
     * @param  string  $flowName  Human-readable flow name
     * @param  array<string, array<string, string>>  $expectedRelationships
     * @return AuditFinding[]
     */
    private function checkFlowRelationships(string $flowName, array $expectedRelationships): array
    {
        $findings = [];

        foreach ($expectedRelationships as $childModel => $relationships) {
            $modelFile = $this->findModelFile($childModel);
            if ($modelFile === null) {
                // Already reported by checkFlowModelsExist
                continue;
            }

            $source = @file_get_contents($modelFile);
            if ($source === false) {
                continue;
            }

            foreach ($relationships as $methodName => $parentModel) {
                $hasRelationship = $this->sourceHasMethod($source, $methodName);

                if (! $hasRelationship) {
                    $findings[] = new AuditFinding(
                        category: $this->category(),
                        severity: Severity::High,
                        title: "{$flowName} flow: {$childModel} missing relationship to {$parentModel}",
                        description: "Model {$childModel} does not define a {$methodName}() relationship "
                            ."method linking it to {$parentModel}. This breaks the {$flowName} flow chain.",
                        file: $this->relativePath($modelFile),
                        line: null,
                        recommendation: "Add a {$methodName}() relationship method to {$childModel} that returns "
                            ."a belongsTo or hasOne relationship to {$parentModel}.",
                        metadata: [
                            'check' => 'flow_relationship_missing',
                            'flow' => $flowName,
                            'child_model' => $childModel,
                            'parent_model' => $parentModel,
                            'expected_method' => $methodName,
                        ],
                    );
                }
            }
        }

        return $findings;
    }

    /**
     * Check that a service class has the expected methods.
     *
     * @param  string  $flowName  Human-readable flow name
     * @param  string  $serviceName  Service class name (without .php)
     * @param  string[]  $expectedMethods
     * @return AuditFinding[]
     */
    private function checkServiceMethods(string $flowName, string $serviceName, array $expectedMethods): array
    {
        $findings = [];

        $serviceFile = $this->findServiceFile($serviceName);
        if ($serviceFile === null) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Critical,
                title: "{$flowName} flow: missing service {$serviceName}",
                description: "The {$flowName} flow requires {$serviceName} but the service file was not found. "
                    .'This is a critical gap in the business flow infrastructure.',
                file: null,
                line: null,
                recommendation: "Create {$serviceName} with the required methods: ".implode(', ', $expectedMethods),
                metadata: [
                    'check' => 'flow_service_missing',
                    'flow' => $flowName,
                    'service' => $serviceName,
                    'expected_methods' => $expectedMethods,
                ],
            );

            return $findings;
        }

        $source = @file_get_contents($serviceFile);
        if ($source === false) {
            return $findings;
        }

        foreach ($expectedMethods as $method) {
            if (! $this->sourceHasMethod($source, $method)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "{$flowName} flow: {$serviceName} missing method {$method}()",
                    description: "{$serviceName} does not define a {$method}() method. "
                        ."This method is required for the {$flowName} flow to function correctly.",
                    file: $this->relativePath($serviceFile),
                    line: null,
                    recommendation: "Add the {$method}() method to {$serviceName}.",
                    metadata: [
                        'check' => 'flow_service_method_missing',
                        'flow' => $flowName,
                        'service' => $serviceName,
                        'method' => $method,
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Check if PHP source code contains a method definition.
     */
    private function sourceHasMethod(string $source, string $methodName): bool
    {
        $pattern = '/function\s+'.preg_quote($methodName, '/').'\s*\(/';

        return (bool) preg_match($pattern, $source);
    }

    /**
     * Find a model file by class name.
     *
     * Searches the models directory (including subdirectories) for a file
     * that declares the given class.
     *
     * @return string|null Absolute path to the model file, or null
     */
    private function findModelFile(string $modelName): ?string
    {
        // Try direct file name first (most common case)
        $directPath = $this->modelPath.'/'.$modelName.'.php';
        if (is_file($directPath)) {
            return $directPath;
        }

        // Search recursively in subdirectories
        $files = $this->discoverPhpFiles($this->modelPath);
        foreach ($files as $file) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            if ($basename === $modelName) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Find a service file by class name.
     *
     * Searches the services directory (including subdirectories) for a file
     * that matches the given service name.
     *
     * @return string|null Absolute path to the service file, or null
     */
    private function findServiceFile(string $serviceName): ?string
    {
        // Try direct file name first
        $directPath = $this->servicePath.'/'.$serviceName.'.php';
        if (is_file($directPath)) {
            return $directPath;
        }

        // Search recursively in subdirectories
        $files = $this->discoverPhpFiles($this->servicePath);
        foreach ($files as $file) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            if ($basename === $serviceName) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Recursively discover all PHP files in a directory.
     *
     * @return string[]
     */
    private function discoverPhpFiles(string $directory): array
    {
        $files = [];

        if (! is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Convert an absolute path to a relative path from the project root.
     */
    private function relativePath(string $absolutePath): string
    {
        $normalised = str_replace('\\', '/', $absolutePath);
        $basePath = str_replace('\\', '/', $this->basePath).'/';

        if (str_starts_with($normalised, $basePath)) {
            return substr($normalised, strlen($basePath));
        }

        return $normalised;
    }
}
