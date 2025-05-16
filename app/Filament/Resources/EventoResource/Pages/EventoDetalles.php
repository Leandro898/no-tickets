<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use Filament\Resources\Pages\Page;

class EventoDetalles extends Page
{
    protected static string $resource = EventoResource::class;

    protected static string $view = 'filament.resources.evento-resource.pages.evento-detalles';

    protected static null|string $slug = 'gestionar-entradas';

    protected bool $shouldGenerateBreadcrumb = false;

    public Evento $record;

    public function mount(Evento $record): void
    {
        $this->record = $record;
    }

    protected function getViewData(): array
    {
        return [
            'evento' => $this->record,
        ];
    }
}
