<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Evento;
use Filament\Notifications\Notification;

class SuspenderEvento extends Component
{
    public $eventoId;
    public $mostrarModal = false;

    public function mount($eventoId)
    {
        $this->eventoId = $eventoId;
    }

    public function suspender()
    {
        $evento = Evento::findOrFail($this->eventoId);

        dd('OK'); // Debería aparecerte cuando hagas click en el botón "Sí, suspender"

        $evento->estado = 'suspended';
        $evento->save();

        Notification::make()
            ->title('Evento suspendido correctamente.')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.eventos.index');
    }

    public function render()
    {
        return view('livewire.suspender-evento');
    }
}
