<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;



class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Crear Evento')
                ->url('/admin/eventos/create')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'btn-right',
                ]),
            // Este boton que sigue es para hacer aparece el otro boton de cancelar
            /* Action::make('delete')
                ->requiresConfirmation()
                ->action(fn() => $this->post->delete()), */
        ];
    }

    // PARA QUITAR MIGAS DE PAN
    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn($record) => EventoResource::getUrl('detalles', ['record' => $record]);
    }
}
