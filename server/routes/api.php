<?php

use App\Http\Controllers\Api\BootstrapController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function (): void {
    Route::get('/bootstrap', BootstrapController::class)->name('api.v1.bootstrap');
});
