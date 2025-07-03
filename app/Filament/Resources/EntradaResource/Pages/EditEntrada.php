<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use App\Filament\Resources\EventoResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Get;
use Filament\Pages\Actions\Action;
use App\Filament\Widgets\SpacerWidget;
use Filament\Notifications\Notification;

class EditEntrada extends EditRecord
{
    protected static string $resource = EntradaResource::class;

    /* BOTON PARA REGRESAR*/
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a gestionar entradas')
                ->icon('heroicon-o-arrow-left')
                ->url("/admin/eventos/{$this->record->evento_id}/gestionar-entradas")
                ->extraAttributes(['class' => 'btn-volver']),
        ];
    }

    // ESTE METODO CREA UN FORMULARIO QUE SOBREESCRIBE A LA CLASE EntradaResource.php QUE ES LA CLASE PADRE QUE DEFINE EL FORMULARIO QUE SE VA A RENDERIZAR

    // public function form(Form $form): Form 
    // {
    // }

    protected function getRedirectUrl(): string
    {
        // Redirige a la página de gestión de entradas del evento al que pertenece la entrada editada
        return EventoResource::getUrl('gestionar-entradas', ['record' => $this->record->evento_id]);
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Editar Entrada / Ticket';
    }

    protected function getFormActions(): array
    {
        return [
            $this
                ->getSaveFormAction()
                ->label('Guardar Cambios')
                ->color('success'),


            $this
                ->getCancelFormAction()
                ->label('Cancelar'),
        ];
    }

    /* WIDGET DE ESPACIADO AL PIE DE LA PAGINA FILAMENT*/

    protected function getFooterWidgets(): array
    {
        return [
            SpacerWidget::class,
        ];
    }

    // ESTO CANCELA LA NOTIFICACION QUE APARECE POR DEFECTO DE FILAMENT
    protected function getSavedNotification(): ?Notification
    {
        // Retorna null para no mostrar la notificación por defecto
        return null;
    }

    // METODO PARA CONFIGURAR Y CREAR UNA NOTIFICACION PERSONALIZADA
    protected function afterSave(): void
    {
        Notification::make()
            ->title('Entrada actualizada')
            ->body('La entrada fue actualizada correctamente.')
            ->send();
    }

}