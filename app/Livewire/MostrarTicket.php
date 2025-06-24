<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchasedTicket; // o el modelo que uses
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MostrarTicket extends Component
{
    public $ticket;

    public function mount($ticket)
    {
        $this->ticket = PurchasedTicket::findOrFail($ticket);
    }

    public function render()
    {
        return view('livewire.mostrar-ticket');
    }
}
