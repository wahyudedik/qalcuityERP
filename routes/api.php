<?php

use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiOrderController;
use App\Http\Controllers\Api\ApiInvoiceController;
use App\Http\Controllers\Api\ApiCustomerController;
use App\Http\Controllers\Api\ApiStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Qalcuity ERP — REST API v1
|--------------------------------------------------------------------------
| Auth: Bearer token or X-API-Token header
| Base URL: /api/v1
*/

Route::prefix('v1')->group(function () {

    // ── Read-only endpoints ──────────────────────────────────────
    Route::middleware('api.token:read')->group(function () {
        Route::get('/stats',              [ApiStatsController::class,   'summary']);
        Route::get('/products',           [ApiProductController::class, 'index']);
        Route::get('/products/{id}',      [ApiProductController::class, 'show']);
        Route::get('/orders',             [ApiOrderController::class,   'index']);
        Route::get('/orders/{id}',        [ApiOrderController::class,   'show']);
        Route::get('/invoices',           [ApiInvoiceController::class, 'index']);
        Route::get('/invoices/{id}',      [ApiInvoiceController::class, 'show']);
        Route::get('/customers',          [ApiCustomerController::class,'index']);
        Route::get('/customers/{id}',     [ApiCustomerController::class,'show']);
    });

    // ── Write endpoints ──────────────────────────────────────────
    Route::middleware('api.token:write')->group(function () {
        Route::post('/orders',                    [ApiOrderController::class,   'store']);
        Route::patch('/orders/{id}/status',       [ApiOrderController::class,   'updateStatus']);
        Route::post('/customers',                 [ApiCustomerController::class,'store']);
        Route::put('/customers/{id}',             [ApiCustomerController::class,'update']);
    });
});
