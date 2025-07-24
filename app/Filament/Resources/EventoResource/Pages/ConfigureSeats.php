<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\Page;

class ConfigureSeats extends Page
{
    protected static string $resource = EventoResource::class;
    protected static string $view     = 'filament.pages.evento.configure-seats';

    public function mount($record): void
    {
        // Cargamos el modelo
        $this->record = EventoResource::getModel()::findOrFail($record);
    }

    protected function getViewData(): array
    {
        // Estos datos estarán disponibles en la blade como $evento
        return [
            'evento' => $this->record,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
