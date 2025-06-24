<?php

namespace App\Livewire;

use App\Models\PurchasedTicket;
use App\Mail\PurchasedTicketsMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ReenviarTicket extends Component
{
    public $ticketId;
    public bool $enviando = false;

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function reenviar()
    {
        if ($this->enviando) return;

        $this->enviando = true;

        try {
            $ticket = PurchasedTicket::find($this->ticketId);
            if (!$ticket || !$ticket->order?->buyer_email) {
                $this->dispatch('toast', [
                    'title' => 'Error',
                    'message' => 'No se pudo reenviar.',
                    'type' => 'error',
                ]);
            } else {
                Mail::to($ticket->order->buyer_email)->send(new PurchasedTicketsMail($ticket->order, [$ticket]));
                $this->dispatch('toast', [
                    'title' => 'Enviado',
                    'message' => 'Entrada reenviada correctamente.',
                    'type' => 'success',
                ]);
            }
        } finally {
            $this->enviando = false;
        }
    }

    public function render()
    {
        return view('livewire.reenviar-ticket');
    }
    
}
