<?php

namespace App\Services\ERP;

use App\Models\ActivityLog;

class ToolRegistry
{
    protected int $tenantId;
    protected int $userId;
    protected array $tools = [];
    protected array $executors = [];

    public function __construct(int $tenantId, int $userId)
    {
        $this->tenantId = $tenantId;
        $this->userId   = $userId;
        $instances = [
            new InventoryTools($tenantId, $userId),
            new SalesTools($tenantId, $userId),
            new PosTools($tenantId, $userId),
            new PurchasingTools($tenantId, $userId),
            new HrmTools($tenantId, $userId),
            new FinanceTools($tenantId, $userId),
            new OnboardingTools($tenantId, $userId),
            new DashboardTools($tenantId, $userId),
            new ReceivableTools($tenantId, $userId),
            new RecipeTools($tenantId, $userId),
            new ProductionTools($tenantId, $userId),
            new ProjectTools($tenantId, $userId),
            new WarehouseTools($tenantId, $userId),
            new ReportTools($tenantId, $userId),
            new AssetTools($tenantId, $userId),
            new PayrollTools($tenantId, $userId),
            new CrmTools($tenantId, $userId),
            new BudgetTools($tenantId, $userId),
            new DocumentTools($tenantId, $userId),
            new CurrencyTools($tenantId, $userId),
            new TaxTools($tenantId, $userId),
            new LoyaltyTools($tenantId, $userId),
            new ShippingTools($tenantId, $userId),
            new BankTools($tenantId, $userId),
            new BotTools($tenantId, $userId),
            new NotificationTools($tenantId, $userId),
            new ReminderTools($tenantId, $userId),
            new SmartQueryTools($tenantId, $userId),
            new ForecastTools($tenantId, $userId),
            new BulkTools($tenantId, $userId),
            new WhatsAppTools($tenantId, $userId),
            new DocumentGeneratorTools($tenantId, $userId),
            new AppGuideTools($tenantId, $userId),
        ];

        foreach ($instances as $instance) {
            foreach ($instance::definitions() as $def) {
                $this->tools[$def['name']] = $def;
                $this->executors[$def['name']] = $instance;
            }
        }
    }

    /**
     * Kembalikan semua definisi tool dalam format Gemini function declarations.
     * Jika $allowedTools tidak null, hanya kembalikan tool yang ada di daftar tersebut.
     */
    public function getDeclarations(?array $allowedTools = null): array
    {
        if ($allowedTools === null) {
            return array_values($this->tools);
        }

        return array_values(array_filter(
            $this->tools,
            fn($def) => in_array($def['name'], $allowedTools)
        ));
    }

    /**
     * Eksekusi tool berdasarkan nama dan argumen.
     * Write operations otomatis dicatat ke audit log dengan label "AI Action".
     */
    public function execute(string $toolName, array $args): array
    {
        if (!isset($this->executors[$toolName])) {
            return ['status' => 'error', 'message' => "Tool '{$toolName}' tidak dikenali."];
        }

        // Map tool name ke method name (snake_case -> camelCase)
        $method = lcfirst(str_replace('_', '', ucwords($toolName, '_')));
        $executor = $this->executors[$toolName];

        if (!method_exists($executor, $method)) {
            return ['status' => 'error', 'message' => "Method '{$method}' tidak ditemukan."];
        }

        $result = $executor->$method($args);

        // Auto-log write operations ke audit trail
        if ($this->isWriteOperation($toolName) && ($result['status'] ?? '') === 'success') {
            try {
                $description = $this->buildAuditDescription($toolName, $args, $result);
                ActivityLog::recordAi(
                    tenantId:    $this->tenantId,
                    userId:      $this->userId,
                    toolName:    $toolName,
                    description: $description,
                    args:        $args,
                    result:      $result,
                );
            } catch (\Throwable $e) {
                // Jangan sampai audit log failure mengganggu tool execution
                \Illuminate\Support\Facades\Log::warning("ToolRegistry: failed to write AI audit log for [{$toolName}]: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bangun deskripsi audit yang human-readable dari tool call.
     */
    protected function buildAuditDescription(string $toolName, array $args, array $result): string
    {
        // Ambil pesan sukses dari result jika ada
        $resultMsg = $result['message'] ?? null;
        if ($resultMsg) {
            // Strip markdown bold/italic untuk audit log
            $clean = preg_replace('/\*\*(.*?)\*\*/', '$1', $resultMsg);
            $clean = preg_replace('/\*(.*?)\*/', '$1', $clean);
            // Potong jika terlalu panjang
            return mb_substr(strip_tags($clean), 0, 500);
        }

        // Fallback: bangun dari tool name + args
        $label = str_replace('_', ' ', $toolName);
        $key   = array_key_first($args);
        $val   = is_string($args[$key] ?? null) ? $args[$key] : '';
        return ucfirst($label) . ($val ? ": {$val}" : '');
    }

    /**
     * Cek apakah tool ini adalah operasi write ke DB.
     */
    public function isWriteOperation(string $toolName): bool
    {
        $writeTools = [
            'add_stock', 'create_purchase_order', 'auto_reorder',
            'add_transaction', 'create_quick_sale',
            'create_product', 'update_product', 'delete_product',
            'create_customer', 'update_customer',
            'create_supplier', 'update_supplier',
            'create_employee', 'record_attendance', 'record_attendance_bulk',
            'create_warehouse', 'create_expense_category', 'setup_business',
            'update_order_status', 'create_quotation',
            'create_sales_order',
            'record_payment',
            'create_recipe', 'produce_with_recipe',
            'create_work_order', 'update_work_order_status', 'record_production_output',
            'create_project', 'update_project_progress', 'add_project_expense',
            'log_timesheet', 'add_project_task',
            'transfer_stock', 'receive_transfer', 'adjust_stock',
            'apply_industry_template',
            // New modules
            'create_asset', 'schedule_maintenance', 'update_asset_status', 'calculate_depreciation',
            'run_payroll', 'mark_payroll_paid',
            'create_lead', 'update_lead_stage', 'log_crm_activity',
            'create_budget', 'update_budget_realized',
            'delete_document',
            'set_currency_rate',
            'setup_tax_rates', 'record_tax',
            'setup_loyalty_program', 'add_loyalty_points', 'redeem_loyalty_points',
            // Advanced features
            'send_bot_notification',
            'send_email_summary',
            'set_reminder',
            'dismiss_reminder',
            'bulk_update_products',
            'send_whatsapp',
            'generate_document',
            'update_product_image',
            'identify_product_from_image',
            'export_report_pdf',
        ];

        return in_array($toolName, $writeTools);
    }
}
