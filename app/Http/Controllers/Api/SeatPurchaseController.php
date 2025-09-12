<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use App\Models\Order;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use App\Http\Controllers\MercadoPagoController;

class SeatPurchaseController extends Controller
{
    public function purchase(Request $request, Evento $evento)
    {
        // 1️⃣ Validar datos de entrada
        $data = $request->validate([
            'seats'           => 'required|array|min:1',
            'seats.*'         => 'integer|exists:seats,id',
            'buyer_full_name' => 'required|string|max:255',
            'buyer_email'     => 'required|email',
            'buyer_dni'       => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // 2️⃣ Bloquear y cargar asientos (con eager load de la entrada para el precio)
            $seats = Seat::with('entrada')
                ->whereIn('id', $data['seats'])
                ->lockForUpdate()
                ->get();

            Log::info('DEBUG purchase — asientos cargados', $seats->map(function ($s) {
                return [
                    'id'         => $s->id,
                    'status'     => $s->status,
                    'entrada_id' => $s->entrada_id,
                    'precio'     => optional($s->entrada)->precio,
                ];
            })->toArray());

            // 3️⃣ Rechazar SOLO los ya vendidos
            $alreadySold = $seats->filter(fn($s) => $s->status === 'vendido');
            if ($alreadySold->isNotEmpty()) {
                $ids = $alreadySold->pluck('id')->join(', ');
                throw new \Exception("Algunos asientos ya fueron vendidos: {$ids}");
            }

            // 4️⃣ Marcar todos como vendidos y limpiar reserva
            foreach ($seats as $seat) {
                $seat->status         = 'vendido';
                $seat->reserved_until = null;
                $seat->save();
            }

            // 5️⃣ Crear la orden en la base de datos
            $total = $seats->sum(fn($s) => $s->entrada->precio);
            $order = Order::create([
                'event_id'        => $evento->id,
                'buyer_full_name' => $data['buyer_full_name'],
                'buyer_email'     => $data['buyer_email'],
                'buyer_dni'       => $data['buyer_dni'] ?? null,
                'total_amount'    => $total,
                'payment_status'  => 'pending',
                'items_data' => json_encode(
                    $seats->map(function ($s) {
                        return [
                            'entrada_id' => $s->entrada_id,
                            'cantidad'   => 1,
                            'seat_id'    => $s->id,
                        ];
                    })
                ),
            ]);

            // 6️⃣ Configurar el access token de Mercado Pago del organizador
            $org = $evento->organizador;
            if (! $org || ! $org->mp_access_token) {
                throw new \Exception('El organizador no tiene Mercado Pago conectado.');
            }

            // ⚠️ FIX: Pasar el access token como primer argumento.
            $accessToken = $org->mp_access_token;
            Log::info('DEBUG: Access Token a enviar a Mercado Pago: ' . (is_string($accessToken) ? 'es una cadena' : gettype($accessToken)));

            // 7️⃣ Preparar los ítems para la preferencia
            $items = $seats->map(fn($s) => [
                'title'      => "Asiento #{$s->label}",
                'quantity'   => 1,
                'unit_price' => (float) $s->entrada->precio,
            ])->toArray();

            /** @var MercadoPagoController $mp */
            $mp = app(MercadoPagoController::class);
            $preference = $mp->createPreference(
                $accessToken, // <-- Token de acceso (Arg 1)
                $items, // <-- Ítems (Arg 2)
                [
                    'email'          => $order->buyer_email,
                    'name'           => $order->buyer_full_name,
                    'identification' => [
                        'type'   => 'DNI',
                        'number' => $order->buyer_dni ?? '',
                    ],
                ], // <-- Payer (Arg 3)
                (string) $order->id, // <-- External Reference (Arg 4)
                [
                    'success' => route('purchase.success', ['order' => $order->id]),
                    'failure' => route('purchase.failure', ['order' => $order->id]),
                    'pending' => route('purchase.pending', ['order' => $order->id]),
                ], // <-- Back URLs (Arg 5)
                0, // <-- Marketplace Fee (Arg 6)
                $total // <-- Total Amount (Arg 7)
            );

            Log::info('DEBUG purchase — MP Preference creada', [
                'id'         => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox'    => $preference->sandbox_init_point,
            ]);

            DB::commit();

            // 8️⃣ Devolver al frontend la URL de redirección
            return response()->json([
                'redirect_url' => $preference->init_point
                    ?? $preference->sandbox_init_point,
            ], 200);
        } catch (MPApiException $mpEx) {
            DB::rollBack();
            Log::error('MPApiException: ' . $mpEx->getMessage());
            Log::error('MPApiResponse: ' . json_encode($mpEx->getApiResponse(), JSON_PRETTY_PRINT));
            return response()->json(['error' => 'Error en Mercado Pago'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
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
