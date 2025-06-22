<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\CompraEntradaSplitController;
use App\Http\Controllers\TicketValidationController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\MercadoPagoOAuthController;
use App\Livewire\TestScanner;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\TicketScanController;
use App\Http\Controllers\Auth\RegistroProductorController;


Route::get('/', function () {
    return view('inicio');
});

// --------------------------- EVENTOS ---------------------------
Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

// ---------------------- COMPRA CON SPLIT ----------------------
Route::get('/eventos/{evento}/comprar-split', [CompraEntradaSplitController::class, 'show'])->name('eventos.comprar.split');
Route::post('/eventos/{evento}/comprar-split', [CompraEntradaSplitController::class, 'store'])->name('eventos.comprar.split.store');

// ----------------------- MERCADO PAGO -------------------------
Route::get('/mercadopago/connect', [MercadoPagoOAuthController::class, 'connect'])->name('mercadopago.connect');
Route::get('/mercadopago/callback', [MercadoPagoOAuthController::class, 'handleCallback'])->name('mercadopago.callback');
Route::post('/mercadopago/unlink', [MercadoPagoOAuthController::class, 'unlinkMPAccount'])->name('mercadopago.unlink');
Route::view('/mercadopago/error', 'mercadopago.error')->name('mercadopago.error');

Route::post('/api/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');

Route::get('/purchase/success/{order}', [MercadoPagoController::class, 'success'])->name('purchase.success');
Route::get('/purchase/failure/{order}', [MercadoPagoController::class, 'failure'])->name('purchase.failure');
Route::get('/purchase/pending/{order}', [MercadoPagoController::class, 'pending'])->name('purchase.pending');

// --------------------------- TICKETS --------------------------
Route::get('/tickets', [CompraEntradaSplitController::class, 'index'])->name('tickets.index');
Route::get('/ticket/{code}/validate', [TicketValidationController::class, 'showValidationPage'])->name('ticket.validate');
//Route::post('/ticket/{code}/scan', [TicketValidationController::class, 'scanTicket'])->name('ticket.scan');
Route::get('/scan-interface', [TicketValidationController::class, 'showScannerInterface'])->name('ticket.scanner.interface');

//Route::get('/scanner-test', TestScanner::class);

// OTRO TEST FUERA DE FILAMENT
Route::middleware(['auth', 'role:scanner'])->group(function () {
    Route::get('/scanner-test', [ScannerController::class, 'index']);
});

// ULTIMO SCANNER
Route::middleware(['auth'])->get('/scanner', function () {
    return view('filament.pages.scan-qr-redirect');
})->name('scanner.index');

// VALIDAR QRs
Route::middleware(['auth', 'role:scanner'])->group(function () {
    Route::post('/validar-ticket', [TicketScanController::class, 'validar']);
});

// RUTAS PARA REGISTRO CON EMAIL
Route::get('/registro', function () {
    return view('auth.productor.opciones');
})->name('registro.opciones');

Route::get('/registro/email', [RegistroProductorController::class, 'showEmailForm'])->name('registro.email');
Route::post('/registro/email', [RegistroProductorController::class, 'handleEmail']);

//REGISTRO CONTRASEÑA
Route::get('/registro/password', [RegistroProductorController::class, 'showPasswordForm'])->name('registro.password');
Route::post('/registro/password', [RegistroProductorController::class, 'handlePassword']);

//RUTAS PARA VERIFICACION DE EMAIL
Route::get('/registro/verificacion', [RegistroProductorController::class, 'showVerificationForm'])->name('registro.verificacion');
Route::post('/registro/verificacion', [RegistroProductorController::class, 'verifyCode']);

//RUTA PARA REENVIO DE CODIGO
Route::post('/registro/re-enviar-codigo', [RegistroProductorController::class, 'reenviarCodigo'])->name('registro.reenviar');

