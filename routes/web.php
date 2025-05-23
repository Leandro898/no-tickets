<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraEntradaController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PaymentController;


Route::get('/', function () {
    return view('welcome');
});

// Rutas para comprar entradas
Route::get('/comprar/{evento}', [CompraEntradaController::class, 'show'])->name('comprar.entrada');
Route::post('/comprar/{evento}', [CompraEntradaController::class, 'store'])->name('comprar.entrada.procesar');

// Ruta para ver entradas vendidas
Route::get('/tickets', [CompraEntradaController::class, 'index'])->name('tickets.index');


// Mostrar formulario de compra
Route::get('/evento/{evento}/comprar', [CompraEntradaController::class, 'show'])
    ->name('comprar.entrada');

// Procesar compra
Route::post('/evento/{evento}/comprar', [CompraEntradaController::class, 'store'])
    ->name('comprar.entrada.store');

// Rutas para la conexión OAuth de Mercado Pago (solo para usuarios autenticados)
Route::middleware(['auth'])->group(function () {
    Route::get('/mercadopago/connect', [MercadoPagoController::class, 'connect'])->name('mercadopago.connect');
    Route::get('/mercadopago/callback', [MercadoPagoController::class, 'callback'])->name('mercadopago.callback');
    Route::get('/mercadopago/status', function () {
        // Simple vista para mostrar el estado de la conexión
        return view('mercadopago.status'); // Esta vista la crearemos a continuación
    })->name('mercadopago.status');
    Route::post('/mercadopago/unlink', [MercadoPagoController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
});

// Ruta para el webhook de Mercado Pago (no necesita autenticación, es llamada por Mercado Pago)
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');