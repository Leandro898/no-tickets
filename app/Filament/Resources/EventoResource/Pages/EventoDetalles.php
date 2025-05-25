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

    // Metodo para quitar las migas de pan
    public function getBreadcrumbs(): array
    {
        return []; // el array vacio quita las migas de pan
    }

    // Aca utilizo otro metodo que se lo asigno al boton "Eliminar Evento"
    public function eliminarEvento()
    {
        $this->record->delete();
        $this->redirect(self::getResource()::getUrl('index'));
    }

    public function getTitle(): string
    {
        return ''; // Retorna una cadena vacía para que no se muestre ningún título
    }

}