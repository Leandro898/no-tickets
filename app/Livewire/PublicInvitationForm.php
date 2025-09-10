<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Evento;
use App\Models\Entrada;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PublicInvitationForm extends Component
{
    // Agrega esta propiedad estática para definir el layout
    //protected static string $layout = 'layouts.app';

    public Evento $evento;
    public $password;
    public $passwordCorrect = false;

    public $nombre;
    public $email;
    public $telefono;
    public $dni;

    public function mount($slug)
    {
        Log::info('Mount: El componente está siendo montado para el slug: ' . $slug);
        $this->evento = Evento::where('slug', $slug)->firstOrFail();
        Log::info('Mount: Evento encontrado: ' . $this->evento->nombre);
    }

    public function submitPassword()
    {
        Log::info('submitPassword: Método llamado. La contraseña en el componente es: ' . $this->password);

        $this->validate([
            'password' => 'required',
        ]);

        if ($this->password === $this->evento->password_invitacion) {
            $this->passwordCorrect = true;
            Log::info('submitPassword: La contraseña es CORRECTA. El estado passwordCorrect se ha cambiado a true.');
        } else {
            Log::warning('submitPassword: La contraseña es INCORRECTA.');
            throw ValidationException::withMessages([
                'password' => 'La contraseña de invitación es incorrecta.',
            ]);
        }
    }

    public function register()
    {
        Log::info('register: Método de registro llamado.');

        // Validar los campos del formulario de registro
        $this->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'dni'      => 'nullable|string|max:20',
        ]);
        Log::info('register: Validación de formulario exitosa.');

        // Verificar si ya se superó el cupo máximo
        $entradasCount = Entrada::where('evento_id', $this->evento->id)
            ->where('tipo', 'invitacion')
            ->count();
        Log::info('register: Entradas actuales: ' . $entradasCount . ' de un cupo de: ' . $this->evento->cupo_invitaciones);

        if ($entradasCount >= $this->evento->cupo_invitaciones) {
            Log::warning('register: Se superó el cupo máximo de invitaciones.');
            session()->flash('error', 'El cupo de invitaciones para este evento ya se ha completado.');
            return;
        }

        // Verificar si este email ya se registró para este evento
        $existing = Entrada::where('evento_id', $this->evento->id)
            ->where('email', $this->email)
            ->first();
        Log::info('register: Verificando si el email ya existe: ' . $this->email);

        if ($existing) {
            Log::warning('register: El email ' . $this->email . ' ya está registrado para este evento.');
            session()->flash('error', 'Ya existe un registro para este email.');
            return;
        }

        // Crear la nueva entrada (invitación)
        $invitacion = new Entrada();
        $invitacion->evento_id = $this->evento->id;
        $invitacion->user_id = auth()->id() ?? null;
        $invitacion->codigo = Str::uuid();
        $invitacion->nombre_completo = $this->nombre;
        $invitacion->email = $this->email;
        $invitacion->telefono = $this->telefono;
        $invitacion->dni = $this->dni;
        $invitacion->precio = 0;
        $invitacion->estado = 'pendiente';
        $invitacion->metodo_pago = 'invitacion';
        $invitacion->tipo = 'invitacion';
        $invitacion->save();
        Log::info('register: Nueva invitación creada con éxito. Código: ' . $invitacion->codigo);

        session()->flash('message', '¡Te has registrado con éxito! Tu invitación ha sido enviada a tu email.');
    }

    public function render()
    {
        Log::info('Render: El método render está siendo llamado. Estado de passwordCorrect: ' . ($this->passwordCorrect ? 'true' : 'false'));

        return view('livewire.public-invitation-form', ['evento' => $this->evento]);
    }
}
