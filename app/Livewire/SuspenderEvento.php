<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Evento;
use Filament\Notifications\Notification;

class SuspenderEvento extends Component
{
    public int  $eventoId;
    public bool $mostrarModal = false;

    public function mount(int $eventoId)
    {
        $this->eventoId = $eventoId;
    }

    public function abrirModal(): void
    {
        $this->mostrarModal = true;
    }

    public function cancelar(): void
    {
        $this->mostrarModal = false;
    }

    public function suspender()
    {
        // Borramos (o suspendemos) el evento
        Evento::findOrFail($this->eventoId)->delete();

        // Notificamos
        Notification::make()
            ->title('Evento eliminado correctamente.')
            ->success()
            ->send();

        // Redirige con la API de Livewire
        return $this->redirectRoute('filament.admin.resources.eventos.index');
    }


    public function render()
    {
        return view('livewire.suspender-evento');
    }
}
