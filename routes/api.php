<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ---- Public auth ----
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {

        // ---- Auth ----
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // ---- Shifts (cashier + manager + admin) ----
        Route::prefix('shifts')->group(function () {
            Route::post('/open', [ShiftController::class, 'open'])->middleware('role:admin,manager,cashier');
            Route::post('/close', [ShiftController::class, 'close'])->middleware('role:admin,manager,cashier');
            Route::get('/current', [ShiftController::class, 'current'])->middleware('role:admin,manager,cashier');
            Route::get('/history', [ShiftController::class, 'history'])->middleware('role:admin,manager,cashier');
        });

        // ---- Dashboard (admin + manager + cashier) ----
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('role:admin,manager,cashier');

        // ---- Products & categories ----
        Route::get('/products/barcode/{barcode}', [ProductController::class, 'byBarcode']);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // ---- Inventory ----
        Route::prefix('inventory')->group(function () {
            Route::get('/movements', [InventoryController::class, 'movements'])->middleware('role:admin,manager,inventory_clerk');
            Route::post('/adjust', [InventoryController::class, 'adjust'])->middleware('role:admin,manager,inventory_clerk');
        });

        // ---- Customers ----
        Route::prefix('customers')->group(function () {
            Route::get('/search', [CustomerController::class, 'search']);
            Route::get('/{customer}/loyalty', [CustomerController::class, 'loyalty']);
            Route::get('/{customer}', [CustomerController::class, 'show']);
        });
        Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);

        // ---- Sales ----
        Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel']);

        // ---- Receipts ----
        Route::prefix('receipts')->group(function () {
            Route::get('/{sale}/html', [ReceiptController::class, 'html']);
            Route::get('/{sale}/download', [ReceiptController::class, 'download']);
        });

        // ---- Reports (admin + manager) ----
        Route::middleware('role:admin,manager')->prefix('reports')->group(function () {
            Route::get('/daily-sales', [ReportController::class, 'dailySales']);
            Route::get('/monthly-sales', [ReportController::class, 'monthlySales']);
            Route::get('/top-products', [ReportController::class, 'topProducts']);
            Route::get('/cashier-performance', [ReportController::class, 'cashierPerformance']);
            Route::get('/shift-summary', [ReportController::class, 'shiftSummary']);
        });

        // ---- Admin (admin only) ----
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::get('/audit-logs', [UserController::class, 'auditLogs']);
            Route::get('/roles', [RoleController::class, 'index']);
            Route::get('/roles/permissions', [RoleController::class, 'permissions']);
            Route::post('/roles/sync', [RoleController::class, 'sync']);
            Route::get('/settings', [SettingsController::class, 'index']);
            Route::put('/settings', [SettingsController::class, 'update']);
        });
    });
});
