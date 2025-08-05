<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Request;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Filament\Resources\EventoResource;
use \Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Actions\Action;


class CreateEntrada extends CreateRecord
{
    protected static string $resource = EntradaResource::class;

    // public function form(Form $form): Form
    // {
    //      ESTE METODO YA VIENE HEREDADO DE EntradaResource.php
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $eventoId = $data['evento_id'] ?? request()->query('evento_id');

        if (! $eventoId) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar un evento válido')
                ->danger()
                ->send();

            $this->halt();
        }

        $data['evento_id'] = $eventoId;

        if (isset($data['stock_inicial']) && ! isset($data['stock_actual'])) {
            $data['stock_actual'] = $data['stock_inicial'];
        }

        return $data;
    }


    protected function getRedirectUrl(): string
    {
        $evento = $this->record->evento;

        if ($evento->has_seats) {
            // Redirige al mapa de asientos
            return EventoResource::getUrl('configure-seats', [
                'record' => $evento->id,
            ]);
        }

        // Si no tiene butacas numeradas, va a gestionar entradas
        return EventoResource::getUrl('gestionar-entradas', [
            'record' => $evento->id,
        ]);
    }




    // QUITAR MIGAS DE PAN
    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Crear Entrada / Ticket';
    }

    /**
     * Aquí redefinimos las acciones del formulario:
     * – getCreateFormAction(): el botón principal (“Crear”)
     * – getCreateAndCreateAnotherFormAction(): “Crear y crear otra”
     * – getCancelFormAction(): “Cancelar”
     */
    protected function getFormActions(): array
    {
        return [
            $this
                ->getCreateFormAction()
                ->label('Crear Entrada')
                ->color('success')
                ->extraAttributes(['class' => 'fi-btn-color-primary']),

            $this
                ->getCancelFormAction()
                ->label('Cancelar')
                ->extraAttributes(['class' => 'fi-btn-color-secondary']),
        ];
    }

    // NOTIFICACION CUANDO SE CREA UNA NUEVA ENTRADA
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Entrada Generada!')
            ->body('La entrada se creó correctamente y ya está disponible.')
            ->icon('heroicon-o-check-circle')  // Cambia el ícono
            ->duration(5000); // Duración en ms, 5000 = 5 segundos
    }


    /* BOTON PARA REGRESAR*/
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a gestionar entradas')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => '/admin/eventos/' . request()->query('evento_id') . '/gestionar-entradas')
                ->extraAttributes(['class' => 'btn-volver']),
        ];
    }

}