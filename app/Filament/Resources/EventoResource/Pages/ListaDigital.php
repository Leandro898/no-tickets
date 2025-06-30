<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use App\Models\PurchasedTicket;
use App\Mail\PurchasedTicketsMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action   as TableAction; // ← para el menú de cada fila
use Filament\Actions\Action          as PageAction;  // ← para el header

class ListaDigital extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = EventoResource::class;
    protected static string $view     = 'filament.resources.evento-resource.pages.lista-digital';

    protected $listeners = ['reenviarEntrada', 'toggleEstado'];

    public int $record;

    /* ---------- Básico ---------- */

    public function getTitle(): string
    {
        return 'Lista Digital';
    }

    public function getBreadcrumbs(): array
    {
        return []; // oculta migas
    }

    /* ---------- Query ---------- */

    public function getTableQuery(): Builder
    {
        return PurchasedTicket::query()
            ->whereHas('entrada', fn($q) => $q->where('evento_id', $this->record));
    }

    /* ---------- Columnas ---------- */

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID de Entrada'),
            TextColumn::make('buyer_name')->label('Nombre'),
            TextColumn::make('ticket_type')->label('Tipo'),
            TextColumn::make('order.buyer_email')->label('Email'),
            TextColumn::make('order.total_amount')->label('Monto Total')
                ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.')),
            TextColumn::make('cantidad')
                ->label('Cant.')
                ->getStateUsing(fn($record) => count(json_decode($record->order->items_data, true))),
            TextColumn::make('status')
                ->badge()
                ->color(fn($state) => $state === 'valid' ? 'success' : 'secondary')
                ->label('Estado'),
        ];
    }

    protected function getTableSearchableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('buyer_name'),
            Tables\Columns\TextColumn::make('ticket_type'),
        ];
    }

    /* ---------- Acciones por fila ---------- */

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
                    ->url(
                        fn($record) =>
                        'https://wa.me/' . preg_replace('/\D/', '', $record->order->buyer_phone) .
                            '?text=' . urlencode("Hola {$record->order->buyer_name}, gracias por tu compra. Aquí está tu entrada: " . asset('storage/' . $record->qr_path))
                    )
                    ->openUrlInNewTab(),
            ])
                ->label('Acciones')
                ->color('warning')
                ->button(),
        ];
    }

    /* ---------- Config visual de la tabla ---------- */

    protected function configureTable(Table $table): void
    {
        $table
            ->heading('Lista Digital de Tickets')
            ->description('Control y validación de entradas')
            ->striped()                                 // zebra
            ->paginated([10, 25])                       // solo 10 / 25
            ->defaultPaginationPageOption(25)           // 25 por defecto
            ->defaultSort('id', 'desc');
    }

    /* ---------- Métodos auxiliares ---------- */

    public function reenviarEntrada($ticketId): void
    {
        $ticket = PurchasedTicket::find($ticketId);

        if ($ticket && $ticket->order?->buyer_email) {
            Mail::to($ticket->order->buyer_email)->send(
                new PurchasedTicketsMail($ticket->order, [$ticket])
            );

            Notification::make()
                ->title('Entrada reenviada')
                ->body('El email con las entradas fue reenviado a ' . $ticket->order->buyer_email)
                ->success()
                ->send();
        }

        $this->dispatch('$refresh');
    }

    public function toggleEstado($ticketId): void
    {
        if ($ticket = PurchasedTicket::find($ticketId)) {
            $ticket->update([
                'status' => $ticket->status === 'valid' ? 'used' : 'valid',
            ]);
        }

        $this->dispatch('$refresh');
    }

    /*------------- Botón “Volver a detalles” -------------*/
    protected function getHeaderActions(): array // (en v3 sigue llamándose así para Page)
    {
        return [
            PageAction::make('volver')
                ->label('Volver a detalles')
                ->icon('heroicon-o-arrow-left')
                ->url(
                    EventoResource::getUrl('detalles', ['record' => $this->record])
                )
                ->color('secondary')
                ->button()
                ->extraAttributes(['class' => 'btn-volver']),
        ];
    }
}
