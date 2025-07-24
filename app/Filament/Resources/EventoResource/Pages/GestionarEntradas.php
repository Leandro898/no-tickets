<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use App\Filament\Resources\EntradaResource\Pages\CreateEntrada;
use App\Filament\Resources\EntradaResource;


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

    public function getHeaderActions(): array
    {
        return [
            Action::make('crear_entrada')
                ->label('Nueva entrada')
                ->url(EntradaResource::getUrl('create', ['evento_id' => $this->evento->id]))
                ->button()
                ->color('nueva-entrada')
                //->icon('heroicon-o-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
