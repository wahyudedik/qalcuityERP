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
use App\Http\Controllers\PosController;
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
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

Route::get('/offline', fn() => response()->file(public_path('offline.html')))->name('offline');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Onboarding wizard
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
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
        Route::post('/send', [ChatController::class, 'send'])->name('send');
        Route::post('/send-media', [ChatController::class, 'sendMedia'])->name('send-media');
        Route::get('/{session}/messages', [ChatController::class, 'messages'])->name('messages');
        Route::delete('/{session}', [ChatController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
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
    });

    // Subscription info (tenant only)
    Route::get('/subscription', [SubscriptionController::class, 'index'])
        ->name('subscription.index')
        ->withoutMiddleware(\App\Http\Middleware\CheckTenantActive::class);

    // Subscription expired page (tidak perlu auth, tapi perlu session)
    Route::get('/subscription/expired', fn() => view('subscription.expired'))->name('subscription.expired')->withoutMiddleware(\App\Http\Middleware\CheckTenantActive::class);

    // Reports & Export (admin + manager only)
    Route::prefix('reports')->name('reports.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales/excel',     [ReportController::class, 'exportSalesExcel'])->name('sales.excel');
        Route::get('/sales/pdf',       [ReportController::class, 'exportSalesPdf'])->name('sales.pdf');
        Route::get('/finance/excel',   [ReportController::class, 'exportFinanceExcel'])->name('finance.excel');
        Route::get('/finance/pdf',     [ReportController::class, 'exportFinancePdf'])->name('finance.pdf');
        Route::get('/inventory/excel', [ReportController::class, 'exportInventoryExcel'])->name('inventory.excel');
        Route::get('/inventory/pdf',   [ReportController::class, 'exportInventoryPdf'])->name('inventory.pdf');
        Route::get('/hrm/excel',       [ReportController::class, 'exportHrmExcel'])->name('hrm.excel');
        Route::get('/hrm/pdf',         [ReportController::class, 'exportHrmPdf'])->name('hrm.pdf');
        Route::get('/receivables/excel',[ReportController::class, 'exportReceivablesExcel'])->name('receivables.excel');
        Route::get('/receivables/pdf', [ReportController::class, 'exportReceivablesPdf'])->name('receivables.pdf');
        Route::get('/profit-loss/pdf', [ReportController::class, 'exportProfitLossPdf'])->name('profit-loss.pdf');
    });

    // POS Kasir
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/barcode', [PosController::class, 'findByBarcode'])->name('barcode');
    });

    // Approval Workflow
    Route::prefix('approvals')->name('approvals.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{approval}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approval}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });

    // Audit Trail
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index')->middleware('role:admin');

    // Bank Reconciliation (admin + manager only)
    Route::prefix('bank')->name('bank.')->middleware('role:admin,manager')->group(function () {
        Route::get('/reconciliation', [BankReconciliationController::class, 'index'])->name('reconciliation');
        Route::post('/import', [BankReconciliationController::class, 'import'])->name('import');
        Route::post('/statements/{statement}/match', [BankReconciliationController::class, 'match'])->name('match');
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

    // E-commerce (admin + manager only)
    Route::prefix('ecommerce')->name('ecommerce.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [EcommerceController::class, 'index'])->name('index');
        Route::post('/channels', [EcommerceController::class, 'storeChannel'])->name('channels.store');
        Route::post('/channels/{channel}/sync', [EcommerceController::class, 'sync'])->name('channels.sync');
    });

    // Inventory (semua role bisa lihat, tapi write hanya admin+manager)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/warehouses', [InventoryController::class, 'warehouses'])->name('warehouses');
        Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');

        // Write operations: admin + manager only
        Route::middleware('role:admin,manager')->group(function () {
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/stock', [InventoryController::class, 'addStock'])->name('add-stock');
            Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->name('warehouses.store');
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
    });

    // Purchasing (admin + manager only)
    Route::prefix('purchasing')->name('purchasing.')->middleware('role:admin,manager')->group(function () {
        Route::get('/suppliers', [PurchasingController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [PurchasingController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [PurchasingController::class, 'updateSupplier'])->name('suppliers.update');
        Route::get('/orders', [PurchasingController::class, 'orders'])->name('orders');
        Route::post('/orders', [PurchasingController::class, 'storeOrder'])->name('orders.store');
        Route::patch('/orders/{order}/status', [PurchasingController::class, 'updateOrderStatus'])->name('orders.status');
    });

    // CRM (admin + manager only)
    Route::prefix('crm')->name('crm.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [CrmController::class, 'index'])->name('index');
        Route::post('/', [CrmController::class, 'store'])->name('store');
        Route::patch('/{lead}/stage', [CrmController::class, 'updateStage'])->name('stage');
        Route::post('/{lead}/activity', [CrmController::class, 'logActivity'])->name('activity');
        Route::delete('/{lead}', [CrmController::class, 'destroy'])->name('destroy');
    });

    // Payroll
    Route::prefix('payroll')->name('payroll.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/process', [PayrollController::class, 'process'])->name('process');
        Route::patch('/{run}/paid', [PayrollController::class, 'markPaid'])->name('paid');
    });

    // Assets
    Route::prefix('assets')->name('assets.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::post('/depreciate', [AssetController::class, 'depreciate'])->name('depreciate');
        Route::get('/maintenance', [AssetController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance', [AssetController::class, 'storeMaintenance'])->name('maintenance.store');
        Route::patch('/maintenance/{maintenance}/status', [AssetController::class, 'updateMaintenanceStatus'])->name('maintenance.status');
    });

    // Import CSV (admin + manager only)
    Route::prefix('import')->name('import.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/products', [ImportController::class, 'importProducts'])->name('products');
        Route::post('/employees', [ImportController::class, 'importEmployees'])->name('employees');
        Route::post('/customers', [ImportController::class, 'importCustomers'])->name('customers');
        Route::get('/template/{type}', [ImportController::class, 'downloadTemplate'])->name('template');
    });

    // Payment Gateway
    Route::prefix('payment')->name('payment.')->middleware('role:admin')->group(function () {
        Route::post('/midtrans/checkout', [\App\Http\Controllers\PaymentGatewayController::class, 'midtransCheckout'])->name('midtrans.checkout');
        Route::get('/midtrans/finish', [\App\Http\Controllers\PaymentGatewayController::class, 'midtransFinish'])->name('midtrans.finish');
        Route::post('/xendit/checkout', [\App\Http\Controllers\PaymentGatewayController::class, 'xenditCheckout'])->name('xendit.checkout');
        Route::get('/xendit/finish', [\App\Http\Controllers\PaymentGatewayController::class, 'xenditFinish'])->name('xendit.finish');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('payment');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
        Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
    });
});

require __DIR__.'/auth.php';

// Bot Webhooks (no auth, verified by platform token)
Route::post('/webhook/telegram', [BotController::class, 'telegramWebhook'])->name('webhook.telegram');
Route::get('/webhook/whatsapp', [BotController::class, 'whatsappWebhook'])->name('webhook.whatsapp.verify');
Route::post('/webhook/whatsapp', [BotController::class, 'whatsappWebhook'])->name('webhook.whatsapp');

// Payment Gateway Webhooks (no auth)
Route::post('/webhook/midtrans', [\App\Http\Controllers\PaymentGatewayController::class, 'midtransWebhook'])->name('webhook.midtrans');
Route::post('/webhook/xendit', [\App\Http\Controllers\PaymentGatewayController::class, 'xenditWebhook'])->name('webhook.xendit');
