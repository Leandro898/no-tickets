<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class PagoDirectoSplitController extends Controller
{
    public function show(Evento $evento)
    {
        return view('pago-directo-split', compact('evento'));
    }

    public function store(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email',
            'entradas' => 'required|array',
        ]);

        $vendedor = $evento->organizador;

        if (!$vendedor || !$vendedor->mp_access_token) {
            return back()->withErrors(['error' => 'El organizador no tiene Mercado Pago conectado.']);
        }

        $items = [];
        $total = 0;

        foreach ($evento->entradas as $entrada) {
            $cantidad = $data['entradas'][$entrada->id]['cantidad'] ?? 0;
            if ($cantidad > 0) {
                $items[] = [
                    "title" => $entrada->titulo,
                    "quantity" => (int)$cantidad,
                    "unit_price" => (float)$entrada->precio,
                    "currency_id" => "ARS"
                ];
                $total += $cantidad * $entrada->precio;
            }
        }

        if ($total === 0) {
            return back()->withErrors(['error' => 'Debes seleccionar al menos una entrada.']);
        }

        try {
            $applicationFee = round($total * 0.03, 2); // 3% de comisión

            MercadoPagoConfig::setAccessToken($vendedor->mp_access_token);
            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => $items,
                "payer" => [
                    "email" => $data['email']
                ],
                "back_urls" => [
                    "success" => route('purchase.success', 0), // puedes ajustar el ID real si creás una orden
                    "failure" => route('purchase.failure', 0),
                    "pending" => route('purchase.pending', 0),
                ],
                "auto_return" => "approved",
                "application_fee_amount" => $applicationFee,
                "binary_mode" => true,
            ]);

            return redirect()->away($preference->init_point);
        } catch (\Exception $e) {
            Log::error('Error al generar preferencia con split', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'No se pudo generar el link de pago.']);
        }
    }

}


