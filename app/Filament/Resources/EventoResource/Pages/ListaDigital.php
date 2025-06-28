<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use App\Mail\PurchasedTicketsMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use App\Models\PurchasedTicket;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class ListaDigital extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = EventoResource::class;
    protected static string $view = 'filament.resources.evento-resource.pages.lista-digital';

    //PROPIEDA PARA CAMBIAR EL ESTADO DEL PEDIDO
    protected $listeners = ['reenviarEntrada', 'toggleEstado'];

    public int $record;

    // Título visible
    public function getTitle(): string
    {
        return 'Lista Digital de Tickets';
    }

    public function getTableQuery(): Builder
    {
        return \App\Models\PurchasedTicket::query()
            ->whereHas('entrada', fn($q) => $q->where('evento_id', $this->record));
    }


    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID de Entrada'),
            TextColumn::make('buyer_name')->label('Nombre'),
            TextColumn::make('ticket_type')->label('Tipo'),
            TextColumn::make('order.buyer_email')->label('Email'),
            TextColumn::make('order.total_amount')->label('Monto Total'),
            TextColumn::make('cantidad')->label('Cantidad')->getStateUsing(fn($record) => count(json_decode($record->order->items_data, true))),
            TextColumn::make('status')
                ->badge()
                ->color(fn($state) => $state === 'valid' ? 'success' : 'secondary')
                ->label('Estado'),
        ];
    }

    // FILTRO
    // protected function getTableFilters(): array
    // {
    //     return [
    //         SelectFilter::make('status')
    //             ->label('Estado')
    //             ->options([
    //                 'valid' => 'Validado',
    //                 'used' => 'Usado',
    //             ]),
    //     ];
    // }

    protected function getTableSearchableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('buyer_name'),
            Tables\Columns\TextColumn::make('ticket_type'),
        ];
    }

    //METODO PARA REENVIAR ENTRADA

    public function reenviarEntrada($ticketId)
    {
        $ticket = PurchasedTicket::find($ticketId);

        if ($ticket && $ticket->order?->buyer_email) {
            Mail::to($ticket->order->buyer_email)->send(new PurchasedTicketsMail($ticket->order, [$ticket]));

            Notification::make()
                ->title('Entrada reenviada')
                ->body('El email con las entradas fue reenviado a ' . $ticket->order->buyer_email)
                ->success()
                ->send();
        }

        $this->dispatch('$refresh');
    }

    // Metodo para quitar las migas de pan
    public function getBreadcrumbs(): array
    {
        return []; // el array vacio quita las migas de pan
    }


    public function toggleEstado($ticketId)
    {
        $ticket = PurchasedTicket::find($ticketId);
        if ($ticket) {
            $ticket->status = $ticket->status === 'valid' ? 'used' : 'valid';
            $ticket->save();
        }

        $this->dispatch('$refresh');
    }

    protected function getTableActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('reenviar_email')
                    ->label('Reenviar por Email')
                    ->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->action(fn($record) => $this->reenviarEntrada($record)),
            
                Action::make('reenviar_whatsapp')
                    ->label('Enviar por WhatsApp')
                    ->icon('heroicon-o-phone')
                    ->url(fn ($record) => 
                        'https://wa.me/' . preg_replace('/\D/', '', $record->order->buyer_phone) .
                        '?text=' . urlencode("Hola {$record->order->buyer_name}, gracias por tu compra. Aquí está tu entrada: " . asset('storage/' . $record->qr_path))
                    )
                    ->openUrlInNewTab()
            ])
            ->label('Acciones')
            ->color('warning')
            ->button()
            
        ];
    }

    
    
}
