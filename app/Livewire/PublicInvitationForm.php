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
use Illuminate\Validation\ValidationException;

class PublicInvitationForm extends Component
{
    public $errorMessage = '';

    public Evento $evento;
    public $password;
    public $passwordCorrect = false;

    // Cambiamos las propiedades individuales por un array de invitados
    public $invitados = [
        ['nombre' => '', 'email' => '', 'telefono' => '', 'dni' => '']
    ];

    public function mount($slug)
    {
        Log::info('Mount: El componente está siendo montado para el slug: ' . $slug);
        $this->evento = Evento::where('slug', $slug)->firstOrFail();
        Log::info('Mount: Evento encontrado: ' . $this->evento->nombre);
    }

    public function addInvitado()
    {
        // Añadimos un nuevo elemento al array de invitados
        $this->invitados[] = ['nombre' => '', 'email' => '', 'telefono' => '', 'dni' => ''];
    }

    public function removeInvitado($index)
    {
        // Eliminamos un elemento del array de invitados por su índice
        unset($this->invitados[$index]);
        $this->invitados = array_values($this->invitados); // Reindexamos el array
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

        // Validamos cada invitado en el array
        $this->validate([
            'invitados.*.nombre'   => 'required|string|max:255',
            'invitados.*.email'    => 'required|email|max:255',
            'invitados.*.telefono' => 'nullable|string|max:20',
            'invitados.*.dni'      => 'nullable|string|max:20',
        ]);
        Log::info('register: Validación de formulario exitosa. Datos validados.');

        try {
            Log::info('register: Intentando encontrar la entrada de tipo invitacion.');
            $invitacionEntrada = Entrada::where('evento_id', $this->evento->id)
                ->where('tipo', 'invitacion')
                ->firstOrFail();
            Log::info('register: Entrada de invitación encontrada con ID: ' . $invitacionEntrada->id);

            // Iteramos sobre cada invitado para crear un registro único
            foreach ($this->invitados as $invitado) {

                // Ahora validamos si el email ya existe para este evento
                $existing = PurchasedTicket::where('email', $invitado['email'])
                    ->whereHas('evento', function ($query) {
                        $query->where('eventos.id', $this->evento->id);
                    })->first();

                if ($existing) {
                    session()->flash('error', 'El email ' . $invitado['email'] . ' ya tiene una invitación para este evento.');
                    Log::warning('register: Intento de registro con email duplicado.');
                    return;
                }

                $invitacion = PurchasedTicket::create([
                    'order_id'      => null,
                    'entrada_id'    => $invitacionEntrada->id,
                    'unique_code'   => Str::uuid(),
                    'qr_path'       => null,
                    'status'        => 'pendiente',
                    'buyer_name'    => $invitado['nombre'],
                    'email'         => $invitado['email'],
                    'telefono'      => $invitado['telefono'],
                    'dni'           => $invitado['dni'],
                    'ticket_type'   => 'invitacion',
                    'short_code'    => Str::random(5),
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
                    // 💡 CAMBIO CLAVE: Cambiamos ->queue() por ->send() para que se ejecute de inmediato
                    Mail::to($invitacion->email)->send(new InvitacionEnviada($invitacion));
                    Log::info('register: El método Mail::to()->send() se ejecutó sin errores.');
                } catch (\Exception $e) {
                    Log::error('Error al enviar el correo: ' . $e->getMessage(), [
                        'email' => $invitacion->email,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
            Log::info('register: Lógica de QR y email completada.');

            // Redireccionamos a la página de confirmación después de procesar todos los invitados
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
