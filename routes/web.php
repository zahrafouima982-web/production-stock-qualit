<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionLineController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionRecordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
| Sends the visitor to the right place without exposing a public landing page.
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->dashboardRouteName())
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest routes (login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ---- Role dashboards (unchanged from Phase 2) --------------------------

    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });

    Route::middleware('role:PRODUCTION_MANAGER,ADMIN')->prefix('production')->name('production.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'production'])->name('dashboard');
    });

    Route::middleware('role:QUALITY_CONTROLLER,ADMIN')->prefix('quality')->name('quality.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'quality'])->name('dashboard');
    });

    Route::middleware('role:STOCK_MANAGER,ADMIN')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'stock'])->name('dashboard');
    });

    // ---- Phase 3: Production Management -----------------------------------
    // No 'role:' middleware here on purpose — QUALITY_CONTROLLER needs
    // read-only access into this module (per the permission matrix), which a
    // blanket role gate can't express. Every action is Policy-gated instead
    // (see app/Policies/*), so access still narrows correctly per role.

    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('production-lines', ProductionLineController::class)->except(['show']);

    Route::prefix('production')->name('production.')->group(function () {
        Route::resource('orders', ProductionOrderController::class)->except(['destroy']);
        Route::post('orders/{order}/cancel', [ProductionOrderController::class, 'cancel'])->name('orders.cancel');

        Route::resource('orders.records', ProductionRecordController::class)
            ->except(['destroy'])
            ->shallow();
    });
});