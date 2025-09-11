<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Evento;
use App\Models\Entrada;
use App\Models\PurchasedTicket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitacionEnviada;

class PublicInvitationForm extends Component
{
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
        $this->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'dni'      => 'nullable|string|max:20',
        ]);
        Log::info('register: Validación de formulario exitosa. Datos validados.');

        $existing = PurchasedTicket::where('email', $this->email)
            ->whereHas('evento', function ($query) {
                $query->where('eventos.id', $this->evento->id);
            })->first();

        if ($existing) {
            session()->flash('error', 'Ya existe un registro para este email.');
            Log::warning('register: Intento de registro con email duplicado.');
            return;
        }
        Log::info('register: Verificación de email duplicado pasada.');

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

            Log::info('register: Generando código QR.');
            $qrCodePath = 'qrcodes/invitacion-' . $invitacion->unique_code . '.svg';
            Storage::disk('public')->put(
                $qrCodePath,
                QrCode::size(250)->generate(route('ticket.validate', ['code' => $invitacion->unique_code]))
            );
            Log::info('register: QR guardado en la ruta: ' . $qrCodePath);

            $invitacion->qr_path = $qrCodePath;
            $invitacion->save();

            Log::info('register: Intentando poner el correo en la cola de envíos para: ' . $invitacion->email);

            try {
                Mail::to($invitacion->email)->queue(new InvitacionEnviada($invitacion));
                Log::info('register: El método Mail::to()->queue() se ejecutó sin errores.');
            } catch (\Exception $e) {
                Log::error('Error al poner el correo en la cola: ' . $e->getMessage(), [
                    'email' => $invitacion->email,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            Log::info('register: Lógica de QR y email completada.');

            return redirect()->route('invitacion.confirmacion', ['invitacion_id' => $invitacion->id]);
        } catch (\Exception $e) {
            Log::error('Error al registrar la invitación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
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
