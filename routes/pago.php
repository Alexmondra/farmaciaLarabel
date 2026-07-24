<?php

use App\Http\Controllers\Tienda\PagoController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:tienda')
    ->prefix('tienda')
    ->name('tienda.')
    ->group(function () {
        Route::get('/pago/{codigo}', [PagoController::class, 'show'])->name('pago.show');
        Route::post('/pago/{codigo}/procesar', [PagoController::class, 'procesar'])->name('pago.procesar');
        Route::post('/pago/{codigo}/crear-orden', [PagoController::class, 'crearOrdenAjax'])->name('pago.crearOrden');
    });

Route::post('/tienda/webhook/culqi', [PagoController::class, 'webhook'])
    ->name('tienda.webhook.culqi')
    ->withoutMiddleware([VerifyCsrfToken::class]);
