<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraEntradaController; // TU controlador principal de compra
use App\Http\Controllers\MercadoPagoController; // Para la conexión OAuth
use App\Http\Controllers\TicketValidationController; // Lo necesitaremos para la fase de validación de QR
use App\Http\Controllers\EventoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// --- Rutas del flujo de compra de entradas (UNIFICADAS) ---
// (He elegido la estructura /eventos/{evento}/comprar para ser más consistente con lo que se suele ver)

// Ruta para mostrar el formulario de compra de un evento (lista los tipos de entrada)
Route::get('/eventos/{evento}/comprar', [CompraEntradaController::class, 'show'])->name('comprar.entrada');

// Ruta para procesar la compra (crear la preferencia de Mercado Pago y redirigir)
Route::post('/eventos/{evento}/comprar', [CompraEntradaController::class, 'store'])->name('comprar.store');

// Rutas de redirección de Mercado Pago (éxito, fallo, pendiente)
// Estas son las URLs a las que Mercado Pago redirige al usuario después de la interacción con su pasarela.
Route::get('/purchase/success/{order}', [CompraEntradaController::class, 'success'])->name('purchase.success');
Route::get('/purchase/failure/{order}', [CompraEntradaController::class, 'failure'])->name('purchase.failure');
Route::get('/purchase/pending/{order}', [CompraEntradaController::class, 'pending'])->name('purchase.pending');


// --- Rutas para la conexión OAuth de Mercado Pago (para vendedores/organizadores) ---
// Estas rutas son para que el organizador del evento conecte su cuenta de Mercado Pago.
// Asumimos que estas rutas requieren que el usuario esté autenticado.
Route::middleware(['auth'])->group(function () {
    Route::get('/mercadopago/connect', [MercadoPagoController::class, 'connect'])->name('mercadopago.connect');
    Route::get('/mercadopago/callback', [MercadoPagoController::class, 'callback'])->name('mercadopago.callback');
    Route::get('/mercadopago/status', function () {
        return view('mercadopago.status');
    })->name('mercadopago.status');
    Route::post('/mercadopago/unlink', [MercadoPagoController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
});

// --- Ruta para el WEBHOOK de Mercado Pago (¡CRÍTICO! Este es el que recibe las notificaciones de pago) ---
// Esta ruta es llamada por los servidores de Mercado Pago, NO por tu usuario.
// No necesita autenticación porque es un sistema externo llamándola.
// ¡AHORA APUNTA A CompraEntradaController!
Route::post('/api/mercadopago/webhook', [CompraEntradaController::class, 'handleWebhook'])->name('mercadopago.webhook');

// --- Rutas para la gestión y validación de tickets (futuras fases, pero ya las dejamos) ---
// Ruta para listar todos los PurchasedTickets (si es para una administración)
Route::get('/tickets', [CompraEntradaController::class, 'index'])->name('tickets.index');

// Rutas para la validación de tickets por QR
Route::get('/ticket/{code}/validate', [TicketValidationController::class, 'showValidationPage'])->name('ticket.validate');
Route::post('/ticket/{code}/scan', [TicketValidationController::class, 'scanTicket'])->name('ticket.scan');

//Ruta eventos.show
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');