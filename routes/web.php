<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraEntradaController; // Para la compra de entradas
use App\Http\Controllers\MailController;
use App\Http\Controllers\MercadoPagoController;   // Para interacción directa con MP
use App\Http\Controllers\TicketValidationController; // Para la validación de tickets
use App\Http\Controllers\EventoController; // Si lo sigues usando para mostrar eventos
use Illuminate\Support\Facades\Log;

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

// --- Rutas del flujo de compra de entradas (Gestionadas por CompraEntradaController) ---
Route::get('/eventos/{evento}/comprar', [CompraEntradaController::class, 'show'])->name('comprar.entrada');
Route::post('/eventos/{evento}/comprar', [CompraEntradaController::class, 'store'])->name('comprar.store');

// --- Rutas de redirección de Mercado Pago (Gestionadas por MercadoPagoController) ---
// Estas rutas son las 'back_urls' configuradas en la preferencia
Route::get('/purchase/success/{order}', [MercadoPagoController::class, 'success'])->name('purchase.success');
Route::get('/purchase/failure/{order}', [MercadoPagoController::class, 'failure'])->name('purchase.failure');
Route::get('/purchase/pending/{order}', [MercadoPagoController::class, 'pending'])->name('purchase.pending');


// --- Rutas para la conexión OAuth de Mercado Pago (siempre en MercadoPagoController) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/mercadopago/connect', [MercadoPagoController::class, 'connect'])->name('mercadopago.connect');
    Route::get('/mercadopago/callback', [MercadoPagoController::class, 'callback'])->name('mercadopago.callback');
    Route::get('/mercadopago/status', function () {
        return view('mercadopago.status');
    })->name('mercadopago.status');
    Route::post('/mercadopago/unlink', [MercadoPagoController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
});

// --- Ruta para el WEBHOOK de Mercado Pago (¡CRÍTICO! - siempre en MercadoPagoController) ---
// Asegúrate de que esta URL sea accesible públicamente (con ngrok o dominio real)
//Route::post('/api/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');

// --- Rutas para la gestión y validación de tickets ---
Route::get('/tickets', [CompraEntradaController::class, 'index'])->name('tickets.index'); // Aquí mostramos las entradas compradas
Route::get('/ticket/{code}/validate', [TicketValidationController::class, 'showValidationPage'])->name('ticket.validate');
Route::post('/ticket/{code}/scan', [TicketValidationController::class, 'scanTicket'])->name('ticket.scan');

//Ruta eventos.show
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

// --- Puedes eliminar estas rutas de prueba si ya no las necesitas ---
// Route::get('/pagar', [MercadoPagoController::class, 'showPaymentPage'])->name('mercadopago.pay');
// Route::post('/pagar/crear-preferencia', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.create_test_preference');

// PRUEBA DE ENVIAR EMAIL

Route::get('/send-mail', [MailController::class, 'index']);

// Route::get('/probar-email', function () {
//     try {
//         Log::info('Intentando enviar correo...');

//         Mail::send([], [], function ($message) {
//             $message->from('leandcief@gmail.com', 'Nombre del sitio');
//             $message->to('neuquenrenault@gmail.com');
//             $message->subject('Correo de Prueba Laravel');

//             // Esta es la forma correcta en Laravel 12 para correo HTML sin vista
//             $message->html('<h1>Hola</h1><p>Este es un correo de prueba enviado desde Laravel con Gmail SMTP.</p>');
//         });

//         Log::info('Correo enviado correctamente.');
//         return 'Correo enviado correctamente. Revisá tu bandeja de entrada (y también SPAM).';
//     } catch (\Exception $e) {
//         Log::error('Error al enviar el correo: ' . $e->getMessage());
//         return 'Error al enviar correo. Revisá storage/logs/laravel.log';
//     }
// });