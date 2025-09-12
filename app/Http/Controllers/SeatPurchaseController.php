<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Seat;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeatPurchaseController extends Controller
{
    // Muestra la vista de checkout con los asientos seleccionados
    public function showCheckout(Evento $evento)
    {
        return view('eventos.checkout-seats', compact('evento'));
    }

    // Procesa el formulario de compra, crea la preferencia de pago y redirige a Mercado Pago
    public function purchase(Request $request, Evento $evento)
    {
        // 1️⃣ Validar los datos de la solicitud
        $data = $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer|exists:asientos,id',
            'buyer_full_name' => 'required|string|max:255',
            'buyer_email' => 'required|email',
        ]);

        $seatIds = $data['seats'];
        $buyerData = [
            'full_name' => $data['buyer_full_name'],
            'email' => $data['buyer_email']
        ];

        DB::beginTransaction();
        try {
            // 2️⃣ Cargar y bloquear los asientos para evitar duplicados
            $asientos = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            // 3️⃣ Verificar que todos los asientos estén disponibles
            $ocupados = $asientos->where('status', '!=', 'disponible');
            if ($ocupados->count()) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Algunos asientos ya no están disponibles.',
                    'ocupados' => $ocupados->pluck('id')
                ], 409);
            }

            // 4️⃣ Calcular el monto total y preparar los ítems para Mercado Pago
            $totalAmount = $asientos->sum('precio');
            $items = $asientos->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'title' => 'Asiento ' . $seat->label,
                    'quantity' => 1,
                    'unit_price' => (float) $seat->precio,
                    'currency_id' => 'ARS',
                ];
            })->toArray();

            // 5️⃣ Crear una orden en tu base de datos (con estado "pendiente")
            $order = Order::create([
                'event_id' => $evento->id,
                'buyer_full_name' => $buyerData['full_name'],
                'buyer_email' => $buyerData['email'],
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'mp_preference_id' => null,
                'items_data' => json_encode($items),
            ]);

            // 6️⃣ Llamar al controlador de Mercado Pago para crear la preferencia
            $mercadoPagoController = new MercadoPagoController();

            // ⚠️ SOLUCIÓN AL ERROR:
            // Aseguramos que el access token sea una string, accediendo a la clave 'access_token'
            // de la cuenta del productor del evento.
            $productor = $evento->productor;

            // Agregamos un log para depuración para ver qué contiene la variable
            Log::debug('Mercado Pago Account Data: ' . print_r($productor->mp_account, true));

            // Si es una cadena, la decodificamos primero
            $mpAccount = is_string($productor->mp_account) ? json_decode($productor->mp_account, true) : $productor->mp_account;

            $accessToken = is_array($mpAccount) && isset($mpAccount['access_token'])
                ? $mpAccount['access_token']
                : null;

            if (!$accessToken) {
                throw new \Exception('No se pudo obtener el Access Token de Mercado Pago.');
            }

            $preference = $mercadoPagoController->createPreference(
                $accessToken,
                $items,
                [
                    'name' => $buyerData['full_name'],
                    'email' => $buyerData['email'],
                ],
                $order->id,
                [
                    'success' => route('purchase.success', ['order' => $order->id]),
                    'failure' => route('purchase.failure', ['order' => $order->id]),
                    'pending' => route('purchase.pending', ['order' => $order->id]),
                ],
                // El 'marketplace_fee' se define en MercadoPagoController, 
                // por lo que el valor aquí no es relevante.
                0,
                $totalAmount
            );

            // 7️⃣ Actualizar la orden con el ID de la preferencia
            $order->mp_preference_id = $preference->id;
            $order->save();

            DB::commit();

            // 8️⃣ Devolver el ID de preferencia y la URL de pago para redirigir
            return response()->json([
                'success' => true,
                'preference_id' => $preference->id,
                'redirect_url' => $preference->init_point,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en proceso de compra: ' . $e->getMessage());
            return response()->json([
                'error' => 'No se pudo procesar la compra. Intenta de nuevo más tarde.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, Evento $evento)
    {
        // Este método se deja aquí por si la ruta original lo utiliza, 
        // pero la lógica de compra principal ahora está en el método `purchase`.
        $data = $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer|exists:asientos,id',
        ]);

        return redirect()->route('purchase.success', /* tu orden */);
    }
}
