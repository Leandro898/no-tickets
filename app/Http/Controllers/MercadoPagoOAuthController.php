<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;

class MercadoPagoOAuthController extends Controller
{
    /**
     * Redirige a Mercado Pago para iniciar OAuth.
     */
    public function connect()
    {
        $user = auth()->user();
        if (!$user) {
            Log::error('Intento de connect sin usuario autenticado');
            return redirect()->route('filament.admin.pages.oauth-connect-page')
                ->with('error', 'Debes iniciar sesión para vincular Mercado Pago.');
        }

        $clientId = config('mercadopago.client_id');
        $redirectUri = config('mercadopago.redirect_uri');
        $state = $user->id; // Pasamos el user_id en state

        $authUrl = "https://auth.mercadopago.com.ar/authorization?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}&state={$state}";
        Log::info('Redirigiendo a Mercado Pago OAuth', ['auth_url' => $authUrl]);

        return redirect()->away($authUrl);
    }

    /**
     * Callback de Mercado Pago después del OAuth.
     */
    public function handleCallback(Request $request)
    {
        Log::info('Callback recibido de Mercado Pago', ['request' => $request->all(), 'headers' => $request->headers->all()]);

        if (!$request->has('code')) {
            Log::warning('Callback sin code', ['request' => $request->all()]);
            return response('No hay código', 400);
        }

        $code = $request->code;
        $userId = $request->state;
        $user = User::find($userId);

        if (!$user) {
            Log::error('Usuario no encontrado desde state', ['state' => $userId]);
            return response('Usuario no encontrado', 404);
        }

        Log::info('Código recibido', ['code' => $code, 'user_id' => $user->id]);

        try {
            // Intercambiar code por access_token
            $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('mercadopago.client_id'),
                'client_secret' => config('mercadopago.client_secret'),
                'code' => $code,
                'redirect_uri' => config('mercadopago.redirect_uri'),
            ]);

            if ($response->failed()) {
                Log::error('Error al intercambiar code por access_token', [
                    'user_id' => $user->id,
                    'response' => $response->body(),
                ]);
                return response('Error al vincular cuenta', 500);
            }

            $data = $response->json();
            Log::info('Access token recibido', ['user_id' => $user->id, 'data' => $data]);

            // Guardar tokens y datos del vendedor en la DB
            $user->update([
                'mp_access_token' => $data['access_token'] ?? null,
                'mp_refresh_token' => $data['refresh_token'] ?? null,
                'mp_user_id' => $data['user_id'] ?? null,
                'mp_expires_in' => isset($data['expires_in']) ? Carbon::now()->addSeconds($data['expires_in']) : null,
                'mp_public_key' => $data['public_key'] ?? null,
            ]);

            Log::info('Cuenta de Mercado Pago vinculada correctamente', ['user_id' => $user->id]);

            return redirect()->route('filament.admin.pages.oauth-connect-page')
                ->with('success', 'Cuenta de Mercado Pago vinculada correctamente.');
        } catch (\Exception $e) {
            Log::error('Excepción al vincular Mercado Pago', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('filament.admin.pages.oauth-connect-page')
                ->with('error', 'Ocurrió un error al vincular la cuenta.');
        }
    }

    /**
     * Desvincular cuenta Mercado Pago.
     */
    public function unlinkMPAccount(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            Log::error('Intento de desvincular sin usuario autenticado');
            return redirect()->back()->with('error', 'Usuario no autenticado.');
        }

        Log::info('Desvinculando cuenta Mercado Pago', ['user_id' => $user->id]);

        $user->update([
            'mp_access_token' => null,
            'mp_refresh_token' => null,
            'mp_user_id' => null,
            'mp_expires_in' => null,
            'mp_public_key' => null,
        ]);

        return redirect()->back()->with('success', 'Cuenta de Mercado Pago desvinculada correctamente.');
    }
}
