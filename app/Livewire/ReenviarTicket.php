<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchasedTicket;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketsResendMail;

class ReenviarTicket extends Component
{
    public int $ticketId;
    public bool $enviando = false;

    public function mount(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function reenviar()
    {
        // Evita dobles envíos
        if ($this->enviando) {
            return;
        }
        $this->enviando = true;

        $ticket = PurchasedTicket::find($this->ticketId);
        $email  = $ticket?->order?->buyer_email;

        if (! $ticket || ! $email) {
            // Error de reenvío
            $this->js('window.dispatchEvent(new CustomEvent("toast", { detail: { title: "Error", message: "No se pudo reenviar.", type: "error" } }))');
        } else {
            // Envía el mail de reenvío
            \Mail::to($email)
                ->send(new \App\Mail\TicketsResendMail($ticket->order));

            // Notifica éxito
            $this->js('window.dispatchEvent(new CustomEvent("toast", { detail: { title: "¡Listo!", message: "Entrada reenviada correctamente.", type: "success" } }))');
        }

        $this->enviando = false;
    }

    public function render()
    {
        return view('livewire.reenviar-ticket');
    }
}
