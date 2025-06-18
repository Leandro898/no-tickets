<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class PagoController extends Controller
{
    public function crearPreferencia(Evento $evento)
    {
        $vendedor = $evento->organizador;

        if (!$vendedor || !$vendedor->mp_access_token) {
            return back()->withErrors(['error' => 'El organizador no tiene Mercado Pago conectado.']);
        }

        // Importante: usamos el access_token del marketplace
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        $entrada = $evento->entradas()->first();
        if (!$entrada) {
            return back()->withErrors(['error' => 'El evento no tiene entradas disponibles.']);
        }

        $preferenceData = [
            "items" => [
                [
                    "title" => $entrada->titulo ?? 'Entrada',
                    "quantity" => 1,
                    "unit_price" => (float) $entrada->precio,
                ],
            ],
            "back_urls" => [
                "success" => route('pago.exito'),
                "failure" => route('pago.fallo'),
                "pending" => route('pago.pendiente'),
            ],
            "auto_return" => "approved",
            "marketplace_fee" => 100,
            "external_reference" => 'orden_' . uniqid(),
        ];

        $client = new PreferenceClient();
        $response = $client->create($preferenceData);

        return view('pagar', ['preferenceId' => $response->id]);
    }

    public function comprar(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'entradas' => 'required|array',
            'entradas.*.cantidad' => 'required|integer|min:0',
        ]);

        $vendedor = $evento->organizador;
        if (!$vendedor || !$vendedor->mp_user_id) {
            return back()->withErrors(['error' => 'El organizador no tiene Mercado Pago conectado.']);
        }

        // Usar access token de la plataforma para crear el split
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        $items = [];
        $total = 0;

        foreach ($evento->entradas as $entrada) {
            $cantidad = (int) ($data['entradas'][$entrada->id]['cantidad'] ?? 0);

            if ($cantidad > 0) {
                $items[] = [
                    "title" => $entrada->titulo,
                    "quantity" => $cantidad,
                    "unit_price" => (float) $entrada->precio,
                ];
                $total += $cantidad * $entrada->precio;
            }
        }

        if (empty($items)) {
            return back()->withErrors(['error' => 'No seleccionaste ninguna entrada.']);
        }

        $marketplaceFee = round($total * 0.1, 2); // 10% de comisión

        $externalReference = 'orden_' . uniqid();

        Log::debug('Creando preferencia MP (split)', [
            'items' => $items,
            'marketplace_fee' => $marketplaceFee,
            'external_reference' => $externalReference,
            'organizador_mp_user_id' => $vendedor->mp_user_id,
        ]);

        try {
            $client = new PreferenceClient();
            $preference = $client->create([
                "items" => $items,
                "back_urls" => [
                    "success" => route('pago.exito'),
                    "failure" => route('pago.fallo'),
                    "pending" => route('pago.pendiente'),
                ],
                "auto_return" => "approved",
                "marketplace_fee" => $marketplaceFee,
                "external_reference" => $externalReference,
                "payer" => [
                    "email" => $request->input('email', 'comprador@test.com'),
                ],
                "metadata" => [
                    "evento_id" => $evento->id,
                    "organizador_id" => $vendedor->id,
                ]
            ]);
        } catch (MPApiException $e) {
            Log::error('Error al crear preferencia de Mercado Pago', [
                'mensaje' => $e->getMessage(),
                'response' => $e->getApiResponse(),
            ]);

            return back()->withErrors(['error' => 'Error al crear la preferencia de pago.']);
        }

        return view('pagar', ['preferenceId' => $preference->id]);
    }

    public function exito()
    {
        return '✅ Pago exitoso';
    }

    public function fallo()
    {
        return '❌ Pago fallido';
    }

    public function pendiente()
    {
        return '⏳ Pago pendiente';
    }
}

