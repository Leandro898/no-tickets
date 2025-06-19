<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use Exception;

class CompraEntradaSplitController extends Controller
{
    public function show(Evento $evento)
    {
        return view('comprar-entrada-split', compact('evento'));
    }

    public function store(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'buyer_dni' => 'nullable|string|max:20',
            'entradas' => 'required|array',
            'entradas.*.id' => 'required|integer',
            'entradas.*.cantidad' => 'required|integer|min:1',
        ]);

        $total = 0;
        $items = [];

        foreach ($validated['entradas'] as $entrada) {
            $entradaDB = $evento->entradas()->findOrFail($entrada['id']);
            $cantidad = (int) $entrada['cantidad'];
            $subtotal = $entradaDB->precio * $cantidad;
            $total += $subtotal;

            $items[] = [
                'entrada_id' => $entradaDB->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $entradaDB->precio,
            ];
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'event_id' => $evento->id,
                'buyer_full_name' => $validated['nombre'],
                'buyer_email' => $validated['email'],
                'buyer_dni' => $validated['buyer_dni'],
                'total_amount' => $total,
                'payment_status' => 'pending',
                'items_data' => json_encode($items),
            ]);

            $vendedor = $evento->organizador;

            if (!$vendedor || !$vendedor->mp_access_token) {
                throw new Exception("El organizador no tiene Mercado Pago conectado.");
            }

            // Se usa el access_token del vendedor
            MercadoPagoConfig::setAccessToken($vendedor->mp_access_token);

            $client = new PreferenceClient();

            $preference = $client->create([
                'items' => array_map(function ($item) {
                    return [
                        'title' => 'Entrada ID ' . $item['entrada_id'],
                        'quantity' => $item['cantidad'],
                        'unit_price' => (float) $item['precio_unitario']
                    ];
                }, $items),
                'payer' => [
                    'email' => $validated['email'],
                    'name' => $validated['nombre'],
                    'identification' => [
                        'type' => 'DNI',
                        'number' => $validated['buyer_dni'] ?? ''
                    ]
                ],
                'back_urls' => [
                    'success' => route('purchase.success', ['order' => $order->id]),
                    'failure' => route('purchase.failure', ['order' => $order->id]),
                    'pending' => route('purchase.pending', ['order' => $order->id]),
                ],
                'auto_return' => 'approved',
                'notification_url' => route('mercadopago.webhook'),
                'metadata' => [
                    'order_id' => $order->id,
                ],
                'statement_descriptor' => 'ENTRADAS',
                'external_reference' => (string) $order->id,
                'marketplace_fee' => round($total * 0.10, 2), // Comision para la plataforma
            ]);

            DB::commit();

            return redirect($preference->init_point);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            DB::rollBack();
            Log::error('MPApiException: ' . $e->getMessage());
            Log::error('MPApiException response: ' . json_encode($e->getApiResponse(), JSON_PRETTY_PRINT));
            return back()->withErrors(['error' => 'Error de la API de Mercado Pago.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en compra con split: " . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo iniciar la compra.']);
        }
    }
}


