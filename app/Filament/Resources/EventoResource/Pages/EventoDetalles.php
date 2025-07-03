<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use App\Models\Order; // Importa el modelo Order
use App\Jobs\RefundMercadoPagoPayment; // Importa tu Job de reembolso
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification; // Para enviar notificaciones
use Filament\Pages\Actions\Action;
use App\Models\PurchasedTicket;


class EventoDetalles extends Page
{
    //PROPIEDADES PUBLICAS PARA LA RECAUDACION Y TICKETS VENDIDOS

    public float $recaudacionTotal = 0;
    public int $ticketsVendidos = 0;
    public int $ticketsDisponibles = 100;

    // PROPIEDAD PARA CONTROLAR EL MODAL DEL LINK
    public bool $mostrarModalLink = false;

    protected static string $resource = EventoResource::class;

    public Evento $record;

    // Propiedades para controlar los modales
    public bool $mostrarPrimerModal = false; // Controla la visibilidad del primer modal ("¿Estás seguro?")
    public bool $mostrarSegundoModal = false; // Controla la visibilidad del segundo modal (confirmación de texto)
    public string $confirmacionTexto = ''; // Campo para el texto de confirmación

    protected static string $view = 'filament.resources.evento-resource.pages.evento-detalles';

    // METODO PARA MOSTRAR DATOS DE VENTAS
    public function mount(Evento $record)
    {
        $this->record = $record;

        // Recaudación total
        $this->recaudacionTotal = $record->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // Tickets vendidos
        $this->ticketsVendidos = PurchasedTicket::whereHas('order', function ($query) use ($record) {
            $query->where('event_id', $record->id)
                ->where('payment_status', 'paid');
        })->count();

        // Tickets disponibles
        $this->ticketsDisponibles = $record->total_tickets ?? 100;
    }



    // Metodo para quitar las migas de pan
    public function getBreadcrumbs(): array
    {
        return []; // el array vacio quita las migas de pan
    }

    // Metodo para eliminar evento (diferente de suspender)
    public function eliminarEvento()
    {
        $this->record->delete();
        Notification::make()
            ->title('Evento eliminado correctamente.')
            ->success()
            ->send();
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

    /**
     * Método para la confirmación final del segundo modal.
     * Aquí se despacha el Job de reembolso y se actualiza el estado del evento.
     */
    public function confirmarSuspension()
    {
        // 1. Validar el texto de confirmación
        if ($this->confirmacionTexto !== 'Reembolsar todas las compras') {
            Notification::make()
                ->title('Texto de confirmación incorrecto.')
                ->body('Debes escribir "Reembolsar todas las compras" para confirmar la suspensión y los reembolsos.')
                ->danger()
                ->send();
            return;
        }

        // 2. Cerrar los modales inmediatamente para dar feedback visual al usuario
        $this->cerrarModales();

        // 3. Obtener las órdenes asociadas a este evento que NECESITAN reembolso.
        $ordersToRefund = $this->record->orders()
                                       ->whereIn('payment_status', ['paid', 'approved']) // Ajusta según tus estados de pago
                                       ->whereNotIn('payment_status', ['refunded', 'cancelled', 'refund_failed'])
                                       ->get();

        // 4. Actualizar el estado del evento en tu base de datos a 'suspended'
        // ¡USANDO TU COLUMNA 'estado' EXISTENTE!
        $this->record->estado = 'suspended'; // <-- CAMBIO CLAVE AQUÍ
        $this->record->save();

        // 5. Notificación inicial para el administrador
        Notification::make()
            ->title('Evento suspendido.')
            ->body('El evento ha sido marcado como "suspendido". Iniciando el proceso de reembolso de las compras.')
            ->success()
            ->send();


        if ($ordersToRefund->isEmpty()) {
            Notification::make()
                ->title('No hay compras activas para reembolsar')
                ->body('No se encontraron compras pagadas activas para este evento que necesiten ser reembolsadas.')
                ->info()
                ->send();
            // Redirigir al índice de eventos si no hay reembolsos pendientes
            $this->redirect(self::getResource()::getUrl('index'));
            return;
        }

        // 6. Despachar el Job de reembolso para cada orden
        foreach ($ordersToRefund as $order) {
            RefundMercadoPagoPayment::dispatch($order);
        }

        // 7. Notificación final después de despachar los jobs
        Notification::make()
            ->title('Reembolsos en proceso.')
            ->body('Se han puesto en cola ' . $ordersToRefund->count() . ' compras para reembolso. El proceso puede tardar unos minutos.')
            ->success()
            ->send();

        // 8. Redirigir al índice de eventos
        $this->redirect(self::getResource()::getUrl('index'));
    }

    // modal
    protected function getActions(): array
    {
        return [
            Action::make('copiarLink')
                ->label('Copiar link')
                ->icon('heroicon-o-link')
                ->modalHeading('Link del evento')
                ->modalContent(view('filament.resources.evento-resource.pages.partials.copiar-link', ['record' => $this->record]))
                ->modalWidth('md')
                ->modalCloseButton()
                ->modalSubmitActionLabel('Copiar al portapapeles')
                ->action(function () {
                    // La acción de copiar al portapapeles debe hacerse con JS en la vista modal.
                    // Aquí puedes poner lógica si fuera necesaria.
                }),
        ];
    }
    
}
