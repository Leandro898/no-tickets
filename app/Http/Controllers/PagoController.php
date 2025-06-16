<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class PagoController extends Controller
{
    public function crearPreferencia()
    {
        // Suplantamos al vendedor manualmente para esta prueba
        $vendedor = \App\Models\User::whereNotNull('mp_access_token')->first();

        if (!$vendedor) {
            return response()->json(['error' => 'No hay usuarios con cuenta de Mercado Pago vinculada.'], 403);
        }

        \MercadoPago\MercadoPagoConfig::setAccessToken($vendedor->mp_access_token);

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
