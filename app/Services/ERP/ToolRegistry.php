<?php

namespace App\Services\ERP;

class ToolRegistry
{
    protected array $tools = [];
    protected array $executors = [];

    public function __construct(int $tenantId, int $userId)
    {
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
     */
    public function getDeclarations(): array
    {
        return array_values($this->tools);
    }

    /**
     * Eksekusi tool berdasarkan nama dan argumen.
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

        return $executor->$method($args);
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
        ];

        return in_array($toolName, $writeTools);
    }
}
