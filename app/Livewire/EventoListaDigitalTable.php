<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchasedTicket;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class EventoListaDigitalTable extends Component implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    use WithPagination;

    public $eventoId;

    protected function getTableQuery(): Builder
    {
        return PurchasedTicket::query()
            ->whereHas('entrada', function ($query) {
                $query->where('evento_id', $this->eventoId);
            });
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('buyer_name')->label('Nombre'),
            TextColumn::make('ticket_type')->label('Tipo de Entrada'),
            TextColumn::make('status')->label('Estado'),
            TextColumn::make('scanned_at')->label('Validado en')->dateTime(),
        ];
    }

    public function render()
    {
        return view('livewire.evento-lista-digital-table');
    }
}
