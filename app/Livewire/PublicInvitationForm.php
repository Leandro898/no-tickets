<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Evento;
use App\Models\Entrada;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PublicInvitationForm extends Component
{
    public Evento $evento;
    public $password;
    public $passwordCorrect = false;

    public $nombre;
    public $email;
    public $telefono;
    public $dni;

    public function mount($slug)
    {
        $this->evento = Evento::where('slug', $slug)->firstOrFail();
    }

    public function submitPassword()
    {
        $this->validate([
            'password' => 'required',
        ]);

        if ($this->password === $this->evento->password_invitacion) {
            $this->passwordCorrect = true;
        } else {
            throw ValidationException::withMessages([
                'password' => 'La contraseña de invitación es incorrecta.',
            ]);
        }
    }

    public function register()
    {
        // Validar los campos del formulario de registro
        $this->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'dni'      => 'nullable|string|max:20',
        ]);

        // Verificar si ya se superó el cupo máximo
        $entradasCount = Entrada::where('evento_id', $this->evento->id)
            ->where('tipo', 'invitacion')
            ->count();

        if ($entradasCount >= $this->evento->cupo_invitaciones) {
            session()->flash('error', 'El cupo de invitaciones para este evento ya se ha completado.');
            return;
        }

        // Verificar si este email ya se registró para este evento
        $existing = Entrada::where('evento_id', $this->evento->id)
            ->where('email', $this->email)
            ->first();

        if ($existing) {
            session()->flash('error', 'Ya existe un registro para este email.');
            return;
        }

        // Crear la nueva entrada (invitación)
        $invitacion = new Entrada();
        $invitacion->evento_id = $this->evento->id;
        $invitacion->user_id = auth()->id() ?? null; // Si usas auth, si no, puedes dejarlo en null
        $invitacion->codigo = Str::uuid(); // Código único para el QR
        $invitacion->nombre_completo = $this->nombre;
        $invitacion->email = $this->email;
        $invitacion->telefono = $this->telefono;
        $invitacion->dni = $this->dni;
        $invitacion->precio = 0; // Las invitaciones no tienen costo
        $invitacion->estado = 'pendiente';
        $invitacion->metodo_pago = 'invitacion';
        $invitacion->tipo = 'invitacion'; // Campo que diferencia
        $invitacion->save();

        session()->flash('message', '¡Te has registrado con éxito! Tu invitación ha sido enviada a tu email.');

        // Opcionalmente, puedes redirigir a una página de éxito
        // return redirect()->route('invitacion.exitosa', ['codigo' => $invitacion->codigo]);
    }

    public function render()
    {
        return view('livewire.public-invitation-form');
    }
}
