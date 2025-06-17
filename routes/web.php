<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraEntradaController; // Para la compra de entradas
use App\Http\Controllers\MailController;
use App\Http\Controllers\MercadoPagoController;   // Para interacción directa con MP
use App\Http\Controllers\TicketValidationController; // Para la validación de tickets
use App\Http\Controllers\EventoController; // Si lo sigues usando para mostrar eventos
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MercadoPagoOAuthController;
use App\Http\Controllers\PagoController;

// PARA ENVIAR EMAIL
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');


// --- Rutas del flujo de compra de entradas (Gestionadas por CompraEntradaController) ---
Route::post('/eventos/{evento}/comprar', [CompraEntradaController::class, 'store'])->name('comprar.store');

Route::get('/eventos/{evento}/comprar', [CompraEntradaController::class, 'show'])->name('eventos.comprar');


// --- Rutas de redirección de Mercado Pago (Gestionadas por MercadoPagoController) ---
// Estas rutas son las 'back_urls' configuradas en la preferencia
Route::get('/purchase/success/{order}', [MercadoPagoController::class, 'success'])->name('purchase.success');
Route::get('/purchase/failure/{order}', [MercadoPagoController::class, 'failure'])->name('purchase.failure');
Route::get('/purchase/pending/{order}', [MercadoPagoController::class, 'pending'])->name('purchase.pending');


// --- Rutas para la conexión OAuth de Mercado Pago (siempre en MercadoPagoController) ---
// Route::middleware(['auth'])->group(function () {
//     Route::get('/mercadopago/connect', [MercadoPagoController::class, 'connect'])->name('mercadopago.connect');
//     Route::get('/mercadopago/callback', [MercadoPagoController::class, 'callback'])->name('mercadopago.callback');
//     Route::get('/mercadopago/status', function () {
//         return view('mercadopago.status');
//     })->name('mercadopago.status');
//     Route::post('/mercadopago/unlink', [MercadoPagoController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
// });

// RUTA PARA VINCULAR EL OAuth DE MERCADO PAGO
Route::get('/mercadopago/connect', [MercadoPagoOAuthController::class, 'connect'])->name('mercadopago.connect');

Route::get('/mercadopago/callback', [MercadoPagoOAuthController::class, 'handleCallback'])->name('mercadopago.callback');
Route::post('/mercadopago/unlink', [MercadoPagoOAuthController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
Route::view('/mercadopago/error', 'mercadopago.error')->name('mercadopago.error');


// --- Ruta para el WEBHOOK de Mercado Pago (¡CRÍTICO! - siempre en MercadoPagoController) ---
// Asegúrate de que esta URL sea accesible públicamente (con ngrok o dominio real)
Route::post('/api/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');

// --- Rutas para la gestión y validación de tickets ---
Route::get('/tickets', [CompraEntradaController::class, 'index'])->name('tickets.index'); // Aquí mostramos las entradas compradas
Route::get('/ticket/{code}/validate', [TicketValidationController::class, 'showValidationPage'])->name('ticket.validate');
Route::post('/ticket/{code}/scan', [TicketValidationController::class, 'scanTicket'])->name('ticket.scan');

//Ruta eventos.show
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

// --- Puedes eliminar estas rutas de prueba si ya no las necesitas ---
// Route::get('/pagar', [MercadoPagoController::class, 'showPaymentPage'])->name('mercadopago.pay');
// Route::post('/pagar/crear-preferencia', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.create_test_preference');

// Ruta para la interfaz del escáner web (para el operador/guardia)
Route::get('/scan-interface', [TicketValidationController::class, 'showScannerInterface'])->name('ticket.scanner.interface');

// PRUEBA SPLIT DE PAGO
Route::get('/pago/prueba', [PagoController::class, 'crearPreferencia'])->name('pago.prueba');
Route::get('/pago/exito', [PagoController::class, 'exito'])->name('pago.exito');
Route::get('/pago/fallo', [PagoController::class, 'fallo'])->name('pago.fallo');
Route::get('/pago/pendiente', [PagoController::class, 'pendiente'])->name('pago.pendiente');
