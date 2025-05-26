<?php

return [
    // Credenciales de tu aplicación/plataforma (las que obtienes al crear la app en MP)
    'client_id' => env('MP_CLIENT_ID'),
    'client_secret' => env('MP_CLIENT_SECRET'),
    'platform_access_token' => env('MP_ACCESS_TOKEN'), // Tu Access Token de la plataforma
    'public_key' => env('MP_PUBLIC_KEY'), // Tu Public Key de la plataforma

    // URL de notificación para webhooks
    'notification_url' => env('MP_NOTIFICATION_URL'),

    // Entorno de ejecución (true para Sandbox, false para Producción)
    'sandbox' => env('APP_ENV') === 'local' || env('APP_ENV') === 'development', // O un env('MP_SANDBOX', true)

    // Puedes añadir otras configuraciones si las necesitas
];