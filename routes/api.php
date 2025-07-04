<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MercadoPagoController; // Asegúrate de importar tu controlador
use App\Http\Controllers\TicketValidationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Puedes dejar esta ruta de ejemplo que Laravel suele incluir
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- ¡MUEVE TU RUTA DE WEBHOOK AQUÍ! ---
// NOTA: Quita el prefijo '/api' de la URL aquí, ya que el archivo api.php ya lo añade automáticamente.
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');

// Ruta para escanner definitivo en PRODUCCION
// Route::get('/tickets/validate', [TicketValidationController::class, 'validate'])
//     ->middleware(['auth', 'role:scanner']);