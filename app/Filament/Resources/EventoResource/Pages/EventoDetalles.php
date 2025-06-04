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

    // Propiedades para controlar los modales
    public bool $mostrarPrimerModal = false; // Controla la visibilidad del primer modal ("¿Estás seguro?")
    public bool $mostrarSegundoModal = false; // Controla la visibilidad del segundo modal (confirmación de texto)
    public string $confirmacionTexto = ''; // Campo para el texto de confirmación

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

    // Método para abrir el primer modal de confirmación
    public function abrirPrimerModal()
    {
        $this->mostrarPrimerModal = true;
    }

    // Método para procesar la confirmación del primer modal y abrir el segundo
    public function entenderConsecuencias()
    {
        $this->mostrarPrimerModal = false; // Cerrar el primer modal
        $this->mostrarSegundoModal = true; // Abrir el segundo modal
        $this->confirmacionTexto = ''; // Limpiar el campo de texto por si acaso
    }

    // Método para cerrar ambos modales
    public function cerrarModales()
    {
        $this->mostrarPrimerModal = false;
        $this->mostrarSegundoModal = false;
        $this->confirmacionTexto = ''; // Limpiar el texto
    }

    // Método para la confirmación final del segundo modal
    public function confirmarSuspension()
    {
        if ($this->confirmacionTexto !== 'Reembolsar todas las compras') {
            Notification::make()
                ->title('Texto incorrecto. Escribí: "Reembolsar todas las compras"')
                ->danger()
                ->send();
            return;
        }

        // Si el texto es correcto, procede a eliminar el evento
        $this->record->delete();

        Notification::make()
            ->title('Evento suspendido correctamente.')
            ->success()
            ->send();

        // Redirigir al índice de eventos después de la suspensión
        $this->redirect(self::getResource()::getUrl('index'));
    }

}
