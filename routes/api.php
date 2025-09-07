<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MercadoPagoController;
use App\Models\Order;
use App\Http\Controllers\SeatMapController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\Api\SeatReservationController;
use App\Http\Controllers\Api\SeatPurchaseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta de ejemplo de Laravel Sanctum
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 1️⃣ Webhook de Mercado Pago
Route::match(['GET', 'POST'], 'mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])
    ->name('mercadopago.webhook');


// 2️⃣ Endpoint para el polling de status
// Route::get('orders/{order}/status', function (Order $order) {
//     return response()->json([
//         // aquí comprueba el campo que utilizas para guardar el estado
//         'status' => $order->payment_status,
//     ]);
// });

//RUTAS PARA SELECCION DE ASIENTOS

// Listar entradas para el mapa (GET /api/eventos/{evento}/entradas)
Route::get('/eventos/{evento}/entradas', [SeatMapController::class, 'listTickets']);

// Guardar la configuración de asientos (POST /api/eventos/{evento}/asientos)
Route::post('/eventos/{evento}/asientos', [SeatMapController::class, 'saveSeats']);

// RUTA PARA GUARDAR LA IMAGEN DE FONDO EN LOS EVENTOS QUE VENDEN ENTRADAS CON ASIENTOS

// ahora la subida de imagen va al método uploadBg() que ya existe
Route::post('/eventos/{evento}/upload-bg',   [SeatMapController::class, 'uploadBg']);
// elimina la imagen de fondo del evento
Route::post('/eventos/{evento}/delete-bg', [SeatMapController::class, 'deleteBg']);

// Listar asientos guardados para el mapa (GET /api/eventos/{evento}/asientos)
Route::get('/eventos/{evento}/asientos', [SeatMapController::class, 'listSeats']);

// (el delete-bg puedes dejarlo en EventoController si ya lo tienes probado,
//  o bien delegarlo también en SeatMapController con un deleteBg() idéntico)

//RUTA PARA GUARDAR EL MAPA DE ASIENTOS COMPLETO
Route::post('/eventos/{evento}/mapa', [SeatMapController::class, 'saveMap']);

// RUTA PARA OBTENER EL MAPA COMPLETO
// (esto es lo que el frontend necesita para cargar el mapa de asientos)
// Devuelve los asientos, la imagen de fondo y el mapa completo 
Route::get('/eventos/{evento}/map', [SeatMapController::class, 'getMap']);


// RUTAS PARA RESERVA DE ASIENTOS
Route::post('/asientos/reservar', [SeatReservationController::class, 'reservar'])->name('api.asientos.reservar');
Route::post('/asientos/liberar', [SeatReservationController::class, 'liberar'])
    ->name('api.asientos.liberar');

/* Ruta para la compra se asientos - Conexion con Mercado Pago */
Route::post(
    '/eventos/{evento}/asientos/purchase',
    [SeatPurchaseController::class, 'purchase']
)->name('api.eventos.asientos.purchase');

// RUTAS PARA COMPRAR ASIENTOS (API)
// Simula una compra de asientos (para pruebas)
Route::post('/eventos/{evento}/purchase-simulated', 
    [SeatReservationController::class, 'purchaseSimulated']
)->name('api.eventos.purchase-simulated');
