<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Chequea modo mantenimiento
if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    require __DIR__.'/../storage/framework/maintenance.php';
}

// Autoload de Composer
require __DIR__.'/../vendor/autoload.php';

// Carga la aplicación
$app = require_once __DIR__.'/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

// Captura la petición y la envía al Kernel
$request  = Request::capture();
$response = $kernel->handle($request);

// Manda la respuesta al navegador
$response->send();

// Termina el ciclo de petición
$kernel->terminate($request, $response);
