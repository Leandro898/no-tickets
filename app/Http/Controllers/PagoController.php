<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use App\Services\MercadoPagoTokenRefresher;
use App\Models\User;

class PagoController extends Controller
{
    public function crearPreferencia()
    {
        // Suplantamos al vendedor manualmente para esta prueba
        $vendedor = User::whereNotNull('mp_access_token')->first();

        if (!$vendedor) {
            return response()->json(['error' => 'No hay usuarios con cuenta de Mercado Pago vinculada.'], 403);
        }

        // Refrescar token si está vencido o vacío
        if (!$vendedor->mp_expires_in || now()->greaterThan($vendedor->mp_expires_in)) {
            MercadoPagoTokenRefresher::refresh($vendedor);
        }

        // Configurar el token actualizado
        MercadoPagoConfig::setAccessToken($vendedor->mp_access_token);

        $client = new PreferenceClient();

        $preferenceData = [
            "items" => [
                [
                    "title" => "Entrada de prueba",
                    "quantity" => 1,
                    "unit_price" => 1000
                ]
            ],
            "back_urls" => [
                "success" => route('pago.exito'),
                "failure" => route('pago.fallo'),
                "pending" => route('pago.pendiente')
            ],
            "auto_return" => "approved",
            "marketplace_fee" => 100
        ];

        $response = $client->create($preferenceData);

        return view('pagar', ['preferenceId' => $response->id]);
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

