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
        // 1) Renovación de tokens de Mercado Pago una vez al día
        $schedule->command('mercadopago:refresh-tokens')->daily();

        // 2) Liberar asientos cuya reserva haya expirado, cada minuto
        $schedule->command('seats:release-expired')->everyMinute();
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
