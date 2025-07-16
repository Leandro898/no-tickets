<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EntradaResource;
use App\Filament\Resources\EventoResource;
// fijarme si funciona todo esto importacion quizas no sirve use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Actions\CancelAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;


class CreateEvento extends CreateRecord
{
    protected static string $resource = EventoResource::class;

    protected function getRedirectUrl(): string
    {
        return EntradaResource::getUrl('create', ['evento_id' => $this->record->id]);
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Crear Evento';
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    /**
     * Los "header actions" son los botones que aparecen junto al título.
     */
    protected function getActions(): array
    {
        return [
            // Botón de volver al listado de eventos
            Action::make('back')
                ->label('Volver a Eventos')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    /* BOTONES DE ACCIONES DEL FORMULAIO */
    
    
}
