<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MercadoPagoController;
use App\Models\Order;

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
Route::post('mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])
     ->name('mercadopago.webhook');

// 2️⃣ Endpoint para el polling de status
Route::get('orders/{order}/status', function (Order $order) {
    return response()->json([
        // aquí comprueba el campo que utilizas para guardar el estado
        'status' => $order->payment_status,
    ]);
});
