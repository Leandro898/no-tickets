<?php

return [
    // Credenciales de tu aplicación/plataforma (las que obtienes al crear la app en MP)
    'client_id' => env('MP_CLIENT_ID'),
    'client_secret' => env('MP_CLIENT_SECRET'),
    'platform_access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'), // Tu Access Token de la plataforma
    'public_key' => env('MP_PUBLIC_KEY'), // Tu Public Key de la plataforma

    // URL de notificación para webhooks
    'notification_url' => env('MERCADO_PAGO_WEBHOOK_URL'),

    // Entorno de ejecución (true para Sandbox, false para Producción)
    // === CAMBIO CLAVE AQUÍ: Leer directamente de MERCADO_PAGO_SANDBOX en .env ===
    'sandbox' => env('MERCADO_PAGO_SANDBOX', true), // Por defecto, es true (sandbox) si la variable no existe en .env

    // Puedes añadir otras configuraciones si las necesitas
];