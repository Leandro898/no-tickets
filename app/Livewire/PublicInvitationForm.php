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
        Log::info('register: Validación de formulario exitosa. Datos validados.');

        // 2. Lógica de negocio (Verificar cupo, email, etc.)
        Log::info('register: Verificación de cupo pasada.');

        $existing = PurchasedTicket::where('email', $this->email)
            ->whereHas('evento', function ($query) {
                $query->where('eventos.id', $this->evento->id); // <--- Línea corregida aquí
            })->first();

        if ($existing) {
            session()->flash('error', 'Ya existe un registro para este email.');
            Log::warning('register: Intento de registro con email duplicado.');
            return;
        }
        Log::info('register: Verificación de email duplicado pasada.');

        // 3. Creación de la invitación
        try {
            Log::info('register: Intentando encontrar la entrada de tipo invitacion.');
            $invitacionEntrada = Entrada::where('evento_id', $this->evento->id)
                ->where('tipo', 'invitacion')
                ->firstOrFail();
            Log::info('register: Entrada de invitación encontrada con ID: ' . $invitacionEntrada->id);

            $invitacion = PurchasedTicket::create([
                'order_id'    => null,
                'entrada_id'  => $invitacionEntrada->id,
                'unique_code' => Str::uuid(),
                'qr_path'     => null,
                'status'      => 'pendiente',
                'buyer_name'  => $this->nombre,
                'email'       => $this->email,
                'telefono'    => $this->telefono,
                'dni'         => $this->dni,
                'ticket_type' => 'invitacion',
                'short_code'  => Str::random(5),
            ]);
            Log::info('register: PurchasedTicket creado exitosamente con ID: ' . $invitacion->id);

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
