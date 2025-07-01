<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditEvento extends EditRecord
{
    protected static string $resource = EventoResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Editar Evento';
    }

    /* PARA COLOCAR BOTONES DE ACCION EN LA CABECERA */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a detalles')
                ->url("/admin/eventos/{$this->record->id}/detalles")
                ->icon('heroicon-o-arrow-left')
                ->extraAttributes(['class' => 'btn-volver']),

            //DeleteAction::make(),
        ];
    }

    /* BOTONES DE ACCIONES DEL FORMULAIO */
    protected function getFormActions(): array
    {
        return [
            $this
                ->getSaveFormAction()
                ->label('Guardar Cambios')
                ->extraAttributes([
                    'class' => 'fi-ac-btn-action fi-color-success',
                ]),

            $this
                ->getCancelFormAction()
                ->label('Cancelar')
                ->extraAttributes([
                    'class' => '',
                ]),
        ];
    }

    
}
