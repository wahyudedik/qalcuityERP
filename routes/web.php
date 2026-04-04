<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantUserController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\SuperAdmin\PlanController as SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\MonitoringController as SuperAdminMonitoringController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Pos\PaymentUIController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\HrmController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\CrmAiController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReceivablesController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\LandedCostController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\ProjectBillingController;
use App\Http\Controllers\SubscriptionBillingController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PeriodLockController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

Route::get('/offline', fn() => response()->file(public_path('offline.html')))->name('offline');

// API Documentation (static files served via route for Herd/Valet compatibility)
Route::get('/api-docs/{path?}', function ($path = 'index.html') {
    $filePath = public_path("api-docs/{$path}");

    if (!file_exists($filePath) || !is_file($filePath)) {
        abort(404);
    }

    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $contentType = match ($extension) {
        'html' => 'text/html; charset=utf-8',
        'json' => 'application/json',
        'yaml', 'yml' => 'text/yaml',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        default => 'text/plain'
    };

    return response()->file($filePath, [
        'Content-Type' => $contentType,
    ]);
})->where('path', '.*')->name('api-docs');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/dashboard/refresh-insights', [DashboardController::class, 'refreshInsights'])
        ->name('dashboard.refresh-insights');
    Route::post('/dashboard/anomalies/{anomaly}/acknowledge', [DashboardController::class, 'acknowledgeAnomaly'])
        ->name('dashboard.anomaly.acknowledge');
    Route::post('/dashboard/widgets/save', [DashboardController::class, 'saveWidgets'])
        ->name('dashboard.widgets.save');
    Route::post('/dashboard/widgets/reset', [DashboardController::class, 'resetWidgets'])
        ->name('dashboard.widgets.reset');

    // Custom widget builder (admin/manager only)
    Route::get('/dashboard/custom-widgets', [DashboardController::class, 'customWidgetsList'])->name('dashboard.custom-widgets.list');
    Route::post('/dashboard/custom-widgets', [DashboardController::class, 'customWidgetStore'])->name('dashboard.custom-widgets.store');
    Route::post('/dashboard/custom-widgets/preview', [DashboardController::class, 'customWidgetPreview'])->name('dashboard.custom-widgets.preview');
    Route::get('/dashboard/custom-widgets/{customWidget}', [DashboardController::class, 'customWidgetShow'])->name('dashboard.custom-widgets.show');
    Route::put('/dashboard/custom-widgets/{customWidget}', [DashboardController::class, 'customWidgetUpdate'])->name('dashboard.custom-widgets.update');
    Route::delete('/dashboard/custom-widgets/{customWidget}', [DashboardController::class, 'customWidgetDelete'])->name('dashboard.custom-widgets.delete');
});

// Onboarding wizard
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    Route::post('/onboarding/ai-chat', [OnboardingController::class, 'aiChat'])->name('onboarding.ai-chat')->middleware(['ai.rate', 'ai.quota']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Chat / AI ERP
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::post('/send', [ChatController::class, 'send'])->name('send')->middleware(['ai.rate', 'ai.quota']);
        Route::post('/send-media', [ChatController::class, 'sendMedia'])->name('send-media')->middleware(['ai.rate', 'ai.quota']);
        Route::get('/{session}/messages', [ChatController::class, 'messages'])->name('messages')->middleware('tenant.isolation');
        Route::patch('/{session}/rename', [ChatController::class, 'rename'])->name('rename')->middleware('tenant.isolation');
        Route::delete('/{session}', [ChatController::class, 'destroy'])->name('destroy')->middleware('tenant.isolation');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/preferences', [NotificationPreferenceController::class, 'index'])->name('preferences');
        Route::post('/preferences', [NotificationPreferenceController::class, 'update'])->name('preferences.update');
        Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });

    // Push Subscription (browser push notifications)
    Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Popup Ad dismiss (record view)
    Route::post('/popup-ads/{ad}/dismiss', [\App\Http\Controllers\PopupAdDismissController::class, 'store'])->name('popup-ads.dismiss');

    // Gamification
    Route::prefix('gamification')->name('gamification.')->group(function () {
        Route::get('/', [GamificationController::class, 'index'])->name('index');
        Route::get('/achievements', [GamificationController::class, 'achievements'])->name('achievements');
        Route::get('/leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/points', [GamificationController::class, 'pointsHistory'])->name('points');
    });

    // ============================================
    // BARCODE PRINTING & SCANNING ROUTES
    // ============================================

    // Barcode Printing (Products)
    Route::prefix('barcode')->name('barcode.')->group(function () {
        // Print labels for products
        Route::post('/print', [BarcodeController::class, 'print'])->name('print');
        // Auto-generate missing barcodes
        Route::post('/auto-generate', [BarcodeController::class, 'autoGenerate'])->name('auto-generate');
        // Preview single product barcode
        Route::get('/products/{product}', [BarcodeController::class, 'show'])->name('products.show');
    });

    // Stock Movements with Barcode Scanning
    Route::prefix('inventory/movements')->name('inventory.movements.')->group(function () {
        // Create movement with scanner
        Route::get('/create', [StockMovementController::class, 'create'])->name('create');
        // Store movement
        Route::post('/', [StockMovementController::class, 'store'])->name('store');
        // List all movements
        Route::get('/', [StockMovementController::class, 'index'])->name('index');
        // API: Lookup product by barcode
        Route::get('/lookup-barcode', [StockMovementController::class, 'lookupByBarcode'])->name('lookup-barcode');
        // API: Get product stock at warehouse
        Route::get('/stock', [StockMovementController::class, 'getProductStock'])->name('stock');
    });

    // Tenant User Management (admin only)
    Route::prefix('users')->name('tenant.users.')->middleware('role:admin')->group(function () {
        Route::get('/', [TenantUserController::class, 'index'])->name('index');
        Route::get('/create', [TenantUserController::class, 'create'])->name('create');
        Route::post('/', [TenantUserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [TenantUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [TenantUserController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle', [TenantUserController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{user}', [TenantUserController::class, 'destroy'])->name('destroy');
        // Granular permissions
        Route::get('/{user}/permissions', [TenantUserController::class, 'permissions'])->name('permissions');
        Route::post('/{user}/permissions', [TenantUserController::class, 'savePermissions'])->name('permissions.save');
        Route::delete('/{user}/permissions', [TenantUserController::class, 'resetPermissions'])->name('permissions.reset');
    });

    // Super Admin Panel
    Route::prefix('super-admin')->name('super-admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/tenants', [SuperAdminTenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/{tenant}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
        Route::patch('/tenants/{tenant}/toggle', [SuperAdminTenantController::class, 'toggleActive'])->name('tenants.toggle');
        Route::patch('/tenants/{tenant}/plan', [SuperAdminTenantController::class, 'updatePlan'])->name('tenants.update-plan');
        Route::delete('/tenants/{tenant}', [SuperAdminTenantController::class, 'destroy'])->name('tenants.destroy');

        // Plan management
        Route::get('/plans', [SuperAdminPlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [SuperAdminPlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [SuperAdminPlanController::class, 'store'])->name('plans.store');
        Route::post('/plans/seed', [SuperAdminPlanController::class, 'seed'])->name('plans.seed');
        Route::get('/plans/{plan}/edit', [SuperAdminPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [SuperAdminPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [SuperAdminPlanController::class, 'destroy'])->name('plans.destroy');
        Route::patch('/plans/{plan}/toggle', [SuperAdminPlanController::class, 'toggleActive'])->name('plans.toggle');

        // Monitoring
        Route::get('/monitoring', [SuperAdminMonitoringController::class, 'index'])->name('monitoring.index');
        Route::post('/monitoring/errors/{error}/resolve', [SuperAdminMonitoringController::class, 'resolveError'])->name('monitoring.resolve-error');
        Route::post('/monitoring/errors/resolve-all', [SuperAdminMonitoringController::class, 'resolveAllErrors'])->name('monitoring.resolve-all');
        Route::delete('/monitoring/errors/{error}', [SuperAdminMonitoringController::class, 'deleteError'])->name('monitoring.delete-error');
        Route::post('/monitoring/errors/clear', [SuperAdminMonitoringController::class, 'clearErrors'])->name('monitoring.clear-errors');
        Route::get('/monitoring/health.json', [SuperAdminMonitoringController::class, 'healthJson'])->name('monitoring.health-json');

        // Error Logs Management (Enhanced)
        Route::prefix('error-logs')->name('error-logs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ErrorLogController::class, 'index'])->name('index');
            Route::get('/{errorLog}', [\App\Http\Controllers\Admin\ErrorLogController::class, 'show'])->name('show');
            Route::post('/{errorLog}/resolve', [\App\Http\Controllers\Admin\ErrorLogController::class, 'resolve'])->name('resolve');
            Route::post('/bulk-resolve', [\App\Http\Controllers\Admin\ErrorLogController::class, 'bulkResolve'])->name('bulk-resolve');
            Route::post('/test-alert', [\App\Http\Controllers\Admin\ErrorLogController::class, 'testAlert'])->name('test-alert');
        });

        // Affiliate Management
        Route::get('/affiliates', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'index'])->name('affiliates.index');
        Route::post('/affiliates', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'store'])->name('affiliates.store');
        Route::patch('/affiliates/{affiliate}/toggle', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'toggleActive'])->name('affiliates.toggle');
        Route::get('/affiliates/commissions', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'commissions'])->name('affiliates.commissions');
        Route::patch('/affiliates/commissions/{affiliateCommission}/approve', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'approveCommission'])->name('affiliates.commissions.approve');
        Route::patch('/affiliates/commissions/{affiliateCommission}/reject', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'rejectCommission'])->name('affiliates.commissions.reject');
        Route::get('/affiliates/payouts', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'payouts'])->name('affiliates.payouts');
        Route::patch('/affiliates/payouts/{affiliatePayout}/approve', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'approvePayout'])->name('affiliates.payouts.approve');
        Route::patch('/affiliates/payouts/{affiliatePayout}/reject', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'rejectPayout'])->name('affiliates.payouts.reject');
        Route::get('/affiliates/audit-logs', [\App\Http\Controllers\SuperAdmin\AffiliateManagementController::class, 'auditLogs'])->name('affiliates.audit-logs');

        // Popup Ads
        Route::prefix('popup-ads')->name('popup-ads.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'store'])->name('store');
            Route::get('/{ad}/edit', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'edit'])->name('edit');
            Route::put('/{ad}', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'update'])->name('update');
            Route::patch('/{ad}/toggle', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'toggle'])->name('toggle');
            Route::delete('/{ad}', [\App\Http\Controllers\SuperAdmin\PopupAdController::class, 'destroy'])->name('destroy');
        });
    });

    // Subscription info (tenant only)
    Route::get('/subscription', [SubscriptionController::class, 'index'])
        ->name('subscription.index')
        ->withoutMiddleware(\App\Http\Middleware\CheckTenantActive::class);

    // Affiliate Dashboard (for affiliate role users)
    Route::prefix('affiliate')->name('affiliate.')->middleware('role:affiliate')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AffiliateDashboardController::class, 'index'])->name('dashboard');
        Route::put('/profile', [\App\Http\Controllers\AffiliateDashboardController::class, 'updateProfile'])->name('profile');
        Route::post('/withdraw', [\App\Http\Controllers\AffiliateDashboardController::class, 'requestWithdraw'])->name('withdraw');
    });

    // Subscription expired page (tidak perlu auth, tapi perlu session)
    Route::get('/subscription/expired', fn() => view('subscription.expired'))->name('subscription.expired')->withoutMiddleware(\App\Http\Middleware\CheckTenantActive::class);

    // Reports & Export (admin + manager only)
    Route::prefix('reports')->name('reports.')->middleware(['role:admin,manager', 'throttle:export'])->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales/excel', [ReportController::class, 'exportSalesExcel'])->name('sales.excel');
        Route::get('/sales/pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.pdf');
        Route::get('/finance/excel', [ReportController::class, 'exportFinanceExcel'])->name('finance.excel');
        Route::get('/finance/pdf', [ReportController::class, 'exportFinancePdf'])->name('finance.pdf');
        Route::get('/inventory/excel', [ReportController::class, 'exportInventoryExcel'])->name('inventory.excel');
        Route::get('/inventory/pdf', [ReportController::class, 'exportInventoryPdf'])->name('inventory.pdf');
        Route::get('/hrm/excel', [ReportController::class, 'exportHrmExcel'])->name('hrm.excel');
        Route::get('/hrm/pdf', [ReportController::class, 'exportHrmPdf'])->name('hrm.pdf');
        Route::get('/receivables/excel', [ReportController::class, 'exportReceivablesExcel'])->name('receivables.excel');
        Route::get('/receivables/pdf', [ReportController::class, 'exportReceivablesPdf'])->name('receivables.pdf');
        Route::get('/profit-loss/pdf', [ReportController::class, 'exportProfitLossPdf'])->name('profit-loss.pdf');
        Route::get('/income-statement/excel', [ReportController::class, 'exportIncomeStatementExcel'])->name('income-statement.excel');
        Route::get('/payroll/excel', [ReportController::class, 'exportPayrollExcel'])->name('payroll.excel');
        Route::get('/aging/excel', [ReportController::class, 'exportAgingExcel'])->name('aging.excel');

        // Balance Sheet (Neraca)
        Route::get('/balance-sheet/excel', [ReportController::class, 'exportBalanceSheetExcel'])->name('balance-sheet.excel');
        Route::get('/balance-sheet/pdf', [ReportController::class, 'exportBalanceSheetPdf'])->name('balance-sheet.pdf');

        // Cash Flow Statement (Arus Kas)
        Route::get('/cash-flow/excel', [ReportController::class, 'exportCashFlowExcel'])->name('cash-flow.excel');
        Route::get('/cash-flow/pdf', [ReportController::class, 'exportCashFlowPdf'])->name('cash-flow.pdf');

        // Budget vs Actual
        Route::get('/budget/excel', [ReportController::class, 'exportBudgetExcel'])->name('budget.excel');
        Route::get('/budget/pdf', [ReportController::class, 'exportBudgetPdf'])->name('budget.pdf');

        // Cash Flow Projection
        Route::get('/cash-flow-projection', [ReportController::class, 'cashFlowProjection'])->name('cash-flow-projection');
        Route::get('/cash-flow-projection/data', [ReportController::class, 'cashFlowProjectionData'])->name('cash-flow-projection.data');
    });

    // KPI Dashboard (admin + manager)
    Route::prefix('kpi')->name('kpi.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [KpiController::class, 'index'])->name('index');
        Route::post('/', [KpiController::class, 'store'])->name('store');
        Route::delete('/{kpiTarget}', [KpiController::class, 'destroy'])->name('destroy');
        Route::get('/drilldown/{metric}', [KpiController::class, 'drilldown'])->name('drilldown');
    });

    // Module Settings (admin only)
    Route::prefix('settings/modules')->name('settings.modules.')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\ModuleSettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\ModuleSettingsController::class, 'update'])->name('update');
        Route::get('/recommend', [\App\Http\Controllers\ModuleSettingsController::class, 'recommend'])->name('recommend');
    });

    // Company Profile (admin only)
    Route::prefix('settings/company-profile')->name('company-profile.')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\CompanyProfileController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\CompanyProfileController::class, 'update'])->name('update');
        Route::delete('/images/{field}', [\App\Http\Controllers\CompanyProfileController::class, 'removeLogo'])->name('remove-image');
        Route::post('/templates', [\App\Http\Controllers\CompanyProfileController::class, 'storeTemplate'])->name('templates.store');
        Route::put('/templates/{template}', [\App\Http\Controllers\CompanyProfileController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('/templates/{template}', [\App\Http\Controllers\CompanyProfileController::class, 'destroyTemplate'])->name('templates.destroy');
    });

    // API Settings (admin only)
    Route::prefix('settings/api')->name('api-settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\ApiSettingsController::class, 'index'])->name('index');
        Route::post('/tokens', [\App\Http\Controllers\ApiSettingsController::class, 'storeToken'])->name('tokens.store');
        Route::patch('/tokens/{apiToken}/revoke', [\App\Http\Controllers\ApiSettingsController::class, 'revokeToken'])->name('tokens.revoke');
        Route::delete('/tokens/{apiToken}', [\App\Http\Controllers\ApiSettingsController::class, 'destroyToken'])->name('tokens.destroy');
        Route::post('/webhooks', [\App\Http\Controllers\ApiSettingsController::class, 'storeWebhook'])->name('webhooks.store');
        Route::patch('/webhooks/{webhookSubscription}/toggle', [\App\Http\Controllers\ApiSettingsController::class, 'toggleWebhook'])->name('webhooks.toggle');
        Route::delete('/webhooks/{webhookSubscription}', [\App\Http\Controllers\ApiSettingsController::class, 'destroyWebhook'])->name('webhooks.destroy');
        Route::post('/webhooks/{webhookSubscription}/test', [\App\Http\Controllers\ApiSettingsController::class, 'testWebhook'])->name('webhooks.test');
        Route::get('/webhooks/{webhookSubscription}/deliveries', [\App\Http\Controllers\ApiSettingsController::class, 'webhookDeliveries'])->name('webhooks.deliveries');
        Route::post('/webhooks/deliveries/{webhookDelivery}/retry', [\App\Http\Controllers\ApiSettingsController::class, 'retryDelivery'])->name('webhooks.deliveries.retry');
        Route::get('/webhooks/log', [\App\Http\Controllers\ApiSettingsController::class, 'deliveryLog'])->name('webhooks.log');
    });

    // POS Kasir
    Route::prefix('pos')->name('pos.')->middleware('permission:pos,view')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout')->middleware(['permission:pos,create', 'throttle:pos-checkout']);
        Route::post('/initiate-payment', [PosController::class, 'initiatePayment'])->name('initiate-payment');
        Route::post('/complete-payment/{order}', [PosController::class, 'completePayment'])->name('complete-payment');
        Route::get('/barcode', [PosController::class, 'findByBarcode'])->name('barcode');

        // Payment UI Routes
        Route::get('/payment/qris/{transactionNumber}', [PaymentUIController::class, 'showQrisPayment'])->name('payment.qris');
        Route::get('/payment/history', [PaymentUIController::class, 'paymentHistory'])->name('payment.history');
    });

    // Payment Gateway Settings (Tenant self-configuration)
    Route::get('/settings/payment-gateways', [PaymentUIController::class, 'gatewaySettings'])->name('settings.payment-gateways');

    // Approval Workflow
    Route::prefix('approvals')->name('approvals.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/', [ApprovalController::class, 'store'])->name('store');
        Route::post('/{approval}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approval}/reject', [ApprovalController::class, 'reject'])->name('reject');
        // Workflow builder
        Route::get('/workflows', [ApprovalController::class, 'workflowIndex'])->name('workflows');
        Route::post('/workflows', [ApprovalController::class, 'workflowStore'])->name('workflows.store');
        Route::put('/workflows/{workflow}', [ApprovalController::class, 'workflowUpdate'])->name('workflows.update');
        Route::delete('/workflows/{workflow}', [ApprovalController::class, 'workflowDestroy'])->name('workflows.destroy');
    });

    // Audit Trail
    Route::middleware('role:admin')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/{activityLog}', [AuditController::class, 'show'])->name('audit.show');
        Route::post('/audit/{activityLog}/rollback', [AuditController::class, 'rollback'])->name('audit.rollback');
        Route::get('/audit-export', [AuditController::class, 'export'])->name('audit.export');
        Route::get('/audit-compliance-report', [AuditController::class, 'complianceReport'])->name('audit.compliance-report');
    });

    // Bank Reconciliation (admin + manager only)
    Route::prefix('bank')->name('bank.')->middleware('role:admin,manager')->group(function () {
        Route::get('/reconciliation', [BankReconciliationController::class, 'index'])->name('reconciliation');
        Route::post('/import', [BankReconciliationController::class, 'import'])->name('import');
        Route::post('/statements/{statement}/match', [BankReconciliationController::class, 'match'])->name('match');
        // AI
        Route::get('/ai/match-all', [BankReconciliationController::class, 'aiMatchAll'])->name('ai.match-all')->middleware('ai.quota');
        Route::get('/ai/match/{statement}', [BankReconciliationController::class, 'aiMatchOne'])->name('ai.match-one')->middleware('ai.quota');
        Route::post('/ai/apply-match/{statement}', [BankReconciliationController::class, 'aiApplyMatch'])->name('ai.apply-match')->middleware('ai.quota');
    });

    // Bank Accounts (master data)
    Route::prefix('bank-accounts')->name('bank-accounts.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [\App\Http\Controllers\BankAccountController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\BankAccountController::class, 'store'])->name('store');
        Route::put('/{bankAccount}', [\App\Http\Controllers\BankAccountController::class, 'update'])->name('update');
        Route::patch('/{bankAccount}/toggle', [\App\Http\Controllers\BankAccountController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{bankAccount}', [\App\Http\Controllers\BankAccountController::class, 'destroy'])->name('destroy');
    });

    // Digital Signature
    Route::prefix('sign')->name('sign.')->group(function () {
        Route::get('/{modelType}/{modelId}', [SignatureController::class, 'pad'])->name('pad');
        Route::post('/{modelType}/{modelId}', [SignatureController::class, 'sign'])->name('sign');
    });

    // Shipping (admin + manager only)
    Route::prefix('shipping')->name('shipping.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::post('/', [ShippingController::class, 'store'])->name('store');
        Route::post('/rate', [ShippingController::class, 'checkRate'])->name('rate');
        Route::post('/track', [ShippingController::class, 'track'])->name('track');
    });

    // Bot Settings (admin only)
    Route::prefix('settings/bot')->name('bot.')->middleware('role:admin')->group(function () {
        Route::get('/', [BotController::class, 'settings'])->name('settings');
        Route::post('/', [BotController::class, 'saveSettings'])->name('save');
    });

    // Tax Management (admin only)
    Route::prefix('settings/taxes')->name('taxes.')->middleware('role:admin')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->name('index');
        Route::post('/', [TaxController::class, 'store'])->name('store');
        Route::put('/{tax}', [TaxController::class, 'update'])->name('update');
        Route::delete('/{tax}', [TaxController::class, 'destroy'])->name('destroy');
        Route::get('/export/efaktur', [TaxController::class, 'exportEfaktur'])->name('efaktur');
    });

    // Accounting Settings (COA + Bank + Tax + Currency in one page)
    Route::prefix('settings/accounting')->name('settings.accounting')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\AccountingSettingsController::class, 'index'])->name('');
        Route::post('/currencies', [\App\Http\Controllers\AccountingSettingsController::class, 'storeCurrency'])->name('.currencies.store');
        Route::put('/currencies/{currency}', [\App\Http\Controllers\AccountingSettingsController::class, 'updateCurrency'])->name('.currencies.update');
        Route::delete('/currencies/{currency}', [\App\Http\Controllers\AccountingSettingsController::class, 'destroyCurrency'])->name('.currencies.destroy');
    });

    // E-commerce (admin + manager only)
    Route::prefix('ecommerce')->name('ecommerce.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [EcommerceController::class, 'index'])->name('index');
        Route::get('/dashboard', [EcommerceController::class, 'dashboard'])->name('dashboard');
        Route::post('/channels', [EcommerceController::class, 'storeChannel'])->name('channels.store');
        Route::put('/channels/{channel}', [EcommerceController::class, 'updateChannel'])->name('channels.update');
        Route::delete('/channels/{channel}', [EcommerceController::class, 'destroyChannel'])->name('channels.destroy');
        Route::post('/channels/{channel}/sync', [EcommerceController::class, 'sync'])->name('channels.sync');
        Route::post('/channels/{channel}/sync-stock', [EcommerceController::class, 'syncStockManual'])->name('channels.sync-stock');
        Route::post('/channels/{channel}/sync-prices', [EcommerceController::class, 'syncPricesManual'])->name('channels.sync-prices');
        Route::get('/channels/{channel}/mappings', [EcommerceController::class, 'mappings'])->name('channels.mappings');
        Route::post('/channels/{channel}/mappings', [EcommerceController::class, 'storeMapping'])->name('channels.mappings.store');
        Route::delete('/mappings/{mapping}', [EcommerceController::class, 'destroyMapping'])->name('mappings.destroy');
    });

    // Sales AI — contextual suggestions (AJAX)
    Route::prefix('sales/ai')->name('sales.ai.')->middleware(['role:admin,manager', 'ai.quota'])->group(function () {
        Route::get('/price-suggest', [\App\Http\Controllers\SalesAiController::class, 'priceSuggest'])->name('price-suggest');
        Route::get('/late-payment-risk', [\App\Http\Controllers\SalesAiController::class, 'latePaymentRisk'])->name('late-payment-risk');
        Route::get('/item-description', [\App\Http\Controllers\SalesAiController::class, 'itemDescription'])->name('item-description');
    });

    // Accounting AI — contextual suggestions (AJAX)
    Route::prefix('accounting/ai')->name('accounting.ai.')->middleware(['role:admin,manager', 'ai.quota'])->group(function () {
        Route::get('/suggest-accounts', [\App\Http\Controllers\AccountingAiController::class, 'suggestAccounts'])->name('suggest-accounts');
        Route::post('/check-journal', [\App\Http\Controllers\AccountingAiController::class, 'checkJournal'])->name('check-journal');
        Route::get('/categorize-statement', [\App\Http\Controllers\AccountingAiController::class, 'categorizeStatement'])->name('categorize-statement');
    });

    // Sales Orders
    Route::prefix('sales')->name('sales.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [SalesOrderController::class, 'index'])->name('index');
        Route::get('/create', [SalesOrderController::class, 'create'])->name('create');
        Route::post('/', [SalesOrderController::class, 'store'])->name('store');
        Route::get('/{salesOrder}', [SalesOrderController::class, 'show'])->name('show');
        Route::patch('/{salesOrder}/status', [SalesOrderController::class, 'updateStatus'])->name('status');
        Route::post('/{salesOrder}/invoice', [SalesOrderController::class, 'createInvoice'])->name('invoice');
        Route::delete('/{salesOrder}', [SalesOrderController::class, 'destroy'])->name('destroy');
    });

    // Expense Management
    Route::prefix('expenses')->name('expenses.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::get('/categories', [ExpenseController::class, 'categories'])->name('categories');
        Route::post('/categories', [ExpenseController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [ExpenseController::class, 'updateCategory'])->name('categories.update');
    });

    // Reimbursement
    Route::prefix('reimbursement')->name('reimbursement.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReimbursementController::class, 'index'])->name('index')->middleware('permission:reimbursement,view');
        Route::post('/', [\App\Http\Controllers\ReimbursementController::class, 'store'])->name('store')->middleware('permission:reimbursement,create');
        Route::patch('/{reimbursement}/approve', [\App\Http\Controllers\ReimbursementController::class, 'approve'])->name('approve')->middleware('permission:reimbursement,edit');
        Route::patch('/{reimbursement}/reject', [\App\Http\Controllers\ReimbursementController::class, 'reject'])->name('reject')->middleware('permission:reimbursement,edit');
        Route::post('/{reimbursement}/pay', [\App\Http\Controllers\ReimbursementController::class, 'pay'])->name('pay')->middleware('permission:reimbursement,edit');
        Route::delete('/{reimbursement}', [\App\Http\Controllers\ReimbursementController::class, 'destroy'])->name('destroy')->middleware('permission:reimbursement,delete');
    });
    // Self-service reimbursement (all roles)
    Route::get('/my-reimbursement', [\App\Http\Controllers\ReimbursementController::class, 'myReimbursements'])->name('reimbursement.my');
    Route::post('/my-reimbursement', [\App\Http\Controllers\ReimbursementController::class, 'submitMy'])->name('reimbursement.my.store');

    // Warehouse Transfers
    Route::prefix('inventory/transfers')->name('inventory.transfers.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [WarehouseTransferController::class, 'index'])->name('index');
        Route::post('/', [WarehouseTransferController::class, 'store'])->name('store');
    });

    // WMS (Advanced Warehouse Management)
    Route::prefix('wms')->name('wms.')->middleware('role:admin,manager,gudang')->group(function () {
        Route::get('/', [\App\Http\Controllers\WmsController::class, 'index'])->name('index')->middleware('permission:wms,view');
        Route::post('/zones', [\App\Http\Controllers\WmsController::class, 'storeZone'])->name('zones.store')->middleware('permission:wms,create');
        Route::post('/bins', [\App\Http\Controllers\WmsController::class, 'storeBin'])->name('bins.store')->middleware('permission:wms,create');
        Route::post('/bins/bulk', [\App\Http\Controllers\WmsController::class, 'bulkCreateBins'])->name('bins.bulk')->middleware('permission:wms,create');
        Route::post('/putaway', [\App\Http\Controllers\WmsController::class, 'putaway'])->name('putaway')->middleware('permission:wms,create');
        Route::get('/suggest-bin', [\App\Http\Controllers\WmsController::class, 'suggestBin'])->name('suggest-bin')->middleware('permission:wms,view');
        Route::get('/picking', [\App\Http\Controllers\WmsController::class, 'pickingLists'])->name('picking')->middleware('permission:wms,view');
        Route::post('/picking', [\App\Http\Controllers\WmsController::class, 'createPickingList'])->name('picking.store')->middleware('permission:wms,create');
        Route::patch('/picking/items/{pickingListItem}', [\App\Http\Controllers\WmsController::class, 'confirmPick'])->name('picking.confirm')->middleware('permission:wms,edit');
        Route::get('/picking/{pickingList}/scan', [\App\Http\Controllers\WmsController::class, 'scanPicking'])->name('picking.scan')->middleware('permission:wms,view');
        Route::get('/bins/{bin}/label', [\App\Http\Controllers\WmsController::class, 'printBinLabel'])->name('bins.label')->middleware('permission:wms,view');
        Route::post('/bins/labels/batch', [\App\Http\Controllers\WmsController::class, 'printBinLabelsBatch'])->name('bins.labels.batch')->middleware('permission:wms,view');
        Route::get('/opname', [\App\Http\Controllers\WmsController::class, 'opnameSessions'])->name('opname')->middleware('permission:wms,view');
        Route::post('/opname', [\App\Http\Controllers\WmsController::class, 'createOpname'])->name('opname.store')->middleware('permission:wms,create');
        Route::get('/opname/{stockOpnameSession}', [\App\Http\Controllers\WmsController::class, 'showOpname'])->name('opname.show')->middleware('permission:wms,view');
        Route::patch('/opname/items/{stockOpnameItem}', [\App\Http\Controllers\WmsController::class, 'updateOpnameItem'])->name('opname.item.update')->middleware('permission:wms,edit');
        Route::patch('/opname/{stockOpnameSession}/complete', [\App\Http\Controllers\WmsController::class, 'completeOpname'])->name('opname.complete')->middleware('permission:wms,edit');
        Route::get('/putaway-rules', [\App\Http\Controllers\WmsController::class, 'putawayRules'])->name('putaway-rules')->middleware('permission:wms,view');
        Route::post('/putaway-rules', [\App\Http\Controllers\WmsController::class, 'storePutawayRule'])->name('putaway-rules.store')->middleware('permission:wms,create');
        Route::delete('/putaway-rules/{putawayRule}', [\App\Http\Controllers\WmsController::class, 'destroyPutawayRule'])->name('putaway-rules.destroy')->middleware('permission:wms,delete');
    });
    Route::prefix('inventory')->name('inventory.')->middleware('tenant.isolation')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/warehouses', fn() => redirect()->route('warehouses.index'))->name('warehouses');
        Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');

        // Write operations: admin + manager only
        Route::middleware('role:admin,manager')->group(function () {
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/stock', [InventoryController::class, 'addStock'])->name('add-stock');
            Route::get('/{product}/batches', [InventoryController::class, 'batches'])->name('batches');
            Route::patch('/batches/{batch}/status', [InventoryController::class, 'updateBatchStatus'])->name('batches.status');
            Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->name('warehouses.store');
            // Inventory AI — contextual (AJAX)
            Route::get('/ai/analyze-all', [\App\Http\Controllers\InventoryAiController::class, 'analyzeAll'])->name('ai.analyze-all')->middleware('ai.quota');
            Route::get('/ai/stockout/{product}', [\App\Http\Controllers\InventoryAiController::class, 'stockoutPrediction'])->name('ai.stockout')->middleware('ai.quota');
            Route::get('/ai/reorder/{product}', [\App\Http\Controllers\InventoryAiController::class, 'reorderSuggest'])->name('ai.reorder')->middleware('ai.quota');
            // Inventory Costing
            Route::get('/costing/valuation', [\App\Http\Controllers\InventoryCostingController::class, 'valuation'])->name('costing.valuation');
            Route::get('/costing/cogs', [\App\Http\Controllers\InventoryCostingController::class, 'cogs'])->name('costing.cogs');
            Route::post('/costing/method', [\App\Http\Controllers\InventoryCostingController::class, 'updateMethod'])->name('costing.method');
            Route::get('/costing/current-cost', [\App\Http\Controllers\InventoryCostingController::class, 'currentCost'])->name('costing.current-cost');
        });
    });

    // HRM
    Route::prefix('hrm')->name('hrm.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [HrmController::class, 'index'])->name('index');
        Route::post('/', [HrmController::class, 'store'])->name('store');
        Route::put('/{employee}', [HrmController::class, 'update'])->name('update');
        Route::delete('/{employee}', [HrmController::class, 'destroy'])->name('destroy');
        Route::get('/attendance', [HrmController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [HrmController::class, 'storeAttendance'])->name('attendance.store');
        // Leave management
        Route::get('/leave', [HrmController::class, 'leave'])->name('leave');
        Route::post('/leave', [HrmController::class, 'storeLeave'])->name('leave.store');
        Route::patch('/leave/{leave}/approve', [HrmController::class, 'approveLeave'])->name('leave.approve');
        Route::delete('/leave/{leave}', [HrmController::class, 'destroyLeave'])->name('leave.destroy');
        // Performance review
        Route::get('/performance', [HrmController::class, 'performance'])->name('performance');
        Route::post('/performance', [HrmController::class, 'storePerformance'])->name('performance.store');
        Route::patch('/performance/{review}/acknowledge', [HrmController::class, 'acknowledgePerformance'])->name('performance.acknowledge');
        Route::delete('/performance/{review}', [HrmController::class, 'destroyPerformance'])->name('performance.destroy');
        // Org chart
        Route::get('/orgchart', [HrmController::class, 'orgChart'])->name('orgchart');
        Route::patch('/{employee}/manager', [HrmController::class, 'updateManager'])->name('manager.update');
        // HRM AI — contextual (AJAX)
        Route::get('/ai/attendance-anomalies', [\App\Http\Controllers\HrmAiController::class, 'attendanceAnomalies'])->name('ai.attendance-anomalies')->middleware('ai.quota');
        Route::get('/ai/salary-suggest/{employee}', [\App\Http\Controllers\HrmAiController::class, 'salarySuggest'])->name('ai.salary-suggest')->middleware('ai.quota');
        Route::get('/ai/career-path/{employee}', [\App\Http\Controllers\HrmAiController::class, 'careerPath'])->name('ai.career-path')->middleware('ai.quota');
        Route::get('/ai/turnover-risk', [\App\Http\Controllers\HrmAiController::class, 'turnoverRisk'])->name('ai.turnover-risk')->middleware('ai.quota');
        // Rekrutmen & Onboarding
        Route::prefix('recruitment')->name('recruitment.')->group(function () {
            Route::get('/', [\App\Http\Controllers\RecruitmentController::class, 'index'])->name('index');
            Route::post('/postings', [\App\Http\Controllers\RecruitmentController::class, 'storePosting'])->name('posting.store');
            Route::put('/postings/{posting}', [\App\Http\Controllers\RecruitmentController::class, 'updatePosting'])->name('posting.update');
            Route::delete('/postings/{posting}', [\App\Http\Controllers\RecruitmentController::class, 'destroyPosting'])->name('posting.destroy');
            Route::get('/postings/{posting}/applications', [\App\Http\Controllers\RecruitmentController::class, 'applications'])->name('applications');
            Route::post('/postings/{posting}/applications', [\App\Http\Controllers\RecruitmentController::class, 'storeApplication'])->name('application.store');
            Route::patch('/applications/{application}/stage', [\App\Http\Controllers\RecruitmentController::class, 'updateStage'])->name('application.stage');
        });
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', [\App\Http\Controllers\RecruitmentController::class, 'onboarding'])->name('index');
            Route::post('/start', [\App\Http\Controllers\RecruitmentController::class, 'startOnboarding'])->name('start');
            Route::get('/{onboarding}', [\App\Http\Controllers\RecruitmentController::class, 'onboardingDetail'])->name('detail');
            Route::patch('/tasks/{task}/toggle', [\App\Http\Controllers\RecruitmentController::class, 'toggleTask'])->name('task.toggle');
        });
        // Shift Management
        Route::prefix('shifts')->name('shifts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShiftController::class, 'index'])->name('index');
            Route::post('/shifts', [\App\Http\Controllers\ShiftController::class, 'storeShift'])->name('shift.store');
            Route::put('/shifts/{shift}', [\App\Http\Controllers\ShiftController::class, 'updateShift'])->name('shift.update');
            Route::delete('/shifts/{shift}', [\App\Http\Controllers\ShiftController::class, 'destroyShift'])->name('shift.destroy');
            Route::post('/assign', [\App\Http\Controllers\ShiftController::class, 'assignShift'])->name('assign');
            Route::post('/copy-week', [\App\Http\Controllers\ShiftController::class, 'copyWeek'])->name('copy-week');
            Route::get('/schedule-data', [\App\Http\Controllers\ShiftController::class, 'scheduleData'])->name('schedule-data');
            Route::get('/today', [\App\Http\Controllers\ShiftController::class, 'todaySchedule'])->name('today');
            Route::get('/conflicts', [\App\Http\Controllers\ShiftController::class, 'conflictDetect'])->name('conflicts');
        });
        // Fingerprint Device Management
        Route::prefix('fingerprint')->name('fingerprint.')->group(function () {
            // Device management
            Route::prefix('devices')->name('devices.')->group(function () {
                Route::get('/', [\App\Http\Controllers\FingerprintDeviceController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\FingerprintDeviceController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\FingerprintDeviceController::class, 'store'])->name('store');
                Route::get('/{device}', [\App\Http\Controllers\FingerprintDeviceController::class, 'show'])->name('show');
                Route::get('/{device}/edit', [\App\Http\Controllers\FingerprintDeviceController::class, 'edit'])->name('edit');
                Route::put('/{device}', [\App\Http\Controllers\FingerprintDeviceController::class, 'update'])->name('update');
                Route::delete('/{device}', [\App\Http\Controllers\FingerprintDeviceController::class, 'destroy'])->name('destroy');
                Route::post('/{device}/test-connection', [\App\Http\Controllers\FingerprintDeviceController::class, 'testConnection'])->name('test-connection');
                Route::post('/{device}/sync-attendance', [\App\Http\Controllers\FingerprintDeviceController::class, 'syncAttendance'])->name('sync-attendance');
            });
            // Employee fingerprint registration
            Route::prefix('employees')->name('employees.')->group(function () {
                Route::get('/', [\App\Http\Controllers\FingerprintDeviceController::class, 'employeeList'])->name('index');
                Route::get('/{employee}/register', [\App\Http\Controllers\FingerprintDeviceController::class, 'registerEmployee'])->name('register');
                Route::post('/{employee}/register', [\App\Http\Controllers\FingerprintDeviceController::class, 'storeEmployeeRegistration'])->name('register.store');
                Route::delete('/{employee}/remove-registration', [\App\Http\Controllers\FingerprintDeviceController::class, 'removeEmployeeRegistration'])->name('register.remove');
            });
        });
        // Overtime / Lembur
        Route::prefix('overtime')->name('overtime.')->group(function () {
            Route::get('/', [\App\Http\Controllers\OvertimeController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\OvertimeController::class, 'store'])->name('store');
            Route::patch('/{overtime}/approve', [\App\Http\Controllers\OvertimeController::class, 'approve'])->name('approve');
            Route::patch('/{overtime}/reject', [\App\Http\Controllers\OvertimeController::class, 'reject'])->name('reject');
            Route::delete('/{overtime}', [\App\Http\Controllers\OvertimeController::class, 'destroy'])->name('destroy');
        });
        // Pelatihan & Sertifikasi
        Route::prefix('training')->name('training.')->group(function () {
            Route::get('/', [\App\Http\Controllers\TrainingController::class, 'index'])->name('index');
            // Programs
            Route::post('/programs', [\App\Http\Controllers\TrainingController::class, 'storeProgram'])->name('programs.store');
            Route::put('/programs/{program}', [\App\Http\Controllers\TrainingController::class, 'updateProgram'])->name('programs.update');
            Route::delete('/programs/{program}', [\App\Http\Controllers\TrainingController::class, 'destroyProgram'])->name('programs.destroy');
            // Sessions
            Route::post('/sessions', [\App\Http\Controllers\TrainingController::class, 'storeSession'])->name('sessions.store');
            Route::get('/sessions/{session}', [\App\Http\Controllers\TrainingController::class, 'sessionDetail'])->name('sessions.detail');
            Route::patch('/sessions/{session}/status', [\App\Http\Controllers\TrainingController::class, 'updateSessionStatus'])->name('sessions.status');
            Route::delete('/sessions/{session}', [\App\Http\Controllers\TrainingController::class, 'destroySession'])->name('sessions.destroy');
            // Participants
            Route::post('/sessions/{session}/participants', [\App\Http\Controllers\TrainingController::class, 'addParticipant'])->name('sessions.participants.add');
            Route::patch('/participants/{participant}', [\App\Http\Controllers\TrainingController::class, 'updateParticipant'])->name('participants.update');
            Route::delete('/participants/{participant}', [\App\Http\Controllers\TrainingController::class, 'removeParticipant'])->name('participants.remove');
            // Certifications
            Route::post('/certifications', [\App\Http\Controllers\TrainingController::class, 'storeCertification'])->name('certifications.store');
            Route::delete('/certifications/{certification}', [\App\Http\Controllers\TrainingController::class, 'destroyCertification'])->name('certifications.destroy');
        });
        // Surat Peringatan & Disiplin
        Route::prefix('disciplinary')->name('disciplinary.')->group(function () {
            Route::get('/', [\App\Http\Controllers\DisciplinaryController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\DisciplinaryController::class, 'store'])->name('store');
            Route::get('/{letter}', [\App\Http\Controllers\DisciplinaryController::class, 'show'])->name('show');
            Route::patch('/{letter}/acknowledge', [\App\Http\Controllers\DisciplinaryController::class, 'acknowledge'])->name('acknowledge');
            Route::patch('/{letter}/expire', [\App\Http\Controllers\DisciplinaryController::class, 'expire'])->name('expire');
            Route::delete('/{letter}', [\App\Http\Controllers\DisciplinaryController::class, 'destroy'])->name('destroy');
            Route::post('/ai-draft', [\App\Http\Controllers\DisciplinaryController::class, 'aiDraft'])->name('ai-draft')->middleware('ai.quota');
        });
    });

    // Purchasing (admin + manager only)
    Route::prefix('purchasing')->name('purchasing.')->middleware('role:admin,manager')->group(function () {
        // Redirect lama ke /suppliers baru
        Route::get('/suppliers', fn() => redirect()->route('suppliers.index'))->name('suppliers');
        Route::post('/suppliers', fn() => redirect()->route('suppliers.index'))->name('suppliers.store');
        Route::put('/suppliers/{supplier}', fn() => redirect()->route('suppliers.index'))->name('suppliers.update');
        Route::get('/orders', [PurchasingController::class, 'orders'])->name('orders');
        Route::post('/orders', [PurchasingController::class, 'storeOrder'])->name('orders.store');
        Route::patch('/orders/{order}/status', [PurchasingController::class, 'updateOrderStatus'])->name('orders.status');
        Route::delete('/orders/{order}', [PurchasingController::class, 'destroyOrder'])->name('orders.destroy');
        // Task 35: State machine actions
        Route::post('/orders/{order}/post', [PurchasingController::class, 'postOrder'])->name('orders.post');
        Route::post('/orders/{order}/cancel', [PurchasingController::class, 'cancelOrder'])->name('orders.cancel');
        // Purchase Requisition
        Route::get('/requisitions', [PurchasingController::class, 'requisitions'])->name('requisitions');
        Route::post('/requisitions', [PurchasingController::class, 'storeRequisition'])->name('requisitions.store');
        Route::patch('/requisitions/{requisition}/approve', [PurchasingController::class, 'approveRequisition'])->name('requisitions.approve');
        Route::post('/requisitions/{requisition}/convert', [PurchasingController::class, 'convertRequisitionToPo'])->name('requisitions.convert');
        // RFQ
        Route::get('/rfq', [PurchasingController::class, 'rfqs'])->name('rfq');
        Route::post('/rfq', [PurchasingController::class, 'storeRfq'])->name('rfq.store');
        Route::post('/rfq/{rfq}/response', [PurchasingController::class, 'storeRfqResponse'])->name('rfq.response');
        Route::patch('/rfq/response/{response}/select', [PurchasingController::class, 'selectRfqResponse'])->name('rfq.response.select');
        Route::post('/rfq/{rfq}/convert', [PurchasingController::class, 'convertRfqToPo'])->name('rfq.convert');
        // Goods Receipt
        Route::get('/goods-receipts', [PurchasingController::class, 'goodsReceipts'])->name('goods-receipts');
        Route::post('/goods-receipts', [PurchasingController::class, 'storeGoodsReceipt'])->name('goods-receipts.store');
        // 3-Way Matching
        Route::get('/matching', [PurchasingController::class, 'matching'])->name('matching');
    });

    // Suppliers (master data)
    Route::prefix('suppliers')->name('suppliers.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [\App\Http\Controllers\SupplierController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('update');
        Route::patch('/{supplier}/toggle', [\App\Http\Controllers\SupplierController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('destroy');
    });

    // Customers (master data)
    Route::prefix('customers')->name('customers.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [\App\Http\Controllers\CustomerController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\CustomerController::class, 'store'])->name('store');
        Route::put('/{customer}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('update');
        Route::patch('/{customer}/toggle', [\App\Http\Controllers\CustomerController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{customer}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->name('destroy');
    });

    // Products (master data)
    Route::prefix('products')->name('products.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])->name('store');
        Route::put('/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('update');
        Route::patch('/{product}/toggle', [\App\Http\Controllers\ProductController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('destroy');
    });

    // Warehouses (master data)
    Route::prefix('warehouses')->name('warehouses.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [\App\Http\Controllers\WarehouseController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\WarehouseController::class, 'store'])->name('store');
        Route::put('/{warehouse}', [\App\Http\Controllers\WarehouseController::class, 'update'])->name('update');
        Route::patch('/{warehouse}/toggle', [\App\Http\Controllers\WarehouseController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{warehouse}', [\App\Http\Controllers\WarehouseController::class, 'destroy'])->name('destroy');
    });

    // CRM (admin + manager only)
    Route::prefix('crm')->name('crm.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [CrmController::class, 'index'])->name('index');
        Route::get('/kanban', [CrmController::class, 'kanban'])->name('kanban');
        Route::post('/', [CrmController::class, 'store'])->name('store');
        Route::patch('/{lead}/stage', [CrmController::class, 'updateStage'])->name('stage');
        Route::patch('/{lead}/stage-drag', [CrmController::class, 'updateStageDrag'])->name('stage-drag');
        Route::post('/{lead}/activity', [CrmController::class, 'logActivity'])->name('activity');
        Route::post('/{lead}/convert-customer', [CrmController::class, 'convertToCustomer'])->name('convert-customer');
        Route::delete('/{lead}', [CrmController::class, 'destroy'])->name('destroy');
        // AI
        Route::get('/ai/score-all', [CrmAiController::class, 'scoreAll'])->name('ai.score-all')->middleware('ai.quota');
        Route::get('/ai/score/{lead}', [CrmAiController::class, 'scoreLead'])->name('ai.score')->middleware('ai.quota');
        Route::get('/ai/follow-up/{lead}', [CrmAiController::class, 'followUp'])->name('ai.follow-up')->middleware('ai.quota');
    });

    // Project Management (admin + manager only)
    Route::prefix('projects')->name('projects.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{project}/tasks', [ProjectController::class, 'storeTask'])->name('tasks.store');
        Route::patch('/tasks/{task}/status', [ProjectController::class, 'updateTaskStatus'])->name('tasks.status');
        Route::post('/tasks/{task}/volume', [ProjectController::class, 'recordVolume'])->name('tasks.volume');
        Route::delete('/tasks/{task}', [ProjectController::class, 'destroyTask'])->name('tasks.destroy');
        Route::post('/{project}/expenses', [ProjectController::class, 'storeExpense'])->name('expenses.store');

        // RAB (Rencana Anggaran Biaya)
        Route::get('/{project}/rab', [\App\Http\Controllers\RabController::class, 'index'])->name('rab')->middleware('permission:rab,view');
        Route::post('/{project}/rab', [\App\Http\Controllers\RabController::class, 'store'])->name('rab.store')->middleware('permission:rab,create');
        Route::put('/rab/{rabItem}', [\App\Http\Controllers\RabController::class, 'update'])->name('rab.update')->middleware('permission:rab,edit');
        Route::post('/rab/{rabItem}/actual', [\App\Http\Controllers\RabController::class, 'recordActual'])->name('rab.actual')->middleware('permission:rab,edit');
        Route::delete('/rab/{rabItem}', [\App\Http\Controllers\RabController::class, 'destroy'])->name('rab.destroy')->middleware('permission:rab,delete');
        Route::get('/{project}/rab/export', [\App\Http\Controllers\RabController::class, 'export'])->name('rab.export')->middleware('permission:rab,view');
        Route::post('/{project}/rab/import', [\App\Http\Controllers\RabController::class, 'import'])->name('rab.import')->middleware('permission:rab,create');
    });

    // Budget vs Actual (admin + manager only)
    Route::prefix('budget')->name('budget.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::post('/', [BudgetController::class, 'store'])->name('store');
        Route::put('/{budget}', [BudgetController::class, 'update'])->name('update');
        Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
        // Budget AI — contextual (AJAX)
        Route::get('/ai/overrun-prediction', [\App\Http\Controllers\BudgetAiController::class, 'overrunPrediction'])->name('ai.overrun')->middleware('ai.quota');
        Route::get('/ai/suggest-allocation', [\App\Http\Controllers\BudgetAiController::class, 'suggestAllocation'])->name('ai.suggest')->middleware('ai.quota');
    });

    // Loyalty Program (admin + manager only)
    Route::prefix('loyalty')->name('loyalty.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::post('/program', [LoyaltyController::class, 'saveProgram'])->name('program.save');
        Route::post('/add-points', [LoyaltyController::class, 'addPoints'])->name('add-points');
        Route::post('/redeem', [LoyaltyController::class, 'redeemPoints'])->name('redeem');
        Route::get('/customer/{customer}/transactions', [LoyaltyController::class, 'transactions'])->name('transactions');
    });

    // Payroll
    Route::prefix('payroll')->name('payroll.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/process', [PayrollController::class, 'process'])->name('process');
        Route::patch('/{run}/paid', [PayrollController::class, 'markPaid'])->name('paid');
        Route::post('/{run}/gl-journal', [PayrollController::class, 'createGlJournal'])->name('gl-journal');
        Route::post('/{run}/gl-payment-journal', [PayrollController::class, 'createPaymentGlJournal'])->name('gl-payment-journal');

        // Komponen Gaji
        Route::prefix('components')->name('components.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SalaryComponentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\SalaryComponentController::class, 'store'])->name('store');
            Route::put('/{component}', [\App\Http\Controllers\SalaryComponentController::class, 'update'])->name('update');
            Route::delete('/{component}', [\App\Http\Controllers\SalaryComponentController::class, 'destroy'])->name('destroy');
            // Per karyawan
            Route::get('/employee/{employee}/json', [\App\Http\Controllers\SalaryComponentController::class, 'employeeComponentsJson'])->name('employee.json');
            Route::post('/employee/{employee}/save', [\App\Http\Controllers\SalaryComponentController::class, 'saveEmployeeComponents'])->name('employee.save');
        });
    });

    // Slip Gaji Self-Service (semua role tenant)
    Route::prefix('payroll/slip')->name('payroll.slip.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PayslipController::class, 'index'])->name('index');
        Route::get('/{item}', [\App\Http\Controllers\PayslipController::class, 'show'])->name('show');
    });

    // Self-Service Karyawan: Cuti & Absensi (semua role tenant)
    Route::prefix('self-service')->name('self-service.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'profile'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'updateProfile'])->name('profile.update');
        Route::get('/leave', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'leaveIndex'])->name('leave.index');
        Route::post('/leave', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'leaveStore'])->name('leave.store');
        Route::delete('/leave/{leave}', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'leaveCancel'])->name('leave.cancel');
        Route::get('/attendance', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'attendanceIndex'])->name('attendance.index');
        Route::post('/attendance/clock-in', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [\App\Http\Controllers\EmployeeSelfServiceController::class, 'clockOut'])->name('attendance.clock-out');
    });

    // Assets
    Route::prefix('assets')->name('assets.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
        Route::get('/{asset}/schedule', [AssetController::class, 'schedule'])->name('schedule');
        Route::post('/depreciate', [AssetController::class, 'depreciate'])->name('depreciate');
        Route::get('/maintenance', [AssetController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance', [AssetController::class, 'storeMaintenance'])->name('maintenance.store');
        Route::patch('/maintenance/{maintenance}/status', [AssetController::class, 'updateMaintenanceStatus'])->name('maintenance.status');

        // Asset Barcode & Scanning
        Route::get('/scan-maintenance', [AssetController::class, 'scanForMaintenance'])->name('scan-maintenance')->middleware('permission:assets,create');
        Route::post('/lookup-by-barcode', [AssetController::class, 'lookupByBarcode'])->name('lookup-barcode')->middleware('permission:assets,view');
        Route::get('/{asset}/barcode', [AssetController::class, 'showBarcode'])->name('barcode.show')->middleware('permission:assets,view');
        Route::post('/barcode/print', [AssetController::class, 'printBarcodes'])->name('barcode.print')->middleware('permission:assets,view');
    });

    // Import CSV (admin + manager only)
    Route::prefix('import')->name('import.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/products', [ImportController::class, 'importProducts'])->name('products')->middleware('throttle:import');
        Route::post('/employees', [ImportController::class, 'importEmployees'])->name('employees')->middleware('throttle:import');
        Route::post('/customers', [ImportController::class, 'importCustomers'])->name('customers')->middleware('throttle:import');
        Route::post('/suppliers', [ImportController::class, 'importSuppliers'])->name('suppliers')->middleware('throttle:import');
        Route::post('/warehouses', [ImportController::class, 'importWarehouses'])->name('warehouses')->middleware('throttle:import');
        Route::post('/coa', [ImportController::class, 'importChartOfAccounts'])->name('coa')->middleware('throttle:import');
        Route::get('/template/{type}', [ImportController::class, 'downloadTemplate'])->name('template');
        // Bulk export
        Route::get('/export/products', [ImportController::class, 'exportProducts'])->name('export.products')->middleware('throttle:export');
        Route::get('/export/customers', [ImportController::class, 'exportCustomers'])->name('export.customers')->middleware('throttle:export');
        Route::get('/export/suppliers', [ImportController::class, 'exportSuppliers'])->name('export.suppliers')->middleware('throttle:export');
        Route::get('/export/employees', [ImportController::class, 'exportEmployees'])->name('export.employees')->middleware('throttle:export');
        Route::get('/export/warehouses', [ImportController::class, 'exportWarehouses'])->name('export.warehouses')->middleware('throttle:export');
        Route::get('/export/coa', [ImportController::class, 'exportChartOfAccounts'])->name('export.coa')->middleware('throttle:export');
    });

    // Payment Gateway
    Route::prefix('payment')->name('payment.')->middleware('role:admin')->group(function () {
        Route::post('/midtrans/checkout', [\App\Http\Controllers\PaymentGatewayController::class, 'midtransCheckout'])->name('midtrans.checkout');
        Route::get('/midtrans/finish', [\App\Http\Controllers\PaymentGatewayController::class, 'midtransFinish'])->name('midtrans.finish');
        Route::post('/xendit/checkout', [\App\Http\Controllers\PaymentGatewayController::class, 'xenditCheckout'])->name('xendit.checkout');
        Route::get('/xendit/finish', [\App\Http\Controllers\PaymentGatewayController::class, 'xenditFinish'])->name('xendit.finish');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('payment');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
        Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
        // Task 35: State machine actions
        Route::post('/{invoice}/post', [InvoiceController::class, 'post'])->name('post');
        Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
        Route::post('/{invoice}/void', [InvoiceController::class, 'void'])->name('void');
    });

    // Receivables & Payables (Piutang & Hutang)
    Route::prefix('receivables')->name('receivables.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ReceivablesController::class, 'receivables'])->name('index');
        Route::post('/{invoice}/payment', [ReceivablesController::class, 'recordReceivablePayment'])->name('payment');
        Route::get('/aging', [ReceivablesController::class, 'aging'])->name('aging');
        Route::get('/{invoice}/installments', [ReceivablesController::class, 'installments'])->name('installments');
        Route::post('/{invoice}/installments', [ReceivablesController::class, 'storeInstallments'])->name('installments.store');
        Route::post('/installment/{installment}/pay', [ReceivablesController::class, 'payInstallment'])->name('installment.pay');
    });
    Route::prefix('payables')->name('payables.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ReceivablesController::class, 'payables'])->name('index');
        Route::post('/{payable}/payment', [ReceivablesController::class, 'recordPayablePayment'])->name('payment');
    });

    // Production / Work Orders
    Route::prefix('production')->name('production.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ProductionController::class, 'index'])->name('index');
        Route::post('/', [ProductionController::class, 'store'])->name('store');
        // Static routes HARUS sebelum {workOrder} wildcard
        Route::get('/recipes', [ProductionController::class, 'recipes'])->name('recipes');
        Route::post('/recipes', [ProductionController::class, 'storeRecipe'])->name('recipes.store');
        // Dynamic routes
        Route::get('/{workOrder}', [ProductionController::class, 'show'])->name('show');
        Route::patch('/{workOrder}/status', [ProductionController::class, 'updateStatus'])->name('status');
        Route::post('/{workOrder}/output', [ProductionController::class, 'recordOutput'])->name('output');
    });

    // Manufacturing (BOM, Work Centers, MRP)
    Route::prefix('manufacturing')->name('manufacturing.')->middleware('role:admin,manager,gudang')->group(function () {
        Route::get('/bom', [ManufacturingController::class, 'bom'])->name('bom')->middleware('permission:manufacturing,view');
        Route::post('/bom', [ManufacturingController::class, 'storeBom'])->name('bom.store')->middleware('permission:manufacturing,create');
        Route::put('/bom/{bom}', [ManufacturingController::class, 'updateBom'])->name('bom.update')->middleware('permission:manufacturing,edit');
        Route::delete('/bom/{bom}', [ManufacturingController::class, 'destroyBom'])->name('bom.destroy')->middleware('permission:manufacturing,delete');
        Route::get('/work-centers', [ManufacturingController::class, 'workCenters'])->name('work-centers')->middleware('permission:manufacturing,view');
        Route::post('/work-centers', [ManufacturingController::class, 'storeWorkCenter'])->name('work-centers.store')->middleware('permission:manufacturing,create');
        Route::put('/work-centers/{workCenter}', [ManufacturingController::class, 'updateWorkCenter'])->name('work-centers.update')->middleware('permission:manufacturing,edit');
        Route::delete('/work-centers/{workCenter}', [ManufacturingController::class, 'destroyWorkCenter'])->name('work-centers.destroy')->middleware('permission:manufacturing,delete');
        Route::get('/mrp', [ManufacturingController::class, 'mrp'])->name('mrp')->middleware('permission:manufacturing,view');
        Route::post('/{workOrder}/consume', [ManufacturingController::class, 'consumeMaterials'])->name('consume')->middleware('permission:manufacturing,create');

        // Work Order Material Scanning
        Route::get('/work-orders/{workOrder}/scan-materials', [ManufacturingController::class, 'scanMaterials'])->name('work-orders.scan-materials')->middleware('permission:manufacturing,create');
        Route::post('/work-orders/{workOrder}/consume-scanned', [ManufacturingController::class, 'consumeScannedMaterials'])->name('work-orders.consume-scanned')->middleware('permission:manufacturing,create');

        // Mix Design (Mutu Beton)
        Route::get('/mix-design', [\App\Http\Controllers\ConcreteMixDesignController::class, 'index'])->name('mix-design')->middleware('permission:manufacturing,view');
        Route::post('/mix-design', [\App\Http\Controllers\ConcreteMixDesignController::class, 'store'])->name('mix-design.store')->middleware('permission:manufacturing,create');
        Route::post('/mix-design/seed-standards', [\App\Http\Controllers\ConcreteMixDesignController::class, 'seedStandards'])->name('mix-design.seed')->middleware('permission:manufacturing,create');
        Route::put('/mix-design/{mixDesign}', [\App\Http\Controllers\ConcreteMixDesignController::class, 'update'])->name('mix-design.update')->middleware('permission:manufacturing,edit');
        Route::delete('/mix-design/{mixDesign}', [\App\Http\Controllers\ConcreteMixDesignController::class, 'destroy'])->name('mix-design.destroy')->middleware('permission:manufacturing,delete');
        Route::get('/mix-design/{mixDesign}/calculate', [\App\Http\Controllers\ConcreteMixDesignController::class, 'calculate'])->name('mix-design.calculate');
        Route::post('/mix-design/{mixDesign}/generate-bom', [\App\Http\Controllers\ConcreteMixDesignController::class, 'generateBom'])->name('mix-design.generate-bom')->middleware('permission:manufacturing,create');
    });

    // Farm / Agriculture — Manajemen Lahan
    Route::prefix('farm')->name('farm.')->middleware('role:admin,manager,gudang')->group(function () {
        Route::get('/plots', [\App\Http\Controllers\FarmPlotController::class, 'index'])->name('plots')->middleware('permission:agriculture,view');
        Route::post('/plots', [\App\Http\Controllers\FarmPlotController::class, 'store'])->name('plots.store')->middleware('permission:agriculture,create');
        Route::get('/plots/{farmPlot}', [\App\Http\Controllers\FarmPlotController::class, 'show'])->name('plots.show')->middleware('permission:agriculture,view');
        Route::put('/plots/{farmPlot}', [\App\Http\Controllers\FarmPlotController::class, 'update'])->name('plots.update')->middleware('permission:agriculture,edit');
        Route::patch('/plots/{farmPlot}/status', [\App\Http\Controllers\FarmPlotController::class, 'updateStatus'])->name('plots.status')->middleware('permission:agriculture,edit');
        Route::delete('/plots/{farmPlot}', [\App\Http\Controllers\FarmPlotController::class, 'destroy'])->name('plots.destroy')->middleware('permission:agriculture,delete');
        Route::post('/plots/{farmPlot}/activities', [\App\Http\Controllers\FarmPlotController::class, 'storeActivity'])->name('plots.activities.store')->middleware('permission:agriculture,create');
        // Crop Cycles
        Route::get('/cycles', [\App\Http\Controllers\CropCycleController::class, 'index'])->name('cycles')->middleware('permission:agriculture,view');
        Route::post('/cycles', [\App\Http\Controllers\CropCycleController::class, 'store'])->name('cycles.store')->middleware('permission:agriculture,create');
        Route::get('/cycles/{cropCycle}', [\App\Http\Controllers\CropCycleController::class, 'show'])->name('cycles.show')->middleware('permission:agriculture,view');
        Route::patch('/cycles/{cropCycle}/phase', [\App\Http\Controllers\CropCycleController::class, 'advancePhase'])->name('cycles.phase')->middleware('permission:agriculture,edit');
        Route::post('/cycles/{cropCycle}/activities', [\App\Http\Controllers\CropCycleController::class, 'storeActivity'])->name('cycles.activities.store')->middleware('permission:agriculture,create');
        // Harvest Logs
        Route::get('/harvests', [\App\Http\Controllers\HarvestLogController::class, 'index'])->name('harvests')->middleware('permission:agriculture,view');
        Route::post('/harvests', [\App\Http\Controllers\HarvestLogController::class, 'store'])->name('harvests.store')->middleware('permission:agriculture,create');
        Route::get('/harvests/{harvestLog}', [\App\Http\Controllers\HarvestLogController::class, 'show'])->name('harvests.show')->middleware('permission:agriculture,view');
        // Analytics
        Route::get('/analytics', [\App\Http\Controllers\FarmPlotController::class, 'analytics'])->name('analytics')->middleware('permission:agriculture,view');
        // Livestock
        Route::get('/livestock', [\App\Http\Controllers\LivestockController::class, 'index'])->name('livestock')->middleware('permission:agriculture,view');
        Route::post('/livestock', [\App\Http\Controllers\LivestockController::class, 'store'])->name('livestock.store')->middleware('permission:agriculture,create');
        Route::get('/livestock/{livestockHerd}', [\App\Http\Controllers\LivestockController::class, 'show'])->name('livestock.show')->middleware('permission:agriculture,view');
        Route::post('/livestock/{livestockHerd}/movement', [\App\Http\Controllers\LivestockController::class, 'recordMovement'])->name('livestock.movement')->middleware('permission:agriculture,create');
        Route::post('/livestock/{livestockHerd}/feed', [\App\Http\Controllers\LivestockController::class, 'storeFeedLog'])->name('livestock.feed.store')->middleware('permission:agriculture,create');
        Route::post('/livestock/{livestockHerd}/health', [\App\Http\Controllers\LivestockController::class, 'storeHealthRecord'])->name('livestock.health.store')->middleware('permission:agriculture,create');
        Route::post('/livestock/{livestockHerd}/vaccinations/generate', [\App\Http\Controllers\LivestockController::class, 'generateVaccinationSchedule'])->name('livestock.vaccinations.generate')->middleware('permission:agriculture,create');
        Route::patch('/vaccinations/{vaccination}/record', [\App\Http\Controllers\LivestockController::class, 'recordVaccination'])->name('livestock.vaccinations.record')->middleware('permission:agriculture,edit');
    });

    // Fleet Management
    Route::prefix('fleet')->name('fleet.')->middleware('role:admin,manager,gudang')->group(function () {
        Route::get('/', [FleetController::class, 'index'])->name('index')->middleware('permission:fleet,view');
        Route::post('/vehicles', [FleetController::class, 'storeVehicle'])->name('vehicles.store')->middleware('permission:fleet,create');
        Route::put('/vehicles/{fleetVehicle}', [FleetController::class, 'updateVehicle'])->name('vehicles.update')->middleware('permission:fleet,edit');
        Route::delete('/vehicles/{fleetVehicle}', [FleetController::class, 'destroyVehicle'])->name('vehicles.destroy')->middleware('permission:fleet,delete');
        Route::get('/drivers', [FleetController::class, 'drivers'])->name('drivers')->middleware('permission:fleet,view');
        Route::post('/drivers', [FleetController::class, 'storeDriver'])->name('drivers.store')->middleware('permission:fleet,create');
        Route::put('/drivers/{fleetDriver}', [FleetController::class, 'updateDriver'])->name('drivers.update')->middleware('permission:fleet,edit');
        Route::delete('/drivers/{fleetDriver}', [FleetController::class, 'destroyDriver'])->name('drivers.destroy')->middleware('permission:fleet,delete');
        Route::get('/trips', [FleetController::class, 'trips'])->name('trips')->middleware('permission:fleet,view');
        Route::post('/trips', [FleetController::class, 'storeTrip'])->name('trips.store')->middleware('permission:fleet,create');
        Route::patch('/trips/{fleetTrip}/complete', [FleetController::class, 'completeTrip'])->name('trips.complete')->middleware('permission:fleet,edit');
        Route::get('/fuel-logs', [FleetController::class, 'fuelLogs'])->name('fuel-logs')->middleware('permission:fleet,view');
        Route::post('/fuel-logs', [FleetController::class, 'storeFuelLog'])->name('fuel-logs.store')->middleware('permission:fleet,create');
        Route::get('/maintenance', [FleetController::class, 'maintenance'])->name('maintenance')->middleware('permission:fleet,view');
        Route::post('/maintenance', [FleetController::class, 'storeMaintenance'])->name('maintenance.store')->middleware('permission:fleet,create');
        Route::patch('/maintenance/{fleetMaintenance}/complete', [FleetController::class, 'completeMaintenance'])->name('maintenance.complete')->middleware('permission:fleet,edit');
        Route::delete('/maintenance/{fleetMaintenance}', [FleetController::class, 'destroyMaintenance'])->name('maintenance.destroy')->middleware('permission:fleet,delete');
    });

    // Contract Management
    Route::prefix('contracts')->name('contracts.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index')->middleware('permission:contracts,view');
        Route::post('/', [ContractController::class, 'store'])->name('store')->middleware('permission:contracts,create');
        Route::get('/templates', [ContractController::class, 'templates'])->name('templates')->middleware('permission:contracts,view');
        Route::post('/templates', [ContractController::class, 'storeTemplate'])->name('templates.store')->middleware('permission:contracts,create');
        Route::delete('/templates/{contractTemplate}', [ContractController::class, 'destroyTemplate'])->name('templates.destroy')->middleware('permission:contracts,delete');
        Route::get('/{contract}', [ContractController::class, 'show'])->name('show')->middleware('permission:contracts,view');
        Route::patch('/{contract}/activate', [ContractController::class, 'activate'])->name('activate')->middleware('permission:contracts,edit');
        Route::patch('/{contract}/terminate', [ContractController::class, 'terminate'])->name('terminate')->middleware('permission:contracts,edit');
        Route::post('/{contract}/renew', [ContractController::class, 'renew'])->name('renew')->middleware('permission:contracts,create');
        Route::post('/{contract}/billing', [ContractController::class, 'generateBilling'])->name('billing')->middleware('permission:contracts,create');
        Route::post('/{contract}/sla', [ContractController::class, 'storeSlaLog'])->name('sla.store')->middleware('permission:contracts,create');
        Route::patch('/sla/{contractSlaLog}/resolve', [ContractController::class, 'resolveSlaLog'])->name('sla.resolve')->middleware('permission:contracts,edit');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy')->middleware('permission:contracts,delete');
    });

    // Landed Cost
    Route::prefix('landed-cost')->name('landed-cost.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [LandedCostController::class, 'index'])->name('index')->middleware('permission:landed_cost,view');
        Route::post('/', [LandedCostController::class, 'store'])->name('store')->middleware('permission:landed_cost,create');
        Route::get('/{landedCost}', [LandedCostController::class, 'show'])->name('show')->middleware('permission:landed_cost,view');
        Route::post('/{landedCost}/allocate', [LandedCostController::class, 'allocate'])->name('allocate')->middleware('permission:landed_cost,edit');
        Route::post('/{landedCost}/post', [LandedCostController::class, 'post'])->name('post')->middleware('permission:landed_cost,edit');
        Route::patch('/allocation/{allocation}/weight', [LandedCostController::class, 'updateWeight'])->name('weight')->middleware('permission:landed_cost,edit');
        Route::delete('/{landedCost}', [LandedCostController::class, 'destroy'])->name('destroy')->middleware('permission:landed_cost,delete');
    });

    // Consignment
    Route::prefix('consignment')->name('consignment.')->middleware('role:admin,manager,gudang')->group(function () {
        Route::get('/', [ConsignmentController::class, 'index'])->name('index')->middleware('permission:consignment,view');
        Route::post('/shipments', [ConsignmentController::class, 'storeShipment'])->name('shipments.store')->middleware('permission:consignment,create');
        Route::get('/shipments/{consignmentShipment}', [ConsignmentController::class, 'show'])->name('shipments.show')->middleware('permission:consignment,view');
        Route::post('/shipments/{consignmentShipment}/sales-report', [ConsignmentController::class, 'storeSalesReport'])->name('sales-report.store')->middleware('permission:consignment,create');
        Route::post('/shipments/{consignmentShipment}/return', [ConsignmentController::class, 'returnItems'])->name('return')->middleware('permission:consignment,edit');
        Route::get('/partners', [ConsignmentController::class, 'partners'])->name('partners')->middleware('permission:consignment,view');
        Route::post('/partners', [ConsignmentController::class, 'storePartner'])->name('partners.store')->middleware('permission:consignment,create');
        Route::delete('/partners/{consignmentPartner}', [ConsignmentController::class, 'destroyPartner'])->name('partners.destroy')->middleware('permission:consignment,delete');
        Route::post('/settlement/{consignmentSalesReport}', [ConsignmentController::class, 'storeSettlement'])->name('settlement.store')->middleware('permission:consignment,create');
    });

    // Commission Management
    Route::prefix('commission')->name('commission.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index')->middleware('permission:commission,view');
        Route::post('/calculate', [CommissionController::class, 'calculate'])->name('calculate')->middleware('permission:commission,create');
        Route::post('/targets', [CommissionController::class, 'storeTarget'])->name('targets.store')->middleware('permission:commission,create');
        Route::get('/rules', [CommissionController::class, 'rules'])->name('rules')->middleware('permission:commission,view');
        Route::post('/rules', [CommissionController::class, 'storeRule'])->name('rules.store')->middleware('permission:commission,create');
        Route::delete('/rules/{commissionRule}', [CommissionController::class, 'destroyRule'])->name('rules.destroy')->middleware('permission:commission,delete');
        Route::patch('/{commissionCalculation}/approve', [CommissionController::class, 'approve'])->name('approve')->middleware('permission:commission,edit');
        Route::post('/{commissionCalculation}/pay', [CommissionController::class, 'pay'])->name('pay')->middleware('permission:commission,edit');
    });

    // Helpdesk / Ticketing
    Route::prefix('helpdesk')->name('helpdesk.')->middleware('role:admin,manager,staff')->group(function () {
        Route::get('/', [HelpdeskController::class, 'index'])->name('index')->middleware('permission:helpdesk,view');
        Route::post('/', [HelpdeskController::class, 'store'])->name('store')->middleware('permission:helpdesk,create');
        Route::get('/knowledge-base', [HelpdeskController::class, 'knowledgeBase'])->name('kb')->middleware('permission:helpdesk,view');
        Route::post('/knowledge-base', [HelpdeskController::class, 'storeArticle'])->name('kb.store')->middleware('permission:helpdesk,create');
        Route::delete('/knowledge-base/{kbArticle}', [HelpdeskController::class, 'destroyArticle'])->name('kb.destroy')->middleware('permission:helpdesk,delete');
        Route::get('/{helpdeskTicket}', [HelpdeskController::class, 'show'])->name('show')->middleware('permission:helpdesk,view');
        Route::post('/{helpdeskTicket}/reply', [HelpdeskController::class, 'reply'])->name('reply')->middleware('permission:helpdesk,create');
        Route::patch('/{helpdeskTicket}/status', [HelpdeskController::class, 'updateStatus'])->name('status')->middleware('permission:helpdesk,edit');
        Route::patch('/{helpdeskTicket}/rate', [HelpdeskController::class, 'rate'])->name('rate')->middleware('permission:helpdesk,edit');
    });

    // Project Billing
    Route::prefix('project-billing')->name('project-billing.')->middleware('role:admin,manager')->group(function () {
        Route::get('/{project}', [ProjectBillingController::class, 'show'])->name('show')->middleware('permission:project_billing,view');
        Route::post('/{project}/config', [ProjectBillingController::class, 'saveConfig'])->name('config')->middleware('permission:project_billing,edit');
        Route::post('/{project}/milestones', [ProjectBillingController::class, 'storeMilestone'])->name('milestones.store')->middleware('permission:project_billing,create');
        Route::patch('/milestones/{projectMilestone}/complete', [ProjectBillingController::class, 'completeMilestone'])->name('milestones.complete')->middleware('permission:project_billing,edit');
        Route::post('/milestones/{projectMilestone}/invoice', [ProjectBillingController::class, 'generateMilestone'])->name('milestones.invoice')->middleware('permission:project_billing,create');
        Route::post('/{project}/time-material', [ProjectBillingController::class, 'generateTimeMaterial'])->name('time-material')->middleware('permission:project_billing,create');
        Route::post('/{project}/retainer', [ProjectBillingController::class, 'generateRetainer'])->name('retainer')->middleware('permission:project_billing,create');
        Route::post('/{project}/termin', [ProjectBillingController::class, 'generateTermin'])->name('termin')->middleware('permission:project_billing,create');
        Route::post('/invoices/{projectInvoice}/release-retention', [ProjectBillingController::class, 'releaseRetention'])->name('release-retention')->middleware('permission:project_billing,edit');
    });

    // Subscription Billing
    Route::prefix('subscription-billing')->name('subscription-billing.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [SubscriptionBillingController::class, 'index'])->name('index')->middleware('permission:subscription_billing,view');
        Route::post('/', [SubscriptionBillingController::class, 'store'])->name('store')->middleware('permission:subscription_billing,create');
        Route::get('/plans', [SubscriptionBillingController::class, 'plans'])->name('plans')->middleware('permission:subscription_billing,view');
        Route::post('/plans', [SubscriptionBillingController::class, 'storePlan'])->name('plans.store')->middleware('permission:subscription_billing,create');
        Route::delete('/plans/{customerSubscriptionPlan}', [SubscriptionBillingController::class, 'destroyPlan'])->name('plans.destroy')->middleware('permission:subscription_billing,delete');
        Route::post('/bulk-generate', [SubscriptionBillingController::class, 'bulkGenerate'])->name('bulk-generate')->middleware('permission:subscription_billing,create');
        Route::get('/{customerSubscription}', [SubscriptionBillingController::class, 'show'])->name('show')->middleware('permission:subscription_billing,view');
        Route::post('/{customerSubscription}/generate', [SubscriptionBillingController::class, 'generateBilling'])->name('generate')->middleware('permission:subscription_billing,create');
        Route::patch('/{customerSubscription}/cancel', [SubscriptionBillingController::class, 'cancel'])->name('cancel')->middleware('permission:subscription_billing,edit');
    });

    // AI Forecasting
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index')
        ->middleware(['role:admin,manager', 'permission:reports,view']);

    // Quotations (Penawaran Harga)
    Route::prefix('quotations')->name('quotations.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [QuotationController::class, 'index'])->name('index');
        Route::post('/', [QuotationController::class, 'store'])->name('store');
        Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->name('update');
        Route::patch('/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('status');
        Route::post('/{quotation}/convert', [QuotationController::class, 'convertToOrder'])->name('convert');
        Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
    });

    // Document Management
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
    });

    // Timesheet
    Route::prefix('timesheets')->name('timesheets.')->group(function () {
        Route::get('/', [TimesheetController::class, 'index'])->name('index');
        Route::post('/', [TimesheetController::class, 'store'])->name('store');
        Route::delete('/{timesheet}', [TimesheetController::class, 'destroy'])->name('destroy');
    });

    // General Ledger & Accounting (admin + manager only)
    Route::prefix('accounting')->name('accounting.')->middleware('role:admin,manager')->group(function () {
        // Chart of Accounts
        Route::get('/coa', [AccountingController::class, 'coa'])->name('coa');
        Route::post('/coa', [AccountingController::class, 'storeCoa'])->name('coa.store');
        Route::put('/coa/{account}', [AccountingController::class, 'updateCoa'])->name('coa.update');
        Route::delete('/coa/{account}', [AccountingController::class, 'destroyCoa'])->name('coa.destroy');
        Route::post('/coa/seed-default', [AccountingController::class, 'seedDefaultCoa'])->name('coa.seed');

        // Accounting Periods
        Route::get('/periods', [AccountingController::class, 'periods'])->name('periods');
        Route::post('/periods', [AccountingController::class, 'storePeriod'])->name('periods.store');
        Route::patch('/periods/{period}/close', [AccountingController::class, 'closePeriod'])->name('periods.close');
        Route::patch('/periods/{period}/lock', [AccountingController::class, 'lockPeriod'])->name('periods.lock');

        // Period Lock & Fiscal Year Management (admin only)
        Route::prefix('period-lock')->name('period-lock.')->middleware('role:admin')->group(function () {
            Route::get('/', [\App\Http\Controllers\PeriodLockController::class, 'index'])->name('index');
            // Fiscal Years
            Route::post('/fiscal-years', [\App\Http\Controllers\PeriodLockController::class, 'storeFiscalYear'])->name('fiscal-years.store');
            Route::patch('/fiscal-years/{fiscalYear}/close', [\App\Http\Controllers\PeriodLockController::class, 'closeFiscalYear'])->name('fiscal-years.close');
            Route::patch('/fiscal-years/{fiscalYear}/lock', [\App\Http\Controllers\PeriodLockController::class, 'lockFiscalYear'])->name('fiscal-years.lock');
            Route::patch('/fiscal-years/{fiscalYear}/reopen', [\App\Http\Controllers\PeriodLockController::class, 'reopenFiscalYear'])->name('fiscal-years.reopen');
            // Period lock
            Route::patch('/periods/{period}/lock', [\App\Http\Controllers\PeriodLockController::class, 'lockPeriod'])->name('periods.lock');
            // Backups
            Route::post('/backups', [\App\Http\Controllers\PeriodLockController::class, 'createBackup'])->name('backups.store');
            Route::get('/backups/{periodBackup}/download', [\App\Http\Controllers\PeriodLockController::class, 'downloadBackup'])->name('backups.download');
            Route::delete('/backups/{periodBackup}', [\App\Http\Controllers\PeriodLockController::class, 'destroyBackup'])->name('backups.destroy');
        });

        // Trial Balance
        Route::get('/trial-balance', [AccountingController::class, 'trialBalance'])->name('trial-balance');

        // Balance Sheet (Neraca)
        Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/balance-sheet/pdf', [AccountingController::class, 'balanceSheetPdf'])->name('balance-sheet.pdf');

        // Income Statement (Laba Rugi dari GL)
        Route::get('/income-statement', [AccountingController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/income-statement/pdf', [AccountingController::class, 'incomeStatementPdf'])->name('income-statement.pdf');

        // Cash Flow Statement (Arus Kas)
        Route::get('/cash-flow', [AccountingController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/cash-flow/pdf', [AccountingController::class, 'cashFlowPdf'])->name('cash-flow.pdf');
    });

    // Journal Entries (admin + manager only)
    Route::prefix('journals')->name('journals.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [JournalController::class, 'index'])->name('index');
        Route::get('/create', [JournalController::class, 'create'])->name('create');
        Route::post('/', [JournalController::class, 'store'])->name('store');
        // Recurring — must be before /{journal} wildcard to avoid conflict
        Route::get('/recurring/list', [JournalController::class, 'recurringIndex'])->name('recurring');
        Route::post('/recurring', [JournalController::class, 'storeRecurring'])->name('recurring.store');
        Route::patch('/recurring/{recurring}/toggle', [JournalController::class, 'toggleRecurring'])->name('recurring.toggle');
        // Wildcard routes last
        Route::get('/{journal}', [JournalController::class, 'show'])->name('show');
        Route::patch('/{journal}/post', [JournalController::class, 'post'])->name('post');
        Route::post('/{journal}/reverse', [JournalController::class, 'reverse'])->name('reverse');
    });

    // Reminders
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->name('index');
        Route::post('/', [ReminderController::class, 'store'])->name('store');
        Route::patch('/{reminder}/done', [ReminderController::class, 'markDone'])->name('done');
        Route::delete('/{reminder}', [ReminderController::class, 'destroy'])->name('destroy');
    });

    // Sales Returns (Retur Penjualan)
    Route::prefix('sales-returns')->name('sales-returns.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\SalesReturnController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SalesReturnController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SalesReturnController::class, 'store'])->name('store');
        Route::post('/{salesReturn}/approve', [\App\Http\Controllers\SalesReturnController::class, 'approve'])->name('approve');
        Route::post('/{salesReturn}/complete', [\App\Http\Controllers\SalesReturnController::class, 'complete'])->name('complete');
        Route::post('/{salesReturn}/cancel', [\App\Http\Controllers\SalesReturnController::class, 'cancel'])->name('cancel');
        Route::get('/invoice/{invoice}/items', [\App\Http\Controllers\SalesReturnController::class, 'invoiceItems'])->name('invoice-items');
    });

    // Purchase Returns (Retur Pembelian)
    Route::prefix('purchase-returns')->name('purchase-returns.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\PurchaseReturnController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\PurchaseReturnController::class, 'store'])->name('store');
        Route::post('/{purchaseReturn}/send', [\App\Http\Controllers\PurchaseReturnController::class, 'send'])->name('send');
        Route::post('/{purchaseReturn}/complete', [\App\Http\Controllers\PurchaseReturnController::class, 'complete'])->name('complete');
        Route::post('/{purchaseReturn}/cancel', [\App\Http\Controllers\PurchaseReturnController::class, 'cancel'])->name('cancel');
        Route::get('/po/{purchaseOrder}/items', [\App\Http\Controllers\PurchaseReturnController::class, 'poItems'])->name('po-items');
    });

    // Down Payments (Uang Muka)
    Route::prefix('down-payments')->name('down-payments.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\DownPaymentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DownPaymentController::class, 'store'])->name('store');
        Route::post('/{downPayment}/apply', [\App\Http\Controllers\DownPaymentController::class, 'apply'])->name('apply');
    });

    // Bulk Payments
    Route::prefix('bulk-payments')->name('bulk-payments.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\BulkPaymentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\BulkPaymentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\BulkPaymentController::class, 'store'])->name('store');
        Route::get('/customer-invoices', [\App\Http\Controllers\BulkPaymentController::class, 'customerInvoices'])->name('customer-invoices');
    });

    // Cost Centers (Task 44)
    Route::prefix('settings/cost-centers')->name('cost-centers.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\CostCenterController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\CostCenterController::class, 'store'])->name('store');
        Route::put('/{costCenter}', [\App\Http\Controllers\CostCenterController::class, 'update'])->name('update');
        Route::delete('/{costCenter}', [\App\Http\Controllers\CostCenterController::class, 'destroy'])->name('destroy');
        Route::get('/report', [\App\Http\Controllers\CostCenterController::class, 'report'])->name('report');
    });

    // Business Constraints (Task 45)
    Route::prefix('settings/constraints')->name('constraints.')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\BusinessConstraintController::class, 'index'])->name('index');
        Route::put('/{businessConstraint}', [\App\Http\Controllers\BusinessConstraintController::class, 'update'])->name('update');
        Route::post('/bulk', [\App\Http\Controllers\BusinessConstraintController::class, 'bulkUpdate'])->name('bulk');
    });

    // Transaction Chain (Task 46)
    Route::prefix('transaction-chain')->name('transaction-chain.')->middleware('auth')->group(function () {
        Route::get('/{type}/{id}', [\App\Http\Controllers\TransactionChainController::class, 'show'])->name('show');
        Route::get('/{type}/{id}/timeline', [\App\Http\Controllers\TransactionChainController::class, 'timeline'])->name('timeline');
    });

    // Deferred Revenue & Prepaid Expense (Task 47)
    Route::prefix('deferred')->name('deferred.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\DeferredItemController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DeferredItemController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DeferredItemController::class, 'store'])->name('store');
        Route::get('/{deferredItem}', [\App\Http\Controllers\DeferredItemController::class, 'show'])->name('show');
        Route::post('/schedules/{schedule}/post', [\App\Http\Controllers\DeferredItemController::class, 'postSchedule'])->name('schedule.post');
        Route::patch('/{deferredItem}/cancel', [\App\Http\Controllers\DeferredItemController::class, 'cancel'])->name('cancel');
    });

    // Write-off Hutang/Piutang (Task 48)
    Route::prefix('writeoffs')->name('writeoffs.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\WriteoffController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\WriteoffController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\WriteoffController::class, 'store'])->name('store');
        Route::post('/{writeoff}/approve', [\App\Http\Controllers\WriteoffController::class, 'approve'])->name('approve');
        Route::post('/{writeoff}/reject', [\App\Http\Controllers\WriteoffController::class, 'reject'])->name('reject');
        Route::post('/{writeoff}/post', [\App\Http\Controllers\WriteoffController::class, 'post'])->name('post');
    });

    // Price List per Customer (Task 49)
    Route::prefix('price-lists')->name('price-lists.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\PriceListController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\PriceListController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\PriceListController::class, 'store'])->name('store');
        Route::get('/{priceList}', [\App\Http\Controllers\PriceListController::class, 'show'])->name('show');
        Route::put('/{priceList}', [\App\Http\Controllers\PriceListController::class, 'update'])->name('update');
        Route::delete('/{priceList}', [\App\Http\Controllers\PriceListController::class, 'destroy'])->name('destroy');
        Route::post('/{priceList}/customers', [\App\Http\Controllers\PriceListController::class, 'assignCustomer'])->name('customers.assign');
        Route::delete('/{priceList}/customers/{customer}', [\App\Http\Controllers\PriceListController::class, 'removeCustomer'])->name('customers.remove');
        Route::get('/api/price', [\App\Http\Controllers\PriceListController::class, 'getPrice'])->name('api.price');
    });

    // Simulations — Task 50
    Route::prefix('simulations')->name('simulations.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\SimulationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SimulationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SimulationController::class, 'store'])->name('store');
        Route::get('/{simulation}', [\App\Http\Controllers\SimulationController::class, 'show'])->name('show');
        Route::delete('/{simulation}', [\App\Http\Controllers\SimulationController::class, 'destroy'])->name('destroy');
    });

    // Anomaly Detection — Task 51
    Route::prefix('anomalies')->name('anomalies.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\AnomalyController::class, 'index'])->name('index');
        Route::post('/detect', [\App\Http\Controllers\AnomalyController::class, 'detect'])->name('detect');
        Route::post('/{anomaly}/acknowledge', [\App\Http\Controllers\AnomalyController::class, 'acknowledge'])->name('acknowledge');
        Route::post('/{anomaly}/resolve', [\App\Http\Controllers\AnomalyController::class, 'resolve'])->name('resolve');
    });

    // AI Memory — Task 52
    Route::prefix('settings/ai-memory')->name('ai-memory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AiMemoryController::class, 'index'])->name('index');
        Route::post('/reset', [\App\Http\Controllers\AiMemoryController::class, 'reset'])->name('reset');
        Route::post('/prune', [\App\Http\Controllers\AiMemoryController::class, 'pruneStale'])->name('prune');
        Route::post('/{memory}/lock', [\App\Http\Controllers\AiMemoryController::class, 'lock'])->name('lock');
        Route::delete('/{aiMemory}', [\App\Http\Controllers\AiMemoryController::class, 'destroy'])->name('destroy');
    });

    // Delivery Orders (Surat Jalan)
    Route::prefix('delivery-orders')->name('delivery-orders.')->middleware(['role:admin,manager', 'tenant.isolation'])->group(function () {
        Route::get('/', [\App\Http\Controllers\DeliveryOrderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DeliveryOrderController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DeliveryOrderController::class, 'store'])->name('store');
        Route::post('/{deliveryOrder}/ship', [\App\Http\Controllers\DeliveryOrderController::class, 'ship'])->name('ship');
        Route::post('/{deliveryOrder}/deliver', [\App\Http\Controllers\DeliveryOrderController::class, 'deliver'])->name('deliver');
        Route::post('/{deliveryOrder}/invoice', [\App\Http\Controllers\DeliveryOrderController::class, 'createInvoice'])->name('invoice');
        Route::get('/so/{salesOrder}/items', [\App\Http\Controllers\DeliveryOrderController::class, 'soItems'])->name('so-items');
    });

    // 2FA Setup — Task 53
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/setup', [\App\Http\Controllers\Auth\TwoFactorController::class, 'setup'])->name('setup');
        Route::post('/confirm', [\App\Http\Controllers\Auth\TwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('/disable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('disable');
        Route::post('/recovery-codes', [\App\Http\Controllers\Auth\TwoFactorController::class, 'regenerateCodes'])->name('recovery-codes');
    });

    // Custom Fields — Task 54
    Route::prefix('settings/custom-fields')->name('custom-fields.')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\CustomFieldController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\CustomFieldController::class, 'store'])->name('store');
        Route::put('/{customField}', [\App\Http\Controllers\CustomFieldController::class, 'update'])->name('update');
        Route::delete('/{customField}', [\App\Http\Controllers\CustomFieldController::class, 'destroy'])->name('destroy');
    });

    // Multi Company — Enterprise Feature
    Route::prefix('company-groups')->name('company-groups.')->middleware(['role:admin', 'permission:company_groups,view'])->group(function () {
        Route::get('/', [\App\Http\Controllers\CompanyGroupController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\CompanyGroupController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\CompanyGroupController::class, 'store'])->name('store');
        Route::get('/{companyGroup}', [\App\Http\Controllers\CompanyGroupController::class, 'show'])->name('show');
        Route::post('/{companyGroup}/members', [\App\Http\Controllers\CompanyGroupController::class, 'addMember'])->name('members.add');
        Route::delete('/{companyGroup}/members/{tenant}', [\App\Http\Controllers\CompanyGroupController::class, 'removeMember'])->name('members.remove');
        Route::post('/{companyGroup}/transactions', [\App\Http\Controllers\CompanyGroupController::class, 'storeTransaction'])->name('transactions.store');
        Route::post('/transactions/{transaction}/post', [\App\Http\Controllers\CompanyGroupController::class, 'postTransaction'])->name('transactions.post');
        Route::post('/transactions/{transaction}/void', [\App\Http\Controllers\CompanyGroupController::class, 'voidTransaction'])->name('transactions.void');
        Route::get('/{companyGroup}/export', [\App\Http\Controllers\CompanyGroupController::class, 'exportCsv'])->name('export');
    });

    // Zero Input ERP — Task 56
    Route::prefix('zero-input')->name('zero-input.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ZeroInputController::class, 'index'])->name('index');
        Route::post('/photo', [\App\Http\Controllers\ZeroInputController::class, 'uploadPhoto'])->name('photo');
        Route::post('/text', [\App\Http\Controllers\ZeroInputController::class, 'processText'])->name('text');
        Route::get('/{zeroInputLog}', [\App\Http\Controllers\ZeroInputController::class, 'show'])->name('show');
        Route::post('/{zeroInputLog}/confirm', [\App\Http\Controllers\ZeroInputController::class, 'confirm'])->name('confirm');
        Route::post('/{zeroInputLog}/reject', [\App\Http\Controllers\ZeroInputController::class, 'reject'])->name('reject');
    });

    // Multi-Company Consolidation — Enterprise Feature
    Route::prefix('consolidation')->name('consolidation.')->middleware(['role:admin'])->group(function () {
        Route::get('/', [\App\Http\Controllers\ConsolidationController::class, 'index'])->name('index');

        // Company Group Management
        Route::get('/groups/create', [\App\Http\Controllers\ConsolidationController::class, 'createGroup'])->name('groups.create');
        Route::post('/groups', [\App\Http\Controllers\ConsolidationController::class, 'storeGroup'])->name('groups.store');
        Route::get('/groups/{group}', [\App\Http\Controllers\ConsolidationController::class, 'show'])->name('show');
        Route::post('/groups/{group}/members', [\App\Http\Controllers\ConsolidationController::class, 'addMember'])->name('members.add');
        Route::delete('/groups/{group}/members/{tenantId}', [\App\Http\Controllers\ConsolidationController::class, 'removeMember'])->name('members.remove');

        // Consolidation Reports
        Route::post('/groups/{group}/reports', [\App\Http\Controllers\ConsolidationController::class, 'generateReport'])->name('report.generate');
        Route::get('/groups/{group}/reports/{report}', [\App\Http\Controllers\ConsolidationController::class, 'showReport'])->name('report.show');
        Route::post('/groups/{group}/reports/{report}/finalize', [\App\Http\Controllers\ConsolidationController::class, 'finalizeReport'])->name('report.finalize');
        Route::get('/groups/{group}/reports/{report}/export', [\App\Http\Controllers\ConsolidationController::class, 'exportReport'])->name('report.export');

        // Master Chart of Accounts
        Route::get('/groups/{group}/master-accounts', [\App\Http\Controllers\ConsolidationController::class, 'masterAccounts'])->name('master-accounts');
        Route::post('/groups/{group}/master-accounts', [\App\Http\Controllers\ConsolidationController::class, 'storeMasterAccount'])->name('master-accounts.store');

        // Account Mappings
        Route::get('/groups/{group}/mappings', [\App\Http\Controllers\ConsolidationController::class, 'accountMappings'])->name('mappings');
        Route::patch('/groups/{group}/mappings/{mapping}', [\App\Http\Controllers\ConsolidationController::class, 'updateMapping'])->name('mappings.update');

        // Elimination Entries
        Route::get('/groups/{group}/eliminations', [\App\Http\Controllers\ConsolidationController::class, 'eliminations'])->name('eliminations');
        Route::post('/groups/{group}/eliminations', [\App\Http\Controllers\ConsolidationController::class, 'storeElimination'])->name('eliminations.store');

        // Ownership Structure
        Route::get('/groups/{group}/ownerships', [\App\Http\Controllers\ConsolidationController::class, 'ownerships'])->name('ownerships');
        Route::post('/groups/{group}/ownerships', [\App\Http\Controllers\ConsolidationController::class, 'storeOwnership'])->name('ownerships.store');
    });

    // =============================================
    // HOTEL PMS MODULE
    // =============================================
    Route::prefix('hotel')->name('hotel.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Hotel\HotelDashboardController::class, 'index'])->name('dashboard');

        // Settings
        Route::get('settings', [App\Http\Controllers\Hotel\HotelSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [App\Http\Controllers\Hotel\HotelSettingController::class, 'update'])->name('settings.update');

        // Room Types
        Route::resource('room-types', App\Http\Controllers\Hotel\RoomTypeController::class)->except(['show', 'edit', 'create'])->names('room-types');

        // Rooms
        Route::get('rooms/availability', [App\Http\Controllers\Hotel\RoomController::class, 'availability'])->name('rooms.availability');
        Route::patch('rooms/{room}/status', [App\Http\Controllers\Hotel\RoomController::class, 'updateStatus'])->name('rooms.status');
        Route::resource('rooms', App\Http\Controllers\Hotel\RoomController::class)->except(['show', 'edit', 'create'])->names('rooms');

        // Reservations
        Route::get('reservations/calendar', [App\Http\Controllers\Hotel\ReservationController::class, 'calendar'])->name('reservations.calendar');
        Route::post('reservations/calculate-rate', [App\Http\Controllers\Hotel\ReservationController::class, 'calculateRate'])->name('reservations.calculate-rate');
        Route::patch('reservations/{reservation}/confirm', [App\Http\Controllers\Hotel\ReservationController::class, 'confirm'])->name('reservations.confirm');
        Route::patch('reservations/{reservation}/cancel', [App\Http\Controllers\Hotel\ReservationController::class, 'cancel'])->name('reservations.cancel');

        // Reservation room changes and early/late requests
        Route::get('reservations/{reservation}/room-change', [App\Http\Controllers\Hotel\ReservationController::class, 'showRoomChange'])->name('reservations.room-change');
        Route::post('reservations/{reservation}/room-change', [App\Http\Controllers\Hotel\ReservationController::class, 'processRoomChange'])->name('reservations.process-room-change');
        Route::post('reservations/{reservation}/early-late-request', [App\Http\Controllers\Hotel\ReservationController::class, 'requestEarlyLate'])->name('reservations.request-early-late');
        Route::post('reservations/early-late/{request}/approve', [App\Http\Controllers\Hotel\ReservationController::class, 'approveEarlyLate'])->name('reservations.approve-early-late');
        Route::post('reservations/early-late/{request}/reject', [App\Http\Controllers\Hotel\ReservationController::class, 'rejectEarlyLate'])->name('reservations.reject-early-late');
        Route::get('reservations/early-late/pending', [App\Http\Controllers\Hotel\ReservationController::class, 'getPendingRequests'])->name('reservations.pending-early-late');
        Route::post('reservations/{reservation}/record-check-in', [App\Http\Controllers\Hotel\ReservationController::class, 'recordCheckIn'])->name('reservations.record-check-in');
        Route::post('reservations/{reservation}/record-check-out', [App\Http\Controllers\Hotel\ReservationController::class, 'recordCheckOut'])->name('reservations.record-check-out');
        Route::get('reservations/{reservation}/room-changes', [App\Http\Controllers\Hotel\ReservationController::class, 'getRoomChanges'])->name('reservations.room-changes');

        Route::resource('reservations', App\Http\Controllers\Hotel\ReservationController::class)->names('reservations');

        // Guests
        Route::get('guests/search', [App\Http\Controllers\Hotel\GuestController::class, 'search'])->name('guests.search');
        Route::get('guests/{guest}/history', [App\Http\Controllers\Hotel\GuestController::class, 'history'])->name('guests.history');
        Route::get('guests/{guest}/preferences', [App\Http\Controllers\Hotel\GuestController::class, 'preferences'])->name('guests.preferences');
        Route::post('guests/{guest}/preferences', [App\Http\Controllers\Hotel\GuestController::class, 'storePreference'])->name('guests.store-preference');
        Route::patch('guests/{guest}/preferences/{preference}', [App\Http\Controllers\Hotel\GuestController::class, 'updatePreference'])->name('guests.update-preference');
        Route::delete('guests/{guest}/preferences/{preference}', [App\Http\Controllers\Hotel\GuestController::class, 'destroyPreference'])->name('guests.destroy-preference');
        Route::post('guests/{guest}/award-points', [App\Http\Controllers\Hotel\GuestController::class, 'awardPoints'])->name('guests.award-points');
        Route::patch('guests/{guest}/vip-level', [App\Http\Controllers\Hotel\GuestController::class, 'updateVipLevel'])->name('guests.update-vip-level');
        Route::resource('guests', App\Http\Controllers\Hotel\GuestController::class)->names('guests');

        // Group Bookings
        Route::get('group-bookings/search', [App\Http\Controllers\Hotel\GroupBookingController::class, 'search'])->name('group-bookings.search');
        Route::post('group-bookings/{groupBooking}/add-reservation', [App\Http\Controllers\Hotel\GroupBookingController::class, 'addReservation'])->name('group-bookings.add-reservation');
        Route::post('group-bookings/{groupBooking}/confirm', [App\Http\Controllers\Hotel\GroupBookingController::class, 'confirm'])->name('group-bookings.confirm');
        Route::post('group-bookings/{groupBooking}/cancel', [App\Http\Controllers\Hotel\GroupBookingController::class, 'cancel'])->name('group-bookings.cancel');
        Route::post('group-bookings/{groupBooking}/payment', [App\Http\Controllers\Hotel\GroupBookingController::class, 'processPayment'])->name('group-bookings.payment');
        Route::post('group-bookings/{groupBooking}/add-benefit', [App\Http\Controllers\Hotel\GroupBookingController::class, 'addBenefit'])->name('group-bookings.add-benefit');
        Route::delete('reservations/{reservation}/remove-from-group', [App\Http\Controllers\Hotel\GroupBookingController::class, 'removeReservation'])->name('group-bookings.remove-reservation');
        Route::resource('group-bookings', App\Http\Controllers\Hotel\GroupBookingController::class)->names('group-bookings');

        // Walk-in Reservations
        Route::get('walk-ins/statistics', [App\Http\Controllers\Hotel\WalkInReservationController::class, 'statistics'])->name('walk-ins.statistics');
        Route::post('walk-ins/quick-check-in', [App\Http\Controllers\Hotel\WalkInReservationController::class, 'quickCheckIn'])->name('walk-ins.quick-check-in');
        Route::resource('walk-ins', App\Http\Controllers\Hotel\WalkInReservationController::class)->except(['create', 'store'])->names('walk-ins');

        // Check-in / Check-out
        Route::get('check-in/{reservation}', [App\Http\Controllers\Hotel\CheckInOutController::class, 'checkInForm'])->name('checkin.form');
        Route::post('check-in/{reservation}', [App\Http\Controllers\Hotel\CheckInOutController::class, 'processCheckIn'])->name('checkin.process');
        Route::get('check-out/{reservation}', [App\Http\Controllers\Hotel\CheckInOutController::class, 'checkOutForm'])->name('checkout.form');
        Route::post('check-out/{reservation}', [App\Http\Controllers\Hotel\CheckInOutController::class, 'processCheckOut'])->name('checkout.process');

        // Housekeeping Module
        Route::prefix('housekeeping')->name('housekeeping.')->group(function () {
            // Dashboard & Room Board
            Route::get('/', [App\Http\Controllers\Hotel\HousekeepingController::class, 'index'])->name('index');
            Route::get('room-board', [App\Http\Controllers\Hotel\HousekeepingController::class, 'roomBoard'])->name('room-board');
            Route::post('rooms/{roomId}/status', [App\Http\Controllers\Hotel\HousekeepingController::class, 'updateRoomStatus'])->name('rooms.status');

            // Tasks Management
            Route::get('tasks', [App\Http\Controllers\Hotel\HousekeepingController::class, 'tasks'])->name('tasks.index');
            Route::post('tasks/{taskId}/assign', [App\Http\Controllers\Hotel\HousekeepingController::class, 'assignTask'])->name('tasks.assign');
            Route::post('tasks/{taskId}/start', [App\Http\Controllers\Hotel\HousekeepingController::class, 'startTask'])->name('tasks.start');
            Route::post('tasks/{taskId}/complete', [App\Http\Controllers\Hotel\HousekeepingController::class, 'completeTask'])->name('tasks.complete');

            // Maintenance Requests
            Route::get('maintenance', [App\Http\Controllers\Hotel\HousekeepingController::class, 'maintenance'])->name('maintenance.index');
            Route::post('maintenance', [App\Http\Controllers\Hotel\HousekeepingController::class, 'createMaintenanceRequest'])->name('maintenance.store');
            Route::post('maintenance/{requestId}/assign', [App\Http\Controllers\Hotel\HousekeepingController::class, 'assignMaintenanceRequest'])->name('maintenance.assign');
            Route::post('maintenance/{requestId}/complete', [App\Http\Controllers\Hotel\HousekeepingController::class, 'completeMaintenanceRequest'])->name('maintenance.complete');

            // Linen Inventory
            Route::get('linen', [App\Http\Controllers\Hotel\HousekeepingController::class, 'linenInventory'])->name('linen.index');
            Route::post('linen/movement', [App\Http\Controllers\Hotel\HousekeepingController::class, 'recordLinenMovement'])->name('linen.movement');

            // Supplies Inventory
            Route::get('supplies', [App\Http\Controllers\Hotel\HousekeepingController::class, 'supplies'])->name('supplies.index');
            Route::post('supplies/usage', [App\Http\Controllers\Hotel\HousekeepingController::class, 'recordSupplyUsage'])->name('supplies.usage');

            // Reports
            Route::get('daily-report', [App\Http\Controllers\Hotel\HousekeepingController::class, 'dailyReport'])->name('daily-report');
        });

        // Rate Management
        Route::get('rates/calendar', [App\Http\Controllers\Hotel\RateController::class, 'calendar'])->name('rates.calendar');
        Route::post('rates/bulk-update', [App\Http\Controllers\Hotel\RateController::class, 'bulkUpdate'])->name('rates.bulk-update');
        Route::resource('rates', App\Http\Controllers\Hotel\RateController::class)->except(['show', 'edit', 'create'])->names('rates');

        // Revenue Management
        Route::prefix('revenue')->name('revenue.')->group(function () {
            Route::get('/', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'dashboard'])->name('dashboard');

            // Rate Plans
            Route::get('rate-plans', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'ratePlans'])->name('rate-plans');
            Route::post('rate-plans', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'storeRatePlan'])->name('rate-plans.store');
            Route::put('rate-plans/{ratePlan}', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'updateRatePlan'])->name('rate-plans.update');

            // Pricing Rules
            Route::get('pricing-rules', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'pricingRules'])->name('pricing-rules');
            Route::post('pricing-rules', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'storePricingRule'])->name('pricing-rules.store');

            // Forecasts
            Route::get('forecasts', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'forecasts'])->name('forecasts');
            Route::post('forecasts/generate', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'generateForecasts'])->name('forecasts.generate');

            // Competitor Rates
            Route::get('competitor-rates', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'competitorRates'])->name('competitor-rates');
            Route::post('competitor-rates', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'storeCompetitorRate'])->name('competitor-rates.store');

            // Special Events
            Route::get('special-events', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'specialEvents'])->name('special-events');
            Route::post('special-events', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'storeSpecialEvent'])->name('special-events.store');

            // Recommendations
            Route::get('recommendations', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'recommendations'])->name('recommendations');
            Route::post('recommendations/{recommendation}/apply', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'applyRecommendation'])->name('recommendations.apply');
            Route::post('recommendations/{recommendation}/reject', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'rejectRecommendation'])->name('recommendations.reject');

            // Rate Calendar
            Route::get('rate-calendar', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'rateCalendar'])->name('rate-calendar');

            // Yield Optimization
            Route::get('yield-optimization', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'yieldOptimization'])->name('yield-optimization');

            // Reports
            Route::get('reports', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'reports'])->name('reports');

            // Bulk Updates
            Route::post('bulk-rate-update', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'bulkRateUpdate'])->name('bulk-rate-update');

            // API Endpoints
            Route::get('api/optimal-rate', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'getOptimalRate'])->name('api.optimal-rate');
            Route::get('api/rate-range', [App\Http\Controllers\Hotel\RevenueManagementController::class, 'getRateRange'])->name('api.rate-range');
        });

        // Food & Beverage Module
        Route::prefix('fb')->name('fb.')->group(function () {
            // Restaurant POS
            Route::get('restaurant', [App\Http\Controllers\Hotel\RestaurantController::class, 'index'])->name('restaurant.index');
            Route::post('restaurant/orders', [App\Http\Controllers\Hotel\RestaurantController::class, 'createOrder'])->name('restaurant.orders.store');
            Route::get('restaurant/orders/{id}', [App\Http\Controllers\Hotel\RestaurantController::class, 'showOrder'])->name('restaurant.orders.show');
            Route::patch('restaurant/orders/{id}/status', [App\Http\Controllers\Hotel\RestaurantController::class, 'updateOrderStatus'])->name('restaurant.orders.status');

            // Menus Management
            Route::get('menus', [App\Http\Controllers\Hotel\RestaurantController::class, 'menus'])->name('menus.index');
            Route::post('menus', [App\Http\Controllers\Hotel\RestaurantController::class, 'storeMenu'])->name('menus.store');
            Route::put('menus/{menu}', [App\Http\Controllers\Hotel\RestaurantController::class, 'updateMenu'])->name('menus.update');
            Route::get('menus/{menu}/items', [App\Http\Controllers\Hotel\RestaurantController::class, 'menuItems'])->name('menus.items');
            Route::post('menu-items', [App\Http\Controllers\Hotel\RestaurantController::class, 'storeMenuItem'])->name('menu-items.store');
            Route::put('menu-items/{menuItem}', [App\Http\Controllers\Hotel\RestaurantController::class, 'updateMenuItem'])->name('menu-items.update');
            Route::delete('menu-items/{menuItem}', [App\Http\Controllers\Hotel\RestaurantController::class, 'destroyMenuItem'])->name('menu-items.destroy');

            // Room Service
            Route::get('roomservice', [App\Http\Controllers\Hotel\RoomServiceController::class, 'index'])->name('roomservice.index');
            Route::post('roomservice/orders', [App\Http\Controllers\Hotel\RoomServiceController::class, 'createOrder'])->name('roomservice.orders.store');
            Route::get('roomservice/orders/{id}', [App\Http\Controllers\Hotel\RoomServiceController::class, 'showOrder'])->name('roomservice.orders.show');
            Route::post('roomservice/orders/{id}/deliver', [App\Http\Controllers\Hotel\RoomServiceController::class, 'deliverOrder'])->name('roomservice.orders.deliver');
            Route::post('roomservice/orders/{id}/charge', [App\Http\Controllers\Hotel\RoomServiceController::class, 'chargeToRoom'])->name('roomservice.orders.charge');
            Route::get('roomservice/menu-items', [App\Http\Controllers\Hotel\RoomServiceController::class, 'availableMenuItems'])->name('roomservice.menu-items');

            // Mini-bar
            Route::get('minibar', [App\Http\Controllers\Hotel\MinibarController::class, 'index'])->name('minibar.index');
            Route::get('minibar/room/{roomNumber}', [App\Http\Controllers\Hotel\MinibarController::class, 'roomStock'])->name('minibar.room-stock');
            Route::post('minibar/consumption', [App\Http\Controllers\Hotel\MinibarController::class, 'recordConsumption'])->name('minibar.consumption');
            Route::post('minibar/restock', [App\Http\Controllers\Hotel\MinibarController::class, 'restock'])->name('minibar.restock');
            Route::get('minibar/reservation/{reservationId}/charges', [App\Http\Controllers\Hotel\MinibarController::class, 'reservationCharges'])->name('minibar.charges');
            Route::post('minibar/reservation/{reservationId}/bill-all', [App\Http\Controllers\Hotel\MinibarController::class, 'billAllCharges'])->name('minibar.bill-all');

            // Banquet & Events
            Route::get('banquet', [App\Http\Controllers\Hotel\BanquetController::class, 'index'])->name('banquet.index');
            Route::get('banquet/create', [App\Http\Controllers\Hotel\BanquetController::class, 'create'])->name('banquet.create');
            Route::post('banquet', [App\Http\Controllers\Hotel\BanquetController::class, 'store'])->name('banquet.store');
            Route::get('banquet/{id}', [App\Http\Controllers\Hotel\BanquetController::class, 'show'])->name('banquet.show');
            Route::post('banquet/{id}/confirm', [App\Http\Controllers\Hotel\BanquetController::class, 'confirmEvent'])->name('banquet.confirm');
            Route::post('banquet/{id}/complete', [App\Http\Controllers\Hotel\BanquetController::class, 'completeEvent'])->name('banquet.complete');
            Route::post('banquet/{id}/cancel', [App\Http\Controllers\Hotel\BanquetController::class, 'cancelEvent'])->name('banquet.cancel');
            Route::patch('banquet/{id}/guest-count', [App\Http\Controllers\Hotel\BanquetController::class, 'updateGuestCount'])->name('banquet.guest-count');

            // F&B Inventory/Supplies
            Route::get('supplies', [App\Http\Controllers\Hotel\FbSuppliesController::class, 'index'])->name('supplies.index');
            Route::post('supplies', [App\Http\Controllers\Hotel\FbSuppliesController::class, 'store'])->name('supplies.store');
            Route::put('supplies/{supply}', [App\Http\Controllers\Hotel\FbSuppliesController::class, 'update'])->name('supplies.update');
            Route::post('supplies/{supply}/add-stock', [App\Http\Controllers\Hotel\FbSuppliesController::class, 'addStock'])->name('supplies.add-stock');
            Route::post('supplies/{supply}/usage', [App\Http\Controllers\Hotel\FbSuppliesController::class, 'recordUsage'])->name('supplies.usage');
            Route::get('supplies/{supply}/transactions', [App\Http\Controllers\Hotel\FbSuppliesController::class, 'transactions'])->name('supplies.transactions');

            // F&B Reports
            Route::get('reports', [App\Http\Controllers\Hotel\FbReportsController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [App\Http\Controllers\Hotel\FbReportsController::class, 'export'])->name('reports.export');
        });

        // Spa & Recreation Module
        Route::prefix('spa')->name('spa.')->group(function () {
            // Dashboard
            Route::get('/', [App\Http\Controllers\Hotel\SpaController::class, 'dashboard'])->name('dashboard');

            // Treatments
            Route::get('treatments', [App\Http\Controllers\Hotel\SpaController::class, 'treatments'])->name('treatments.index');
            Route::post('treatments', [App\Http\Controllers\Hotel\SpaController::class, 'storeTreatment'])->name('treatments.store');
            Route::put('treatments/{treatment}', [App\Http\Controllers\Hotel\SpaController::class, 'updateTreatment'])->name('treatments.update');

            // Packages
            Route::get('packages', [App\Http\Controllers\Hotel\SpaController::class, 'packages'])->name('packages.index');
            Route::get('packages/create', [App\Http\Controllers\Hotel\SpaController::class, 'createPackage'])->name('packages.create');
            Route::post('packages', [App\Http\Controllers\Hotel\SpaController::class, 'storePackage'])->name('packages.store');
            Route::get('packages/{package}', [App\Http\Controllers\Hotel\SpaController::class, 'showPackage'])->name('packages.show');

            // Therapists
            Route::get('therapists', [App\Http\Controllers\Hotel\SpaController::class, 'therapists'])->name('therapists.index');
            Route::post('therapists', [App\Http\Controllers\Hotel\SpaController::class, 'storeTherapist'])->name('therapists.store');
            Route::get('therapists/{therapist}/schedule', [App\Http\Controllers\Hotel\SpaController::class, 'therapistSchedule'])->name('therapists.schedule');

            // Bookings
            Route::get('bookings', [App\Http\Controllers\Hotel\SpaController::class, 'bookings'])->name('bookings.index');
            Route::get('bookings/create', [App\Http\Controllers\Hotel\SpaController::class, 'createBooking'])->name('bookings.create');
            Route::post('bookings', [App\Http\Controllers\Hotel\SpaController::class, 'storeBooking'])->name('bookings.store');
            Route::post('bookings/{booking}/confirm', [App\Http\Controllers\Hotel\SpaController::class, 'confirmBooking'])->name('bookings.confirm');
            Route::post('bookings/{booking}/complete', [App\Http\Controllers\Hotel\SpaController::class, 'completeBooking'])->name('bookings.complete');
            Route::post('bookings/{booking}/cancel', [App\Http\Controllers\Hotel\SpaController::class, 'cancelBooking'])->name('bookings.cancel');

            // Product Sales
            Route::get('product-sales', [App\Http\Controllers\Hotel\SpaController::class, 'productSales'])->name('product-sales.index');
            Route::post('product-sales', [App\Http\Controllers\Hotel\SpaController::class, 'recordProductSale'])->name('product-sales.store');

            // Reports
            Route::get('reports', [App\Http\Controllers\Hotel\SpaController::class, 'reports'])->name('reports.index');
        });

        // Hotel Reports & Analytics Module
        Route::prefix('reports')->name('reports.')->group(function () {
            // Dashboard
            Route::get('/', [App\Http\Controllers\Hotel\HotelReportsController::class, 'dashboard'])->name('dashboard');

            // Daily Operations Report
            Route::get('daily-operations', [App\Http\Controllers\Hotel\HotelReportsController::class, 'dailyOperations'])->name('daily-operations');

            // Revenue Report
            Route::get('revenue', [App\Http\Controllers\Hotel\HotelReportsController::class, 'revenue'])->name('revenue');

            // Occupancy Analytics
            Route::get('occupancy', [App\Http\Controllers\Hotel\HotelReportsController::class, 'occupancy'])->name('occupancy');

            // Guest Analytics
            Route::get('guest-analytics', [App\Http\Controllers\Hotel\HotelReportsController::class, 'guestAnalytics'])->name('guest-analytics');

            // Staff Performance
            Route::get('staff-performance', [App\Http\Controllers\Hotel\HotelReportsController::class, 'staffPerformance'])->name('staff-performance');
        });

        // Night Audit Module
        Route::prefix('night-audit')->name('night-audit.')->group(function () {
            // Dashboard
            Route::get('/', [App\Http\Controllers\Hotel\NightAuditController::class, 'index'])->name('index');

            // Audit Batch Processing
            Route::post('start', [App\Http\Controllers\Hotel\NightAuditController::class, 'startAudit'])->name('start');
            Route::get('batch/{id}', [App\Http\Controllers\Hotel\NightAuditController::class, 'showBatch'])->name('batch');
            Route::post('batch/{id}/post-room-charges', [App\Http\Controllers\Hotel\NightAuditController::class, 'postRoomCharges'])->name('post-room-charges');
            Route::post('batch/{id}/post-fb-revenue', [App\Http\Controllers\Hotel\NightAuditController::class, 'postFBRevenue'])->name('post-fb-revenue');
            Route::post('batch/{id}/post-minibar', [App\Http\Controllers\Hotel\NightAuditController::class, 'postMinibarCharges'])->name('post-minibar');
            Route::post('batch/{id}/calculate-occupancy', [App\Http\Controllers\Hotel\NightAuditController::class, 'calculateOccupancy'])->name('calculate-occupancy');
            Route::post('batch/{id}/complete', [App\Http\Controllers\Hotel\NightAuditController::class, 'completeAudit'])->name('complete');

            // Revenue Postings
            Route::get('revenue-postings', [App\Http\Controllers\Hotel\NightAuditController::class, 'revenuePostings'])->name('revenue-postings');
            Route::post('revenue-postings/{id}/void', [App\Http\Controllers\Hotel\NightAuditController::class, 'voidPosting'])->name('void-posting');

            // Statistics & Reports
            Route::get('statistics', [App\Http\Controllers\Hotel\NightAuditController::class, 'statistics'])->name('statistics');
            Route::post('recalculate-rates', [App\Http\Controllers\Hotel\NightAuditController::class, 'recalculateRates'])->name('recalculate-rates');
        });

        // Channel Manager
        Route::get('channels', [App\Http\Controllers\Hotel\ChannelManagerController::class, 'index'])->name('channels.index');
        Route::get('channels/logs', [App\Http\Controllers\Hotel\ChannelManagerController::class, 'logs'])->name('channels.logs');
        Route::get('channels/{channel}/configure', [App\Http\Controllers\Hotel\ChannelManagerController::class, 'configure'])->name('channels.configure');
        Route::put('channels/{channel}/configure', [App\Http\Controllers\Hotel\ChannelManagerController::class, 'updateConfig'])->name('channels.update-config');
        Route::post('channels/{channel}/sync', [App\Http\Controllers\Hotel\ChannelManagerController::class, 'sync'])->name('channels.sync');
    });
});

require __DIR__ . '/auth.php';

// ── Mobile (Mode Lapangan) ─────────────────────────────────────────────────
use App\Http\Controllers\MobileController;
use App\Http\Controllers\Telecom\DeviceController as TelecomDeviceController;
use App\Http\Controllers\Telecom\PackageController as TelecomPackageController;
use App\Http\Controllers\Telecom\SubscriptionController as TelecomSubscriptionController;
use App\Http\Controllers\Telecom\DashboardController as TelecomDashboardController;
use App\Http\Controllers\Telecom\CustomerController as TelecomCustomerController;
use App\Http\Controllers\Telecom\VoucherController as TelecomVoucherController;
use App\Http\Controllers\Telecom\ReportsController as TelecomReportsController;

Route::prefix('mobile')->name('mobile.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [MobileController::class, 'hub'])->name('hub');
    Route::get('/picking', [MobileController::class, 'picking'])->name('picking');
    Route::get('/picking/{id}', [MobileController::class, 'pickingShow'])->name('picking.show');
    Route::post('/picking/{id}/confirm', [MobileController::class, 'pickingConfirm'])->name('picking.confirm');
    Route::post('/picking/{id}/batch-confirm', [MobileController::class, 'pickingBatchConfirm'])->name('picking.batch-confirm');
    Route::get('/opname', [MobileController::class, 'opname'])->name('opname');
    Route::get('/opname/{id}', [MobileController::class, 'opnameShow'])->name('opname.show');
    Route::post('/opname/{id}/update', [MobileController::class, 'opnameUpdate'])->name('opname.update');
    Route::post('/opname/{id}/batch-update', [MobileController::class, 'opnameBatchUpdate'])->name('opname.batch-update');
    Route::patch('/opname/{id}/complete', [MobileController::class, 'opnameComplete'])->name('opname.complete');
    Route::get('/farm-activity', [MobileController::class, 'farmActivity'])->name('farm-activity');
    Route::post('/farm-activity', [MobileController::class, 'farmActivityStore'])->name('farm-activity.store');
    Route::get('/transfer', [MobileController::class, 'transfer'])->name('transfer');
    Route::post('/transfer', [MobileController::class, 'transferStore'])->name('transfer.store');
});

// Bot Webhooks (no auth, verified by platform token)
Route::post('/webhook/telegram', [BotController::class, 'telegramWebhook'])->name('webhook.telegram')->middleware('throttle:webhook-inbound');
Route::get('/webhook/whatsapp', [BotController::class, 'whatsappWebhook'])->name('webhook.whatsapp.verify');
Route::post('/webhook/whatsapp', [BotController::class, 'whatsappWebhook'])->name('webhook.whatsapp')->middleware('throttle:webhook-inbound');

// Payment Gateway Webhooks (no auth, verified by signature middleware)
Route::post('/webhook/midtrans', [\App\Http\Controllers\PaymentGatewayController::class, 'midtransWebhook'])
    ->name('webhook.midtrans')
    ->middleware(['webhook.verify:midtrans', 'throttle:webhook-inbound']);
Route::post('/webhook/xendit', [\App\Http\Controllers\PaymentGatewayController::class, 'xenditWebhook'])
    ->name('webhook.xendit')
    ->middleware(['webhook.verify:xendit', 'throttle:webhook-inbound']);

// ── Telecom Module - Device Management ──────────────────────────────────────
Route::prefix('telecom')->name('telecom.')->middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [TelecomDashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/device-status', [TelecomDashboardController::class, 'getDeviceStatus'])->name('dashboard.device-status');
    Route::get('dashboard/bandwidth-data', [TelecomDashboardController::class, 'getBandwidthData'])->name('dashboard.bandwidth-data');

    // Customer Portal
    Route::get('customers/usage', [TelecomCustomerController::class, 'usage'])->name('customers.usage');
    Route::get('customers/{customer}/usage', [TelecomCustomerController::class, 'showUsage'])->name('customers.show-usage');
    Route::post('customers/{customer}/reset-quota', [TelecomCustomerController::class, 'resetQuota'])->name('customers.reset-quota');
    Route::post('customers/{customer}/suspend', [TelecomCustomerController::class, 'suspendSubscription'])->name('customers.suspend');
    Route::post('customers/{customer}/reactivate', [TelecomCustomerController::class, 'reactivateSubscription'])->name('customers.reactivate');

    // Vouchers
    Route::get('vouchers', [TelecomVoucherController::class, 'index'])->name('vouchers.index');
    Route::get('vouchers/create', [TelecomVoucherController::class, 'create'])->name('vouchers.create');
    Route::post('vouchers', [TelecomVoucherController::class, 'store'])->name('vouchers.store');
    Route::get('vouchers/print', [TelecomVoucherController::class, 'print'])->name('vouchers.print');
    Route::get('vouchers/stats', [TelecomVoucherController::class, 'stats'])->name('vouchers.stats');
    Route::post('vouchers/{voucher}/revoke', [TelecomVoucherController::class, 'revoke'])->name('vouchers.revoke');
    Route::post('vouchers/{voucher}/extend', [TelecomVoucherController::class, 'extendValidity'])->name('vouchers.extend');

    // Reports & Analytics
    Route::get('reports', [TelecomReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/revenue-by-package', [TelecomReportsController::class, 'revenueByPackage'])->name('reports.revenue-by-package');
    Route::get('reports/bandwidth-utilization', [TelecomReportsController::class, 'bandwidthUtilization'])->name('reports.bandwidth-utilization');
    Route::get('reports/customer-usage-analytics', [TelecomReportsController::class, 'customerUsageAnalytics'])->name('reports.customer-usage-analytics');
    Route::get('reports/top-consumers', [TelecomReportsController::class, 'topConsumers'])->name('reports.top-consumers');

    // Devices
    Route::resource('devices', TelecomDeviceController::class)->except(['create', 'edit']);
    Route::get('devices/create', [TelecomDeviceController::class, 'create'])->name('devices.create');
    Route::get('devices/{device}/edit', [TelecomDeviceController::class, 'edit'])->name('devices.edit');
    Route::post('devices/{device}/test-connection', [TelecomDeviceController::class, 'testConnection'])->name('devices.test-connection');
    Route::post('devices/{device}/toggle-maintenance', [TelecomDeviceController::class, 'toggleMaintenance'])->name('devices.toggle-maintenance');

    // Packages
    Route::resource('packages', TelecomPackageController::class)->except(['create', 'edit']);
    Route::get('packages/create', [TelecomPackageController::class, 'create'])->name('packages.create');
    Route::get('packages/{package}/edit', [TelecomPackageController::class, 'edit'])->name('packages.edit');
    Route::post('packages/{package}/toggle-status', [TelecomPackageController::class, 'toggleStatus'])->name('packages.toggle-status');

    // Subscriptions
    Route::resource('subscriptions', TelecomSubscriptionController::class)->except(['create', 'edit']);
    Route::get('subscriptions/create', [TelecomSubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::get('subscriptions/{subscription}/edit', [TelecomSubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::post('subscriptions/{subscription}/suspend', [TelecomSubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
    Route::post('subscriptions/{subscription}/reactivate', [TelecomSubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
    Route::post('subscriptions/{subscription}/reset-quota', [TelecomSubscriptionController::class, 'resetQuota'])->name('subscriptions.reset-quota');
});
