// config/mercadopago.php

<?php

return [
    // Access token de tu plataforma (el que obtuviste de tu aplicación en Mercado Pago)
    'platform_access_token' => env('MERCADOPAGO_PLATFORM_ACCESS_TOKEN'),

    // Client ID y Client Secret de tu aplicación para el flujo OAuth
    'client_id' => env('MERCADOPAGO_CLIENT_ID'),
    'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),

    // Si usar el entorno de Sandbox (pruebas) o Producción
    'sandbox' => env('MERCADOPAGO_SANDBOX', true),

    // URL de redireccionamiento para el flujo OAuth (debe coincidir con la configurada en Mercado Pago)
    'redirect_uri' => env('MP_REDIRECT_URI', 'http://localhost/mercadopago/callback'),
];