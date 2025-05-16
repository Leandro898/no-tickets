<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use Filament\Resources\Pages\Page;
use Illuminate\View\View;

class GestionarEntradas extends Page
{
    protected static string $resource = EventoResource::class;

    protected static string $view = 'filament.resources.evento-resource.pages.gestionar-entradas';

    public ?Evento $evento = null;

    public function mount($record): void
    {
        $this->evento = Evento::findOrFail($record);
    }

    protected function getViewData(): array
    {
        return [
            'evento' => $this->evento,
        ];
    }

}
