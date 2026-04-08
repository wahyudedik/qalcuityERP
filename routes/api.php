<?php

use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiOrderController;
use App\Http\Controllers\Api\ApiInvoiceController;
use App\Http\Controllers\Api\ApiCustomerController;
use App\Http\Controllers\Api\ApiStatsController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\MarketplaceWebhookController;
use App\Http\Controllers\Pos\PrintController;
use App\Http\Controllers\Api\Telecom\DeviceController;
use App\Http\Controllers\Api\Telecom\HotspotUserController;
use App\Http\Controllers\Api\Telecom\UsageController;
use App\Http\Controllers\Api\Telecom\VoucherController;
use App\Http\Controllers\Api\Telecom\WebhookController;
use App\Http\Controllers\OfflineSyncController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Qalcuity ERP — REST API v1
|--------------------------------------------------------------------------
| Auth: Bearer token or X-API-Token header
| Base URL: /api/v1
*/

Route::prefix('v1')->group(function () {

    // ── Read-only endpoints (60 req/min base, scaled by plan) ────
    Route::middleware(['api.token:read', 'api.rate:api-read'])->group(function () {
        Route::get('/stats', [ApiStatsController::class, 'summary']);
        Route::get('/products', [ApiProductController::class, 'index']);
        Route::get('/products/{id}', [ApiProductController::class, 'show']);
        Route::get('/orders', [ApiOrderController::class, 'index']);
        Route::get('/orders/{id}', [ApiOrderController::class, 'show']);
        Route::get('/invoices', [ApiInvoiceController::class, 'index']);
        Route::get('/invoices/{id}', [ApiInvoiceController::class, 'show']);
        Route::get('/customers', [ApiCustomerController::class, 'index']);
        Route::get('/customers/{id}', [ApiCustomerController::class, 'show']);
    });

    // ── Write endpoints (20 req/min base, scaled by plan) ────────
    Route::middleware(['api.token:write', 'api.rate:api-write'])->group(function () {
        Route::post('/orders', [ApiOrderController::class, 'store']);
        Route::patch('/orders/{id}/status', [ApiOrderController::class, 'updateStatus']);
        Route::post('/customers', [ApiCustomerController::class, 'store']);
        Route::put('/customers/{id}', [ApiCustomerController::class, 'update']);
    });
});

// ── Marketplace webhook endpoints (NO auth — verified by HMAC signature) ──
Route::prefix('webhooks')->middleware('api.rate:webhook-inbound')->group(function () {
    Route::post('/shopee', [MarketplaceWebhookController::class, 'handleShopee']);
    Route::post('/tokopedia', [MarketplaceWebhookController::class, 'handleTokopedia']);
    Route::post('/lazada', [MarketplaceWebhookController::class, 'handleLazada']);
    // Fingerprint device webhooks
    Route::post('/fingerprint/attendance', [\App\Http\Controllers\Api\FingerprintWebhookController::class, 'handleAttendance']);
    Route::post('/fingerprint/heartbeat', [\App\Http\Controllers\Api\FingerprintWebhookController::class, 'heartbeat']);
    Route::get('/fingerprint/pending-registrations', [\App\Http\Controllers\Api\FingerprintWebhookController::class, 'getPendingRegistrations']);
});

// ── Telecom Module API Endpoints ──────────────────────────────────────
Route::prefix('telecom')->middleware(['auth:sanctum', 'api.rate:api-write'])->group(function () {

    // Device Management
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices', [DeviceController::class, 'store']);
    Route::get('/devices/{device}/status', [DeviceController::class, 'status']);

    // Hotspot User Management
    Route::post('/hotspot/users', [HotspotUserController::class, 'store']);
    Route::get('/hotspot/users/{user}/stats', [HotspotUserController::class, 'stats']);
    Route::post('/hotspot/users/{user}/suspend', [HotspotUserController::class, 'suspend']);
    Route::post('/hotspot/users/{user}/reactivate', [HotspotUserController::class, 'reactivate']);

    // Usage Tracking
    Route::get('/usage/{customerId}', [UsageController::class, 'index']);
    Route::post('/usage/record', [UsageController::class, 'record']);

    // Voucher Management
    Route::post('/vouchers/generate', [VoucherController::class, 'generate']);
    Route::post('/vouchers/redeem', [VoucherController::class, 'redeem']);
    Route::get('/vouchers/stats', [VoucherController::class, 'stats']);
});

// ── Telecom Webhook Endpoints (NO auth — verified by signature) ──
Route::prefix('telecom/webhook')->middleware('api.rate:webhook-inbound')->group(function () {
    Route::post('/router-usage', [WebhookController::class, 'routerUsage']);
    Route::post('/device-alert', [WebhookController::class, 'deviceAlert']);
});

// ── POS Print endpoints ──
Route::prefix('pos/print')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/receipt/{order}', [PrintController::class, 'printReceipt']);
    Route::post('/kitchen/{order}', [PrintController::class, 'printKitchenTicket']);
    Route::post('/barcode', [PrintController::class, 'printBarcodeLabel']);
    Route::post('/test', [PrintController::class, 'testPrinter']);
    Route::get('/queue', [PrintController::class, 'getPrintQueue']);
    Route::post('/queue/{job}/retry', [PrintController::class, 'retryPrintJob']);
    Route::post('/queue/{job}/cancel', [PrintController::class, 'cancelPrintJob']);
    Route::get('/settings', [PrintController::class, 'getPrinterSettings']);
    Route::post('/settings', [PrintController::class, 'savePrinterSettings']);
});

// ── Payment Gateway endpoints (Tenant self-configured) ──
Route::prefix('payment')->group(function () {
    // Webhook endpoints (NO auth - verified by signature)
    Route::post('/webhook/{provider}', [PaymentController::class, 'webhook'])->name('payment.webhook');

    // Authenticated endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/qris/{order}', [PaymentController::class, 'generateQris']);
        Route::get('/status', [PaymentController::class, 'checkStatus']);
        Route::get('/transaction/{transactionNumber}', [PaymentController::class, 'getTransaction']);
        Route::get('/history', [PaymentController::class, 'getHistory']);

        // Gateway configuration (tenant self-setup)
        Route::get('/gateways', [PaymentController::class, 'getGatewaySettings']);
        Route::post('/gateways', [PaymentController::class, 'saveGatewaySettings']);
        Route::post('/gateways/test', [PaymentController::class, 'testGateway']);
        Route::post('/gateways/{gateway}/toggle', [PaymentController::class, 'toggleGateway']);
        Route::delete('/gateways/{gateway}', [PaymentController::class, 'deleteGateway']);

        // Webhook testing & monitoring
        Route::prefix('webhook-test')->group(function () {
            Route::post('/midtrans', [\App\Http\Controllers\Api\WebhookTestController::class, 'testMidtrans']);
            Route::post('/xendit', [\App\Http\Controllers\Api\WebhookTestController::class, 'testXendit']);
            Route::get('/history', [\App\Http\Controllers\Api\WebhookTestController::class, 'getWebhookHistory']);
            Route::post('/retry-failed', [\App\Http\Controllers\Api\WebhookTestController::class, 'retryFailedWebhooks']);
            Route::get('/stats', [\App\Http\Controllers\Api\WebhookTestController::class, 'getWebhookStats']);
        });
    });
});

// ── Offline Sync endpoints (authenticated) ──────────────────────
Route::middleware(['auth:sanctum', 'api.rate:api-write'])->prefix('offline')->group(function () {
    Route::get('/status', [OfflineSyncController::class, 'getStatus']);
    Route::post('/sync', [OfflineSyncController::class, 'bulkSync']);
    Route::delete('/failed', [OfflineSyncController::class, 'clearFailed']);
    Route::get('/cache/{key}', [OfflineSyncController::class, 'getCache']);
    Route::post('/cache/{key}', [OfflineSyncController::class, 'updateCache']);

    // BUG-OFF-001 FIX: Conflict resolution endpoints
    Route::get('/conflicts', [OfflineSyncController::class, 'getConflicts']);
    Route::post('/conflicts/{id}/resolve', [OfflineSyncController::class, 'resolveConflict']);
    Route::post('/conflicts/auto-resolve', [OfflineSyncController::class, 'autoResolveAll']);
});

// ── CSRF Token Refresh (authenticated, for offline sync) ────────
// BUG-OFF-002 FIX: Endpoint to get fresh CSRF token
Route::middleware(['auth:sanctum'])->get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
    ]);
});

// ── Health Check endpoints (public, no auth required) ───────────
Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'health']);
    Route::get('/detailed', [HealthCheckController::class, 'detailed']);
    Route::get('/ready', [HealthCheckController::class, 'ready']);
    Route::get('/live', [HealthCheckController::class, 'live']);
});
