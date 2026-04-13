<?php

namespace App\Services\ERP;

use App\Models\ActivityLog;
use App\Services\AiCommandValidator;
use App\Services\AI\IntentDetector;
use Illuminate\Support\Facades\Log;

class ToolRegistry
{
    // BUG-AI-002 FIX: Static cache to prevent unnecessary object creation
    protected static array $registryCache = [];
    protected static array $toolsCache = [];
    protected static array $executorsCache = [];

    protected int $tenantId;
    protected int $userId;
    protected array $tools = [];
    protected array $executors = [];
    protected AiCommandValidator $validator;

    public function __construct(int $tenantId, int $userId)
    {
        $this->tenantId = $tenantId;
        $this->userId = $userId;
        $this->validator = new AiCommandValidator();

        // BUG-AI-002 FIX: Use cache key based on tenant+user
        $cacheKey = "{$tenantId}:{$userId}";

        // Check if tools are already cached for this tenant+user
        if (isset(self::$toolsCache[$cacheKey]) && isset(self::$executorsCache[$cacheKey])) {
            $this->tools = self::$toolsCache[$cacheKey];
            $this->executors = self::$executorsCache[$cacheKey];
            // Always feed definitions to validator (even from cache)
            $this->validator->setToolDefinitions($this->tools);
            return; // Skip expensive object creation!
        }

        // Tool class definitions (lightweight, just class names)
        $toolClasses = [
            InventoryTools::class,
            SalesTools::class,
            PosTools::class,
            PurchasingTools::class,
            HrmTools::class,
            FinanceTools::class,
            OnboardingTools::class,
            DashboardTools::class,
            ReceivableTools::class,
            RecipeTools::class,
            ProductionTools::class,
            ProjectTools::class,
            WarehouseTools::class,
            ReportTools::class,
            AssetTools::class,
            PayrollTools::class,
            CrmTools::class,
            BudgetTools::class,
            DocumentTools::class,
            CurrencyTools::class,
            TaxTools::class,
            LoyaltyTools::class,
            ShippingTools::class,
            BankTools::class,
            BotTools::class,
            NotificationTools::class,
            ReminderTools::class,
            SmartQueryTools::class,
            ForecastTools::class,
            BulkTools::class,
            WhatsAppTools::class,
            DocumentGeneratorTools::class,
            AppGuideTools::class,
            ConcreteMixTools::class,
            FarmTools::class,
            AdvisorTools::class,
        ];

        // Instantiate and cache tools
        $tools = [];
        $executors = [];

        foreach ($toolClasses as $toolClass) {
            // Create instance only once per tenant+user
            $instance = new $toolClass($tenantId, $userId);

            foreach ($instance::definitions() as $def) {
                $tools[$def['name']] = $def;
                $executors[$def['name']] = $instance;
            }
        }

        // Cache for this tenant+user combination
        $this->tools = $tools;
        $this->executors = $executors;
        self::$toolsCache[$cacheKey] = $tools;
        self::$executorsCache[$cacheKey] = $executors;

        // Feed all tool definitions to the validator so it can validate args
        $this->validator->setToolDefinitions($this->tools);
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
     * Get tool declarations filtered by intent.
     * 
     * This optimizes AI response time by only sending relevant tools
     * instead of all 100+ tools every request.
     * 
     * @param string $intent Detected intent (e.g., 'sales', 'inventory')
     * @param array|null $allowedTools User's allowed tools (optional filter)
     * @return array Filtered tool declarations
     */
    public function getDeclarationsForIntent(string $intent, ?array $allowedTools = null): array
    {
        // If intent is 'general', return all tools (fallback behavior)
        if ($intent === 'general') {
            return $this->getDeclarations($allowedTools);
        }

        // Get tool classes for this intent
        $detector = new IntentDetector();
        $toolClasses = $detector->getToolClassesForIntent($intent);

        // If no mapping found, fallback to all tools
        if (empty($toolClasses)) {
            Log::info("ToolRegistry: No tool mapping for intent '{$intent}', using all tools");
            return $this->getDeclarations($allowedTools);
        }

        // Filter tools by the classes mapped to this intent
        $relevantTools = array_filter(
            $this->tools,
            function ($def) use ($toolClasses) {
                // Get the class name from executor
                $toolName = $def['name'];
                if (!isset($this->executors[$toolName])) {
                    return false;
                }

                $executorClass = get_class($this->executors[$toolName]);
                $executorShortName = class_basename($executorClass);

                return in_array($executorShortName, $toolClasses);
            }
        );

        // Apply allowedTools filter if provided
        if ($allowedTools !== null) {
            $relevantTools = array_filter(
                $relevantTools,
                fn($def) => in_array($def['name'], $allowedTools)
            );
        }

        $filtered = array_values($relevantTools);
        $total = count($this->tools);
        $filteredCount = count($filtered);

        Log::info("ToolRegistry: Intent '{$intent}' filtered tools from {$total} to {$filteredCount}");

        return $filtered;
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

        // VALIDATION: Validate and sanitize command before execution
        $validationResult = $this->validator->validate($toolName, $args);

        if (!$validationResult['valid']) {
            Log::warning('ToolRegistry: Blocked invalid AI command', [
                'tool' => $toolName,
                'user_id' => $this->userId,
                'tenant_id' => $this->tenantId,
                'errors' => $validationResult['errors'],
                'original_args' => $args,
            ]);

            return [
                'status' => 'error',
                'message' => 'Validasi perintah gagal: ' . implode(', ', $validationResult['errors']),
            ];
        }

        // Use sanitized arguments
        $args = $validationResult['sanitized'];

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
                    tenantId: $this->tenantId,
                    userId: $this->userId,
                    toolName: $toolName,
                    description: $description,
                    args: $args,
                    result: $result,
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
        $key = array_key_first($args);
        $val = is_string($args[$key] ?? null) ? $args[$key] : '';
        return ucfirst($label) . ($val ? ": {$val}" : '');
    }

    /**
     * Cek apakah tool ini adalah operasi write ke DB.
     */
    public function isWriteOperation(string $toolName): bool
    {
        $writeTools = [
            'add_stock',
            'create_purchase_order',
            'auto_reorder',
            'add_transaction',
            'create_quick_sale',
            'create_product',
            'update_product',
            'delete_product',
            'create_customer',
            'update_customer',
            'create_supplier',
            'update_supplier',
            'create_employee',
            'record_attendance',
            'record_attendance_bulk',
            'create_warehouse',
            'create_expense_category',
            'setup_business',
            'update_order_status',
            'create_quotation',
            'create_sales_order',
            'record_payment',
            'create_recipe',
            'produce_with_recipe',
            'create_work_order',
            'update_work_order_status',
            'record_production_output',
            'create_project',
            'update_project_progress',
            'add_project_expense',
            'log_timesheet',
            'add_project_task',
            'add_rab_item',
            'record_rab_actual',
            'record_volume_progress',
            'setup_concrete_standards',
            'create_mix_design',
            'create_farm_plot',
            'update_plot_status',
            'record_farm_activity',
            'start_crop_cycle',
            'advance_crop_phase',
            'log_harvest',
            'add_livestock',
            'record_livestock_movement',
            'record_livestock_health',
            'record_feed',
            'transfer_stock',
            'receive_transfer',
            'adjust_stock',
            'apply_industry_template',
            // New modules
            'create_asset',
            'schedule_maintenance',
            'update_asset_status',
            'calculate_depreciation',
            'run_payroll',
            'mark_payroll_paid',
            'create_lead',
            'update_lead_stage',
            'log_crm_activity',
            'create_budget',
            'update_budget_realized',
            'delete_document',
            'set_currency_rate',
            'setup_tax_rates',
            'record_tax',
            'setup_loyalty_program',
            'add_loyalty_points',
            'redeem_loyalty_points',
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

    /**
     * BUG-AI-002 FIX: Clear static cache (useful for testing/memory management)
     */
    public static function clearCache(): void
    {
        self::$registryCache = [];
        self::$toolsCache = [];
        self::$executorsCache = [];
    }

    /**
     * BUG-AI-002 FIX: Get cache statistics for monitoring
     */
    public static function getCacheStats(): array
    {
        return [
            'cached_registries' => count(self::$toolsCache),
            'cached_keys' => array_keys(self::$toolsCache),
            'memory_usage' => strlen(serialize(self::$toolsCache)) + strlen(serialize(self::$executorsCache)),
        ];
    }

    /**
     * BUG-AI-002 FIX: Check if registry is cached for tenant+user
     */
    public static function isCached(int $tenantId, int $userId): bool
    {
        $cacheKey = "{$tenantId}:{$userId}";
        return isset(self::$toolsCache[$cacheKey]);
    }
}
