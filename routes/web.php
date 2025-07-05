<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\CompraEntradaSplitController;
use App\Http\Controllers\TicketValidationController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\MercadoPagoOAuthController;
use App\Livewire\TestScanner;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\Auth\RegistroProductorController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\TicketScanController;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\MisEntradasController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\TicketReenvioController;
use App\Livewire\MostrarTicket;
use App\Http\Controllers\TicketPdfController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\TicketScannerController;
use App\Filament\Pages\TicketScanner;

//RUTA DE INICIO CON UN CONTROLADOR PARA PODER HACER CONSULTAS Y TRAER DATOS DE LOS EVENTOS AL FRONT
Route::get('/', [HomeController::class, 'index'])->name('home');

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
//Route::view('/mercadopago/error', 'mercadopago.error')->name('mercadopago.error');

Route::post('/api/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');

Route::get('/purchase/success/{order}', [MercadoPagoController::class, 'success'])->name('purchase.success');
Route::get('/purchase/failure/{order}', [MercadoPagoController::class, 'failure'])->name('purchase.failure');
Route::get('/purchase/pending/{order}', [MercadoPagoController::class, 'pending'])->name('purchase.pending');

// --------------------------- TICKETS --------------------------
Route::get('/tickets', [CompraEntradaSplitController::class, 'index'])->name('tickets.index');
Route::get('/ticket/{code}/validate', [TicketValidationController::class, 'showValidationPage'])->name('ticket.validate');
//Route::post('/ticket/{code}/scan', [TicketValidationController::class, 'scanTicket'])->name('ticket.scan');
// Route::get('/scan-interface', [TicketValidationController::class, 'showScannerInterface'])->name('ticket.scanner.interface');

Route::get('/scanner-test', TestScanner::class);

// OTRO TEST FUERA DE FILAMENT
/* Route::middleware(['auth', 'role:scanner'])->group(function () {
    Route::get('/scanner-test', [ScannerController::class, 'index']);
}); */

// ULTIMO SCANNER
Route::middleware(['auth'])->get('/scanner', function () {
    return view('filament.pages.scan-qr-redirect');
})->name('scanner.index');

// VALIDAR QRs
// Route::middleware(['auth', 'role:scanner'])->group(function () {
//     Route::post('/validar-ticket', [TicketScanController::class, 'validar']);
// });

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

//RUTAS PARA REGISTRARTE CON GOOGLE
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

//RUTA PARA COPIAR EVENTOS DESDE EL PANEL DE DETALLES DE EVENTO DE UN PRODUCTOR
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

// //RUTA PARA DESCARGAR ENTRADAS POR WhatsApp
// Route::get('/descargar/qr/{filename}', function ($filename) {
//     $path = 'qrcodes/' . $filename;

//     if (!Storage::disk('public')->exists($path)) {
//         abort(404);
//     }

//     return Storage::disk('public')->download($path, 'entrada-' . $filename);
// })->name('qr.download');

// //OPCION PARA RECIBIR O DESCARGAR ENTRADAS POR WHATSAPP
// Route::get('/orden/{order}/reenviar-whatsapp', [TicketScanController::class, 'reenviarWhatsApp'])->name('orden.reenviar.whatsapp');

//RUTA PARA QUE SE PUEDA DESCARGAR LA ENTRADA DESPUES DE COMPRARLA

Route::get('/descargar-entrada/{filename}', function ($filename) {
    $path = storage_path('app/public/qrcodes/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return Response::download($path);
})->name('qr.descargar');

//RUTA PARA VER MIS ENTRADAS
Route::middleware(['auth'])->group(function () {
    Route::get('/mis-entradas', [MisEntradasController::class, 'index'])->name('mis-entradas');
});

// Rutas DASHBOARD USUARIO
Route::middleware(['auth'])->group(function () {
    // Redirige /dashboard a /mis-entradas
    Route::redirect('/dashboard', '/mis-entradas')
        ->name('dashboard');
});

// REENVIO DE TICKETS DESDE EL PANEL DE USUARIO
Route::middleware(['auth'])->get('/ticket/{ticket}/reenviar', [TicketReenvioController::class, 'reenviar'])->name('ticket.reenviar');

//RUTA PARA VER LOS TICKETS DESDE EL PANEL DEL USUARIO -bien perrito malvado con la docu
Route::get('/ticket/{ticket}', MostrarTicket::class)->name('ticket.mostrar');

//RUTA PARA PODER GENERAR LA ENTRADA COMO PDF EN EL PANEL DE USUARIO
Route::get('/ticket/{ticket}/descargar', [TicketPdfController::class, 'download'])
    ->name('ticket.descargar')
    ->middleware('auth');

// SCANNER FINAL NUEVO
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Esto mapea GET /admin/ticket-scanner → TicketScanner Page
        // (Filament lo hace por slug automáticamente)

        // AJAX endpoint:
        Route::post('ticket-scanner/scan', [TicketScannerController::class, 'scan'])
            ->name('ticket-scanner.scan');
    });






//esto conecta las rutas de autenticación
require __DIR__ . '/auth.php';
