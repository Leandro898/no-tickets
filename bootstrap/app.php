<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; // Asegúrate de que esta línea esté presente

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ¡DEBES AÑADIR ESTA LÍNEA AQUÍ DENTRO!
        $middleware->validateCsrfTokens(except: [
            '/api/mercadopago/webhook', // <--- Esta es la línea que falta
        ]);

        // Si tienes otros middlewares, irían aquí también.
        // Por ejemplo, si tu grupo 'web' tuviera otros middlewares:
        // $middleware->web(append: [
        //     \App\Http\Middleware\EncryptCookies::class,
        //     // etc.
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
