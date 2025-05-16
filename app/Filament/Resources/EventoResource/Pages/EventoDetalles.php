<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class EventoDetalles extends Page
{
    protected static string $resource = EventoResource::class;
    protected static string $view = 'filament.resources.evento-resource.pages.evento-detalles';
    
    // ⬇️ Desactivamos los breadcrumbs automáticos
    protected bool $shouldGenerateBreadcrumb = false;
}