<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\DeleteAction;
use Filament\Notifications\Notification;

class EventoDetalles extends Page
{
    protected static string $resource = EventoResource::class;

    public Evento $record;

    protected static string $view = 'filament.resources.evento-resource.pages.evento-detalles';

    public function mount(Evento $record)
    {
        $this->record = $record;
    }

    // ESTE METODO ME PERMITE AGREGAR UN BOTON DE FILAMENT PARA ELIMINAR EL EVENTO
    // protected function getActions(): array
    // {
    //     return [
    //         Action::make('Eliminar')
    //             ->requiresConfirmation()
    //             ->color('danger')
    //             ->action(function () {
    //                 $this->record->delete();

    //                 Notification::make()
    //                     ->title('Evento eliminado correctamente')
    //                     ->success()
    //                     ->send();

    //                 return redirect()->to(static::getResource()::getUrl('index'));
    //             }),
    //     ];
    // }

    // Aca utilizo otro metodo que se lo asigno al boton "Eliminar Evento"
    public function eliminarEvento()
    {
        $this->record->delete();
        $this->redirect(self::getResource()::getUrl('index'));
    }


}