<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Jobs\RefundMercadoPagoPayment;
use App\Models\PurchasedTicket;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class EventoDetalles extends ViewRecord
{
    protected static string $resource = EventoResource::class;
    protected static string $view     = 'filament.resources.evento-resource.pages.evento-detalles';

    public float  $recaudacionTotal    = 0;
    public int    $ticketsVendidos     = 0;
    public int    $ticketsDisponibles  = 100;

    public bool   $mostrarModalLink    = false;
    public bool   $mostrarPrimerModal  = false;
    public bool   $mostrarSegundoModal = false;
    public string $confirmacionTexto   = '';

    public function mount($record): void
    {
        parent::mount($record);

        // Cálculos de recaudación y tickets
        $this->recaudacionTotal = $this->record
            ->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $this->ticketsVendidos = PurchasedTicket::whereHas('order', function ($query) {
            $query
                ->where('event_id', $this->record->id)
                ->where('payment_status', 'paid');
        })->count();

        $this->ticketsDisponibles = $this->record->total_tickets ?? 100;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function eliminarEvento(): void
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
        return '';
    }

    public function abrirPrimerModal(): void
    {
        $this->mostrarPrimerModal = true;
    }

    public function entenderConsecuencias(): void
    {
        $this->mostrarPrimerModal  = false;
        $this->mostrarSegundoModal = true;
        $this->confirmacionTexto   = '';
    }

    public function cerrarModales(): void
    {
        $this->mostrarPrimerModal  = false;
        $this->mostrarSegundoModal = false;
        $this->confirmacionTexto   = '';
    }

    public function confirmarSuspension(): void
    {
        if ($this->confirmacionTexto !== 'Reembolsar todas las compras') {
            Notification::make()
                ->title('Texto de confirmación incorrecto.')
                ->body('Debes escribir "Reembolsar todas las compras" para confirmar la suspensión y los reembolsos.')
                ->danger()
                ->send();

            return;
        }

        $this->cerrarModales();

        $ordersToRefund = $this->record
            ->orders()
            ->whereIn('payment_status', ['paid', 'approved'])
            ->whereNotIn('payment_status', ['refunded', 'cancelled', 'refund_failed'])
            ->get();

        $this->record->estado = 'suspended';
        $this->record->save();

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

            $this->redirect(self::getResource()::getUrl('index'));
            return;
        }

        foreach ($ordersToRefund as $order) {
            RefundMercadoPagoPayment::dispatch($order);
        }

        Notification::make()
            ->title('Reembolsos en proceso.')
            ->body('Se han puesto en cola ' . $ordersToRefund->count() . ' compras para reembolso. El proceso puede tardar unos minutos.')
            ->success()
            ->send();

        $this->redirect(self::getResource()::getUrl('index'));
    }

    protected function getActions(): array
    {
        return [
            Action::make('copiarLink')
                ->label('Copiar link')
                ->icon('heroicon-o-link')
                ->modalHeading('Link del evento')
                ->modalContent(view('filament.resources.evento-resource.pages.partials.copiar-link', [
                    'record' => $this->record,
                ]))
                ->modalWidth('md')
                ->modalCloseButton()
                ->modalSubmitActionLabel('Copiar al portapapeles')
                ->action(function (): void {
                    // Lógica de copia si fuera necesaria
                }),
        ];
    }
}
