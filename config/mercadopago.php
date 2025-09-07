<?php

return [

    // Credenciales para iniciar el flujo OAuth (para obtener access_token del vendedor)
    'client_id' => env('MP_CLIENT_ID'),
    'client_secret' => env('MP_CLIENT_SECRET'),
    'redirect_uri' => env('MP_REDIRECT_URI'),

    // Configuración opcional para plataforma (si vas a hacer pagos como plataforma)
    'platform_access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
    'public_key' => env('MP_PUBLIC_KEY'),
    'notification_url' => env('MERCADO_PAGO_WEBHOOK_URL'),
    'sandbox' => env('MERCADO_PAGO_SANDBOX', true),
];
