<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Importa la fachada URL
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;


class AppServiceProvider extends ServiceProvider
{
    // ... (método register) ...

    public function boot(): void
    {
        if (env('APP_URL')) {
            URL::forceRootUrl(env('APP_URL'));
            if (str_starts_with(env('APP_URL'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        // Registro de la vista personalizada para notificaciones
        // SIN ESTA CONFIGURACION NO FUNCIONAN LAS NOTIFICACIONES PERSONALIZADAS HIJO DE P... Me costo lograrlo
        Notification::configureUsing(function (Notification $notification): void {
            $notification->view('filament.notifications.notification');
        });

        // ESTA REDIRECCION LA USE PARA FORZAR LAS PRUEBAS Y VER SI LLEGABA LA PETICION AL SERVIDOR. NO ES NECESARIO PORQUE LIVEWIRE YA MANEJA TODO EL FLUJO INTERNAMENTE. PERO LO DEJO COMO RECORDATORIO DE LAS PRUEBAS QUE HICE.
        // ERAN PRUEBAS PARA QUE FUNCIONE EL BOTON DE SUSPENDER EVENTO
        // Livewire::setUpdateRoute(function ($handle) {
        //     return Route::post('/livewire/update', $handle);
        // });
    }

}