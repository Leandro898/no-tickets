<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define las tareas programadas de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecuta la renovación de tokens de Mercado Pago una vez por día
        $schedule->command('mercadopago:refresh-tokens')->daily();
    }

    /**
     * Registra los comandos disponibles para Artisan.
     */
    protected function commands(): void
    {
        // Carga automáticamente todos los comandos en app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // También puede cargar comandos definidos en routes/console.php
        require base_path('routes/console.php');
    }
}

