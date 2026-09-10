<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
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

    // ADMIN — global oversight, whole application.
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });

    // PRODUCTION_MANAGER (+ ADMIN can supervise every module).
    Route::middleware('role:PRODUCTION_MANAGER,ADMIN')->prefix('production')->name('production.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'production'])->name('dashboard');
    });

    // QUALITY_CONTROLLER (+ ADMIN).
    Route::middleware('role:QUALITY_CONTROLLER,ADMIN')->prefix('quality')->name('quality.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'quality'])->name('dashboard');
    });

    // STOCK_MANAGER (+ ADMIN).
    Route::middleware('role:STOCK_MANAGER,ADMIN')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'stock'])->name('dashboard');
    });
});
