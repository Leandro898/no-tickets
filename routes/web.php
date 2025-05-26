<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraEntradaController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\TicketValidationController;
use App\Http\Controllers\EventoController;
// NO necesitarás importar LoginController ni RegisterController si Filament maneja la autenticación.

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- NO INCLUIR Auth::routes(); AQUÍ SI USAS FILAMENT PARA AUTENTICACIÓN ---

// ... (el resto de tus rutas existentes) ...

// --- Rutas del flujo de compra de entradas (UNIFICADAS) ---
Route::get('/eventos/{evento}/comprar', [CompraEntradaController::class, 'show'])->name('comprar.entrada');
Route::post('/eventos/{evento}/comprar', [CompraEntradaController::class, 'store'])->name('comprar.store');

// Rutas de redirección de Mercado Pago (éxito, fallo, pendiente)
Route::get('/purchase/success/{order}', [CompraEntradaController::class, 'success'])->name('purchase.success');
Route::get('/purchase/failure/{order}', [CompraEntradaController::class, 'failure'])->name('purchase.failure');
Route::get('/purchase/pending/{order}', [CompraEntradaController::class, 'pending'])->name('purchase.pending');


// --- Rutas para la conexión OAuth de Mercado Pago (para vendedores/organizadores) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/mercadopago/connect', [MercadoPagoController::class, 'connect'])->name('mercadopago.connect');
    Route::get('/mercadopago/callback', [MercadoPagoController::class, 'callback'])->name('mercadopago.callback');
    Route::get('/mercadopago/status', function () {
        return view('mercadopago.status');
    })->name('mercadopago.status');
    Route::post('/mercadopago/unlink', [MercadoPagoController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
});

// --- Ruta para el WEBHOOK de Mercado Pago (¡CRÍTICO!) ---
Route::post('/api/mercadopago/webhook', [CompraEntradaController::class, 'handleWebhook'])->name('mercadopago.webhook');

// --- Rutas para la gestión y validación de tickets ---
Route::get('/tickets', [CompraEntradaController::class, 'index'])->name('tickets.index');
Route::get('/ticket/{code}/validate', [TicketValidationController::class, 'showValidationPage'])->name('ticket.validate');
Route::post('/ticket/{code}/scan', [TicketValidationController::class, 'scanTicket'])->name('ticket.scan');

//Ruta eventos.show
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');