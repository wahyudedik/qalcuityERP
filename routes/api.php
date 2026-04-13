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

// ── Offline Sync endpoints (authenticated - Sanctum or Web Session) ──
// Note: 'web' middleware dibutuhkan agar session cookie bisa dibaca dari browser
Route::middleware(['web', 'auth', 'api.rate:api-write'])->prefix('offline')->group(function () {
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

use App\Http\Controllers\Api\HealthcareApiController;
use App\Http\Controllers\Api\HotelApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\HrmApiController;
use App\Http\Controllers\Api\ManufacturingApiController;
use App\Http\Controllers\Api\AgricultureApiController;
use App\Http\Controllers\Api\FisheriesApiController;
use App\Http\Controllers\Api\LivestockApiController;
use App\Http\Controllers\Api\CosmeticsApiController;
use App\Http\Controllers\Api\TourTravelApiController;

// ── Health Check endpoints ───────────────────────────────────────
// /health dan /live: public (dipakai load balancer & uptime monitor)
// /detailed dan /ready: dilindungi auth — mengekspos info sensitif (DB version, Redis, queue)
Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'health']);
    Route::get('/live', [HealthCheckController::class, 'live']);
});

Route::prefix('health')->middleware('auth:sanctum')->group(function () {
    Route::get('/detailed', [HealthCheckController::class, 'detailed']);
    Route::get('/ready', [HealthCheckController::class, 'ready']);
});

// ── Healthcare Module API Endpoints ──────────────────────────────────────
Route::prefix('healthcare')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Patients
    Route::get('/patients', [HealthcareApiController::class, 'patients']);
    Route::get('/patients/{id}', [HealthcareApiController::class, 'patient']);
    Route::post('/patients', [HealthcareApiController::class, 'createPatient'])->middleware('api.rate:api-write');
    Route::put('/patients/{id}', [HealthcareApiController::class, 'updatePatient'])->middleware('api.rate:api-write');
    Route::delete('/patients/{id}', [HealthcareApiController::class, 'deletePatient'])->middleware('api.rate:api-write');

    // Doctors
    Route::get('/doctors', [HealthcareApiController::class, 'doctors']);
    Route::get('/doctors/{id}', [HealthcareApiController::class, 'doctor']);

    // Appointments
    Route::get('/appointments', [HealthcareApiController::class, 'appointments']);
    Route::get('/appointments/{id}', [HealthcareApiController::class, 'appointment']);
    Route::post('/appointments', [HealthcareApiController::class, 'createAppointment'])->middleware('api.rate:api-write');
    Route::patch('/appointments/{id}/status', [HealthcareApiController::class, 'updateAppointmentStatus'])->middleware('api.rate:api-write');

    // Lab Results
    Route::get('/lab-results', [HealthcareApiController::class, 'labResults']);
    Route::get('/lab-results/{id}', [HealthcareApiController::class, 'labResult']);
    Route::post('/lab-results', [HealthcareApiController::class, 'createLabResult'])->middleware('api.rate:api-write');
    Route::post('/lab-results/{id}/approve', [HealthcareApiController::class, 'approveLabResult'])->middleware('api.rate:api-write');

    // Lab Orders & Equipment
    Route::get('/lab-orders/{id}/results', [HealthcareApiController::class, 'getLabOrderResults']);
    Route::get('/lab-equipment/calibration-due', [HealthcareApiController::class, 'getLabEquipmentCalibrationDue']);
    Route::post('/lab-samples/{id}/process', [HealthcareApiController::class, 'processLabSample'])->middleware('api.rate:api-write');

    // Prescriptions
    Route::get('/prescriptions', [HealthcareApiController::class, 'prescriptions']);
    Route::get('/prescriptions/{id}', [HealthcareApiController::class, 'prescription']);
    Route::post('/prescriptions', [HealthcareApiController::class, 'createPrescription'])->middleware('api.rate:api-write');
    Route::post('/prescriptions/{id}/dispense', [HealthcareApiController::class, 'dispensePrescription'])->middleware('api.rate:api-write');

    // EMR (Electronic Medical Records)
    Route::get('/emr/{patientId}', [HealthcareApiController::class, 'getEmr']);
    Route::post('/emr', [HealthcareApiController::class, 'createEmr'])->middleware('api.rate:api-write');

    // Admissions
    Route::get('/admissions', [HealthcareApiController::class, 'admissions']);
    Route::post('/admissions', [HealthcareApiController::class, 'createAdmission'])->middleware('api.rate:api-write');
    Route::post('/admissions/{id}/transfer-ward', [HealthcareApiController::class, 'transferWard'])->middleware('api.rate:api-write');
    Route::post('/admissions/{id}/discharge', [HealthcareApiController::class, 'dischargeAdmission'])->middleware('api.rate:api-write');

    // Beds
    Route::get('/beds/availability', [HealthcareApiController::class, 'getBedAvailability']);
    Route::get('/beds/{id}', [HealthcareApiController::class, 'bedDetail']);

    // Radiology APIs
    Route::get('/radiology-exams/{id}/images', [HealthcareApiController::class, 'getRadiologyExamImages']);
    Route::get('/pacs/studies', [HealthcareApiController::class, 'getPacsStudies']);
    Route::post('/radiology-reports/{id}/finalize', [HealthcareApiController::class, 'finalizeRadiologyReport'])->middleware('api.rate:api-write');

    // Surgery APIs
    Route::get('/operating-rooms/availability', [HealthcareApiController::class, 'getOperatingRoomsAvailability']);
    Route::post('/surgery-schedules/{id}/assign-team', [HealthcareApiController::class, 'assignSurgeryTeam'])->middleware('api.rate:api-write');
    Route::post('/surgery-schedules/{id}/complete', [HealthcareApiController::class, 'completeSurgery'])->middleware('api.rate:api-write');

    // Pharmacy APIs
    Route::get('/medications/expiring', [HealthcareApiController::class, 'getExpiringMedications']);
    Route::post('/pharmacy/stock-opname', [HealthcareApiController::class, 'createPharmacyStockOpname'])->middleware('api.rate:api-write');
});

// ── Hotel Module API Endpoints ──────────────────────────────────────
Route::prefix('hotel')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Rooms
    Route::get('/rooms', [HotelApiController::class, 'rooms']);
    Route::get('/rooms/{id}', [HotelApiController::class, 'room']);
    Route::post('/rooms', [HotelApiController::class, 'createRoom'])->middleware('api.rate:api-write');
    Route::put('/rooms/{id}', [HotelApiController::class, 'updateRoom'])->middleware('api.rate:api-write');

    // Reservations
    Route::get('/reservations', [HotelApiController::class, 'reservations']);
    Route::get('/reservations/{id}', [HotelApiController::class, 'reservation']);
    Route::post('/reservations', [HotelApiController::class, 'createReservation'])->middleware('api.rate:api-write');
    Route::patch('/reservations/{id}/status', [HotelApiController::class, 'updateReservationStatus'])->middleware('api.rate:api-write');
    Route::delete('/reservations/{id}', [HotelApiController::class, 'cancelReservation'])->middleware('api.rate:api-write');

    // Room Types
    Route::get('/room-types', [HotelApiController::class, 'roomTypes']);
    Route::post('/room-types', [HotelApiController::class, 'createRoomType'])->middleware('api.rate:api-write');

    // Guests
    Route::get('/guests', [HotelApiController::class, 'guests']);
    Route::get('/guests/{id}', [HotelApiController::class, 'guest']);

    // Billing
    Route::get('/billing/{reservationId}', [HotelApiController::class, 'getBilling']);
    Route::post('/billing/{reservationId}/charge', [HotelApiController::class, 'addCharge'])->middleware('api.rate:api-write');

    // Housekeeping
    Route::get('/housekeeping/status', [HotelApiController::class, 'housekeepingStatus']);
    Route::patch('/housekeeping/{roomId}/status', [HotelApiController::class, 'updateHousekeepingStatus'])->middleware('api.rate:api-write');
});

// ── Inventory Module API Endpoints ──────────────────────────────────────
Route::prefix('inventory')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Stock Levels
    Route::get('/stock', [InventoryApiController::class, 'stockLevels']);
    Route::get('/stock/{productId}', [InventoryApiController::class, 'stockDetail']);

    // Stock Movements
    Route::get('/movements', [InventoryApiController::class, 'movements']);
    Route::post('/movements', [InventoryApiController::class, 'recordMovement'])->middleware('api.rate:api-write');

    // Stock Adjustments
    Route::get('/adjustments', [InventoryApiController::class, 'adjustments']);
    Route::post('/adjustments', [InventoryApiController::class, 'createAdjustment'])->middleware('api.rate:api-write');

    // Stock Transfers
    Route::get('/transfers', [InventoryApiController::class, 'transfers']);
    Route::post('/transfers', [InventoryApiController::class, 'createTransfer'])->middleware('api.rate:api-write');
    Route::patch('/transfers/{id}/status', [InventoryApiController::class, 'updateTransferStatus'])->middleware('api.rate:api-write');

    // Inventory Valuation
    Route::get('/valuation', [InventoryApiController::class, 'valuation']);

    // Low Stock Alerts
    Route::get('/low-stock', [InventoryApiController::class, 'lowStockAlerts']);

    // Stock Count/Opname
    Route::post('/stock-count', [InventoryApiController::class, 'recordStockCount'])->middleware('api.rate:api-write');
    Route::get('/stock-count/history', [InventoryApiController::class, 'stockCountHistory']);
});

// ── HRM Module API Endpoints ──────────────────────────────────────
Route::prefix('hrm')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Employees
    Route::get('/employees', [HrmApiController::class, 'employees']);
    Route::get('/employees/{id}', [HrmApiController::class, 'employee']);
    Route::post('/employees', [HrmApiController::class, 'createEmployee'])->middleware('api.rate:api-write');
    Route::put('/employees/{id}', [HrmApiController::class, 'updateEmployee'])->middleware('api.rate:api-write');

    // Attendance
    Route::get('/attendance', [HrmApiController::class, 'attendance']);
    Route::post('/attendance/check-in', [HrmApiController::class, 'checkIn'])->middleware('api.rate:api-write');
    Route::post('/attendance/check-out', [HrmApiController::class, 'checkOut'])->middleware('api.rate:api-write');

    // Leave Management
    Route::get('/leave/requests', [HrmApiController::class, 'leaveRequests']);
    Route::post('/leave/requests', [HrmApiController::class, 'requestLeave'])->middleware('api.rate:api-write');
    Route::patch('/leave/requests/{id}/status', [HrmApiController::class, 'updateLeaveStatus'])->middleware('api.rate:api-write');

    // Payroll
    Route::get('/payroll', [HrmApiController::class, 'payroll']);
    Route::post('/payroll/process', [HrmApiController::class, 'processPayroll'])->middleware('api.rate:api-write');
    Route::get('/payroll/{id}/slip', [HrmApiController::class, 'payrollSlip']);

    // Departments
    Route::get('/departments', [HrmApiController::class, 'departments']);

    // Employee Performance
    Route::get('/performance/{employeeId}', [HrmApiController::class, 'employeePerformance']);
});

// ── Manufacturing Module API Endpoints (Extended) ───────────────────────────
Route::prefix('manufacturing')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Work Orders (existing)
    Route::get('/work-orders', [ManufacturingApiController::class, 'workOrders']);
    Route::post('/work-orders', [ManufacturingApiController::class, 'createWorkOrder'])->middleware('api.rate:api-write');
    Route::get('/work-orders/{id}', [ManufacturingApiController::class, 'workOrderDetail']);
    Route::patch('/work-orders/{id}/status', [ManufacturingApiController::class, 'updateWorkOrderStatus'])->middleware('api.rate:api-write');

    // BOM
    Route::get('/boms', [ManufacturingApiController::class, 'boms']);
    Route::get('/boms/{id}', [ManufacturingApiController::class, 'bomDetail']);
    Route::post('/boms', [ManufacturingApiController::class, 'createBom'])->middleware('api.rate:api-write');

    // Mix Design
    Route::get('/mix-design', [ManufacturingApiController::class, 'mixDesigns']);
    Route::get('/mix-design/{id}', [ManufacturingApiController::class, 'mixDesignDetail']);
    Route::post('/mix-design/calculate', [ManufacturingApiController::class, 'calculateMixDesign'])->middleware('api.rate:api-write');

    // MRP
    Route::post('/mrp/calculate', [ManufacturingApiController::class, 'runMrp'])->middleware('api.rate:api-write');

    // Quality Checks
    Route::get('/quality-checks', [ManufacturingApiController::class, 'qualityChecks']);
    Route::get('/quality-checks/{id}', [ManufacturingApiController::class, 'qualityCheckDetail']);
    Route::post('/quality-checks', [ManufacturingApiController::class, 'createQualityCheck'])->middleware('api.rate:api-write');
    Route::post('/quality-checks/{id}/submit', [ManufacturingApiController::class, 'submitQualityCheck'])->middleware('api.rate:api-write');

    // Defects
    Route::get('/defects', [ManufacturingApiController::class, 'defects']);
    Route::get('/defects/{id}', [ManufacturingApiController::class, 'defectDetail']);
    Route::post('/defects', [ManufacturingApiController::class, 'recordDefect'])->middleware('api.rate:api-write');
    Route::patch('/defects/{id}/resolve', [ManufacturingApiController::class, 'resolveDefect'])->middleware('api.rate:api-write');

    // Production Output
    Route::get('/production/output', [ManufacturingApiController::class, 'productionOutput']);
    Route::post('/production/output', [ManufacturingApiController::class, 'recordProductionOutput'])->middleware('api.rate:api-write');
});

// ── Agriculture Module API Endpoints ──────────────────────────────────────
Route::prefix('agriculture')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Crops
    Route::get('/crops', [AgricultureApiController::class, 'crops']);
    Route::get('/crops/{id}', [AgricultureApiController::class, 'crop']);
    Route::post('/crops', [AgricultureApiController::class, 'createCrop'])->middleware('api.rate:api-write');

    // Harvest
    Route::get('/harvests', [AgricultureApiController::class, 'harvests']);
    Route::post('/harvests', [AgricultureApiController::class, 'recordHarvest'])->middleware('api.rate:api-write');

    // Land/Fields
    Route::get('/fields', [AgricultureApiController::class, 'fields']);
    Route::post('/fields', [AgricultureApiController::class, 'createField'])->middleware('api.rate:api-write');

    // Planting Cycles
    Route::get('/planting-cycles', [AgricultureApiController::class, 'plantingCycles']);
    Route::post('/planting-cycles', [AgricultureApiController::class, 'createPlantingCycle'])->middleware('api.rate:api-write');
});

// ── Fisheries Module API Endpoints ──────────────────────────────────────
Route::prefix('fisheries')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Ponds
    Route::get('/ponds', [FisheriesApiController::class, 'ponds']);
    Route::post('/ponds', [FisheriesApiController::class, 'createPond'])->middleware('api.rate:api-write');

    // Fish Stocks
    Route::get('/fish-stocks', [FisheriesApiController::class, 'fishStocks']);
    Route::post('/fish-stocks', [FisheriesApiController::class, 'stockFish'])->middleware('api.rate:api-write');

    // Harvest
    Route::get('/harvests', [FisheriesApiController::class, 'harvests']);
    Route::post('/harvests', [FisheriesApiController::class, 'recordHarvest'])->middleware('api.rate:api-write');

    // Water Quality
    Route::get('/water-quality', [FisheriesApiController::class, 'waterQuality']);
    Route::post('/water-quality', [FisheriesApiController::class, 'recordWaterQuality'])->middleware('api.rate:api-write');
});

// ── Livestock Module API Endpoints ──────────────────────────────────────
Route::prefix('livestock')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Animals
    Route::get('/animals', [LivestockApiController::class, 'animals']);
    Route::get('/animals/{id}', [LivestockApiController::class, 'animal']);
    Route::post('/animals', [LivestockApiController::class, 'createAnimal'])->middleware('api.rate:api-write');

    // Health Records
    Route::get('/health-records', [LivestockApiController::class, 'healthRecords']);
    Route::post('/health-records', [LivestockApiController::class, 'recordHealth'])->middleware('api.rate:api-write');

    // Breeding
    Route::get('/breeding', [LivestockApiController::class, 'breeding']);
    Route::post('/breeding', [LivestockApiController::class, 'recordBreeding'])->middleware('api.rate:api-write');
});

// ── Cosmetics Module API Endpoints ──────────────────────────────────────
Route::prefix('cosmetics')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Products/Formulations
    Route::get('/formulations', [CosmeticsApiController::class, 'formulations']);
    Route::get('/formulations/{id}', [CosmeticsApiController::class, 'formulation']);
    Route::post('/formulations', [CosmeticsApiController::class, 'createFormulation'])->middleware('api.rate:api-write');

    // BPOM Registration
    Route::get('/bpom-registrations', [CosmeticsApiController::class, 'bpomRegistrations']);
    Route::post('/bpom-registrations', [CosmeticsApiController::class, 'registerBpom'])->middleware('api.rate:api-write');

    // Batch Production
    Route::get('/batches', [CosmeticsApiController::class, 'batches']);
    Route::post('/batches', [CosmeticsApiController::class, 'createBatch'])->middleware('api.rate:api-write');
});

// ── Tour & Travel Module API Endpoints ──────────────────────────────────────
Route::prefix('tour-travel')->middleware(['auth:sanctum', 'api.rate:api-read'])->group(function () {
    // Tour Packages
    Route::get('/packages', [TourTravelApiController::class, 'packages']);
    Route::get('/packages/{id}', [TourTravelApiController::class, 'package']);
    Route::post('/packages', [TourTravelApiController::class, 'createPackage'])->middleware('api.rate:api-write');

    // Bookings
    Route::get('/bookings', [TourTravelApiController::class, 'bookings']);
    Route::get('/bookings/{id}', [TourTravelApiController::class, 'booking']);
    Route::post('/bookings', [TourTravelApiController::class, 'createBooking'])->middleware('api.rate:api-write');
    Route::patch('/bookings/{id}/status', [TourTravelApiController::class, 'updateBookingStatus'])->middleware('api.rate:api-write');

    // Itineraries
    Route::get('/itineraries', [TourTravelApiController::class, 'itineraries']);
    Route::post('/itineraries', [TourTravelApiController::class, 'createItinerary'])->middleware('api.rate:api-write');

    // Vehicles
    Route::get('/vehicles', [TourTravelApiController::class, 'vehicles']);
    Route::post('/vehicles', [TourTravelApiController::class, 'createVehicle'])->middleware('api.rate:api-write');
});


