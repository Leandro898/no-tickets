<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\MercadoPagoTokenRefresher;

class RefreshMercadoPagoTokens extends Command
{
    protected $signature = 'mercadopago:refresh-tokens';

    protected $description = 'Renueva automáticamente los access_tokens de los vendedores conectados.';

    public function handle(): void
    {
        $users = User::whereNotNull('mp_refresh_token')->get();

        foreach ($users as $user) {
            if (!$user->mp_expires_in || now()->greaterThan($user->mp_expires_in)) {
                MercadoPagoTokenRefresher::refresh($user);
            }
        }

        $this->info('Tokens de Mercado Pago actualizados.');
    }
}

