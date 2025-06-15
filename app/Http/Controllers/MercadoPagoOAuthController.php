<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Carbon\Carbon;

class MercadoPagoOAuthController extends Controller
{
    public function connect()
    {
        $clientId = config('mercadopago.client_id');
        $redirectUri = config('mercadopago.redirect_uri');

        $authUrl = "https://auth.mercadopago.com.ar/authorization?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}";
        return redirect()->away($authUrl);
    }

    public function handleCallback(Request $request)
    {
        if (!$request->has('code')) {
            return response()->json(['error' => 'No se recibió el código de autorización.'], 400);
        }

        $code = $request->code;

        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('mercadopago.client_id'),
            'client_secret' => config('mercadopago.client_secret'),
            'code'          => $code,
            'redirect_uri'  => config('mercadopago.redirect_uri'),
        ]);

        if ($response->failed()) {
            Log::error('Error al obtener el access token de MP', ['body' => $response->body()]);
            return response()->json(['error' => 'No se pudo obtener el token de acceso.'], 500);
        }

        $data = $response->json();

        // Guardar los tokens en el usuario autenticado
        $user = Auth::user();
        $user->mp_access_token = $data['access_token'];
        $user->mp_refresh_token = $data['refresh_token'];
        $user->mp_user_id = $data['user_id'];
        $user->save();

        return response()->json(['message' => 'Cuenta Mercado Pago vinculada con éxito.']);
    }
}
