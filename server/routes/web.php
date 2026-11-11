<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\IptvServerController;
use App\Http\Controllers\Panel\MaintenanceMessageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');

Route::prefix('panel')->name('panel.')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/servers', [IptvServerController::class, 'index'])->name('servers.index');
        Route::get('/servers/create', [IptvServerController::class, 'create'])->name('servers.create');
        Route::post('/servers', [IptvServerController::class, 'store'])->name('servers.store');
        Route::get('/servers/{server}/edit', [IptvServerController::class, 'edit'])->name('servers.edit');
        Route::put('/servers/{server}', [IptvServerController::class, 'update'])->name('servers.update');
        Route::delete('/servers/{server}', [IptvServerController::class, 'destroy'])->name('servers.destroy');

        Route::get('/maintenance', [MaintenanceMessageController::class, 'index'])->name('maintenance.index');
        Route::get('/maintenance/create', [MaintenanceMessageController::class, 'create'])->name('maintenance.create');
        Route::post('/maintenance', [MaintenanceMessageController::class, 'store'])->name('maintenance.store');
        Route::get('/maintenance/{message}/edit', [MaintenanceMessageController::class, 'edit'])->name('maintenance.edit');
        Route::put('/maintenance/{message}', [MaintenanceMessageController::class, 'update'])->name('maintenance.update');
        Route::delete('/maintenance/{message}', [MaintenanceMessageController::class, 'destroy'])->name('maintenance.destroy');
    });
});
