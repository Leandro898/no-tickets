<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Widgets\SpacerWidget;

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
                ->label('Ir a detalles')
                ->url("/admin/eventos/{$this->record->slug}/detalles")
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
                    'class' => 'fi-color-primary',
                ]),
        ];
    }

    /* WIDGET DE ESPACIADO AL PIE DE LA PAGINA FILAMENT*/

    protected function getFooterWidgets(): array
    {
        return [
            SpacerWidget::class,
        ];
    }

    // METODO PARA MOSTRAR NOTIFICACION CUANDO SE EDITA UN EVENTO EXITOSAMENTE
    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Evento editado correctamente')
            ->success()
            ->send();
    }

    // METODO PARA REDIRIGIR DESPUES DE EDITAR EL EVENTO
    protected function getRedirectUrl(): string
    {
        return "/admin/eventos/{$this->record->slug}/detalles";
    }

}
