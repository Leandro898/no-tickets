<?php

namespace App\Livewire;

use App\Models\Evento;
use App\Models\Entrada; // Mantén este modelo si lo usas en otro lugar
use App\Models\PurchasedTicket; // Asegúrate de importar este modelo
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PublicInvitationForm extends Component
{
    // Agrega esta propiedad estática para definir el layout
    //protected static string $layout = 'layouts.app';

    public $errorMessage = '';

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

        // 1. Validación de campos
        $this->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'dni'      => 'nullable|string|max:20',
        ]);
        Log::info('register: Validación de formulario exitosa.');

        // 2. Lógica de negocio (Verificar cupo, email, etc.)
        $entradasCount = PurchasedTicket::where('ticket_type', 'invitacion')
            ->whereHas('evento', function ($query) {
                $query->where('id', $this->evento->id);
            })
            ->count();
        Log::info('register: Entradas actuales: ' . $entradasCount . ' de un cupo de: ' . $this->evento->cupo_invitaciones);

        if ($entradasCount >= $this->evento->cupo_invitaciones) {
            session()->flash('error', 'El cupo de invitaciones para este evento ya se ha completado.');
            return;
        }

        $existing = PurchasedTicket::where('email', $this->email)
            ->whereHas('evento', function ($query) {
                $query->where('id', $this->evento->id);
            })->first();

        if ($existing) {
            session()->flash('error', 'Ya existe un registro para este email.');
            return;
        }

        // 3. Creación de la invitación usando el modelo PurchasedTicket
        try {
            // Encuentra la entrada de tipo 'invitacion' para este evento
            $invitacionEntrada = Entrada::where('evento_id', $this->evento->id)
                ->where('tipo', 'invitacion')
                ->firstOrFail();

            $invitacion = PurchasedTicket::create([
                'order_id'    => null,
                'entrada_id'  => $invitacionEntrada->id,
                'unique_code' => Str::uuid(),
                'qr_path'     => null, // Lo generarás después con un job
                'status'      => 'pendiente',
                'buyer_name'  => $this->nombre,
                'email'       => $this->email,
                'telefono'    => $this->telefono,
                'dni'         => $this->dni,
                'ticket_type' => 'invitacion',
                'short_code'  => Str::random(5), // Tu modelo ya hace esto, pero es seguro pasarlo
            ]);

            // 4. Redirección final
            return redirect()->route('invitacion.confirmacion', ['invitacion_id' => $invitacion->id]);
        } catch (\Exception $e) {
            Log::error('Error al registrar la invitación: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error inesperado al procesar tu solicitud. Por favor, inténtalo de nuevo.');
            return;
        }
    }
    public function render()
    {
        Log::info('Render: El método render está siendo llamado. Estado de passwordCorrect: ' . ($this->passwordCorrect ? 'true' : 'false'));

        return view('livewire.public-invitation-form', ['evento' => $this->evento]);
    }
}
