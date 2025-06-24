<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ListaDigital extends Component
{
    public $eventoId;
    public $search = '';
    public $estado = 'all';

    public function mount($eventoId)
    {
        $this->eventoId = $eventoId;
    }

    public function reenviarEntrada($ticketId)
    {
        // A futuro: enviar email con QR o PDF
        session()->flash('mensaje', "Entrada $ticketId reenviada.");
    }

    public function render()
    {
        $tickets = DB::table('purchased_tickets')
            ->join('entradas', 'purchased_tickets.entrada_id', '=', 'entradas.id')
            ->where('entradas.evento_id', $this->eventoId)
            ->when($this->estado !== 'all', fn($q) => $q->where('purchased_tickets.status', $this->estado))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('purchased_tickets.buyer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('purchased_tickets.ticket_type', 'like', '%' . $this->search . '%');
                });
            })
            ->select('purchased_tickets.*')
            ->orderBy('purchased_tickets.created_at', 'desc')
            ->get();

        return view('livewire.lista-digital', compact('tickets'));
    }
}
