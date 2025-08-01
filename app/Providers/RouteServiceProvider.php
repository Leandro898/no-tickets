<?php

namespace App\Providers;

use App\Models\Evento;               // ← importa tu modelo
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    // public function boot(): void
    // {
    //     // Activa el “scoped bindings” global para usar getRouteKeyName
    //     Route::scopedBindings();

    //     parent::boot();
    // }
}

