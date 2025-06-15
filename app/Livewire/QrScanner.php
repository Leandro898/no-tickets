<?php

namespace App\Livewire;

use Livewire\Component;
use App\Http\Controllers\TicketValidationController; // Importa tu controlador

class QrScanner extends Component
{
    public $scanResult = ''; // Almacena el último código escaneado
    public $message = 'Listo para escanear. Por favor, asegúrate de que tu cámara esté activada.'; // Mensaje de estado
    public $messageType = 'info'; // 'info', 'success', 'error' para estilos CSS dinámicos

    // Listener para el evento JavaScript 'codeScanned'.
    // Cuando HTML5-QRCode detecte un código, emitirá este evento.
    protected $listeners = ['codeScanned' => 'handleScannedCode'];

    /**
     * Se ejecuta al montar el componente. Puedes inicializar cosas aquí.
     */
    public function mount()
    {
        // Opcional: Si quieres un mensaje inicial diferente al cargar
        // $this->message = 'Cargando el escáner...';
    }

    /**
     * Maneja el código escaneado recibido desde el JavaScript.
     * Llama a la lógica de validación de tu controlador.
     *
     * @param string $code El código decodificado del ticket.
     */
    public function handleScannedCode($code)
    {
        // Validar que se recibió un código
        if (empty($code)) {
            $this->scanResult = ''; // Limpia el resultado si no hay código
            $this->message = 'No se detectó ningún código válido.';
            $this->messageType = 'error';
            return;
        }

        $this->scanResult = $code; // Muestra el código que se intentó escanear

        // Instancia tu controlador de validación de tickets
        $validator = new TicketValidationController();
        $validationResult = $validator->processTicketValidation($code); // Llama a la lógica central de tu controlador

        // Actualiza las propiedades del componente con el resultado de la validación
        $this->message = $validationResult['message'];
        $this->messageType = $validationResult['status']; // 'success' o 'error'

        // Opcional: Puedes añadir lógica para emitir un evento JS de vuelta
        // para reiniciar el escáner automáticamente o hacer algún sonido.
        // $this->dispatch('scannerProcessed'); // Puedes escuchar esto en el JS
    }

    /**
     * Renderiza la vista Blade asociada a este componente Livewire.
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.qr-scanner');
    }
}