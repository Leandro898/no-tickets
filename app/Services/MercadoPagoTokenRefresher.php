<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class MercadoPagoTokenRefresher
{
    public static function refresh(User $user): void
    {
        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('mercadopago.client_id'),
            'client_secret' => config('mercadopago.client_secret'),
            'refresh_token' => $user->mp_refresh_token,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $user->update([
                'mp_access_token' => $data['access_token'],
                'mp_refresh_token' => $data['refresh_token'],
                'mp_expires_in' => now()->addSeconds($data['expires_in']),
            ]);

            Log::info("Access token renovado para user ID {$user->id}");
        } else {
            Log::error("Error al renovar token MP para user ID {$user->id}", [
                'response' => $response->body(),
            ]);
        }
    }
}
