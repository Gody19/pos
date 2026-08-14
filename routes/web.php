<?php

use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:5,1');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---------------------------------------------------------------------------
// Authenticated application
// ---------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view dashboard');

    // -----------------------------------------------------------------------
    // Shifts
    // -----------------------------------------------------------------------
    Route::middleware('permission:manage shifts')->group(function () {
        Route::get('/shifts/open', [ShiftController::class, 'showOpenForm'])->name('shifts.open');
        Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open.store');
        Route::get('/shifts/close', [ShiftController::class, 'showCloseForm'])->name('shifts.close');
        Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close.store');
        Route::get('/shifts/history', [ShiftController::class, 'history'])->name('shifts.history');
    });

    // -----------------------------------------------------------------------
    // POS terminal
    // -----------------------------------------------------------------------
    Route::middleware('permission:create sales')->group(function () {
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index')->middleware('shift.open');
            Route::get('/products', [PosController::class, 'products'])->middleware('shift.open');
            Route::get('/cart', [PosController::class, 'cart']);
            Route::post('/cart/add', [PosController::class, 'addToCart']);
            Route::post('/cart/update', [PosController::class, 'updateCart']);
            Route::post('/cart/remove', [PosController::class, 'removeFromCart']);
            Route::post('/checkout', [PosController::class, 'checkout'])->middleware('shift.open');
        });
    });

    // -----------------------------------------------------------------------
    // Products
    // -----------------------------------------------------------------------
    Route::get('/products/barcode/{barcode}', [ProductController::class, 'byBarcode']);
    Route::middleware('permission:view products')->group(function () {
        Route::resource('products', ProductController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
        Route::middleware('permission:create products')->group(function () {
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        });
        Route::middleware('permission:update products')->group(function () {
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        });
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy')
            ->middleware('permission:delete products');
    });

    // -----------------------------------------------------------------------
    // Categories
    // -----------------------------------------------------------------------
    Route::middleware('permission:manage categories')->group(function () {
        Route::resource('categories', CategoryController::class);
    });

    // -----------------------------------------------------------------------
    // Customers
    // -----------------------------------------------------------------------
    Route::middleware('permission:view customers')->group(function () {
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::resource('customers', CustomerController::class)->only(['index', 'show']);
        Route::get('/customers/{customer}/loyalty', [CustomerController::class, 'loyalty'])->name('customers.loyalty');

        Route::middleware('permission:create customers')->group(function () {
            Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        });

        Route::middleware('permission:update customers')->group(function () {
            Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        });

        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
            ->name('customers.destroy')
            ->middleware('permission:delete customers');
    });

    // -----------------------------------------------------------------------
    // Inventory
    // -----------------------------------------------------------------------
    Route::middleware('permission:manage inventory')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    });

    // -----------------------------------------------------------------------
    // Sales & receipts
    // -----------------------------------------------------------------------
    Route::middleware('permission:view sales')->group(function () {
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::get('/sales/{sale}/receipt/download', [SaleController::class, 'downloadReceipt'])->name('sales.receipt.download');
        Route::get('/sales/{sale}/receipt/print', [SaleController::class, 'printReceipt'])->name('sales.receipt.print');

        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
            ->name('sales.cancel')
            ->middleware('permission:cancel sales');
    });

    // -----------------------------------------------------------------------
    // Reports
    // -----------------------------------------------------------------------
    Route::middleware('permission:view reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/daily-sales', [ReportController::class, 'dailySales'])->name('daily');
        Route::get('/monthly-sales', [ReportController::class, 'monthlySales'])->name('monthly');
        Route::get('/top-products', [ReportController::class, 'topProducts'])->name('top-products');
        Route::get('/cashier-performance', [ReportController::class, 'cashierPerformance'])->name('cashier-performance');
        Route::get('/shift-summary', [ReportController::class, 'shiftSummary'])->name('shift-summary');
    });

    // -----------------------------------------------------------------------
    // Admin
    // -----------------------------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});