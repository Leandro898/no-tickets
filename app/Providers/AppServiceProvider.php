<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Importa la fachada URL
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

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

        FilamentAsset::register([
            Css::make('custom-filament', resource_path('css/custom-filament.css')),
        ]);
    }
}