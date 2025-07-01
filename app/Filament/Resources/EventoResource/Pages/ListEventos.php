<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    /**
     * Aquí definimos las acciones que irán en la barra superior
     * (junto al título de “Eventos”).
     */
    protected function getActions(): array
    {
        return [
            // Crea el botón “Nuevo Evento” que enlaza a /admin/eventos/create
            CreateAction::make()
                ->label('Nuevo Evento'),
        ];
    }
}
