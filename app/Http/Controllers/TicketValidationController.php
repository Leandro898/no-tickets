<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasedTicket; // Asegúrate de que esta ruta sea correcta para tu modelo Ticket
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TicketValidationController extends Controller
{
    /**
     * Muestra la página de validación de un ticket específico.
     * @param string $code El cdigo único del ticket.
     * @return \Illuminate\View\View
     */
    public function showValidationPage($code)
    {
        $ticket = PurchasedTicket::with('entrada.evento')->where('unique_code', $code)->first();

        if (!$ticket) {
            abort(404, 'Ticket no encontrado.');
        }

        return view('ticket_validation_page', compact('ticket'));
    }

    /**
     * Procesa la lógica de validación de un ticket.
     * Esta lógica puede ser llamada desde una API o desde un componente Livewire.
     *
     * @param string $code El código único del ticket a validar.
     * @return array Un array asociativo con 'status' ('success'/'error') y 'message').
     */
    // public function processTicketValidation(string $code): array
    // {
    //     $ticket = PurchasedTicket::where('unique_code', $code)->first();

    //     if (!$ticket) {
    //         return ['status' => 'error', 'message' => 'Ticket no encontrado.'];
    //     }

    //     if ($ticket->status === 'used') {
    //         return ['status' => 'error', 'message' => 'Este ticket ya ha sido utilizado.'];
    //     }

    //     if ($ticket->status === 'invalid') {
    //         return ['status' => 'error', 'message' => 'Este ticket no es válido.'];
    //     }

    //     // Marcar el ticket como usado
    //     $ticket->status = 'used';
    //     $ticket->scan_date = now(); // Registra la fecha y hora del escaneo
    //     $ticket->save();

    //     Log::info('Ticket escaneado y marcado como utilizado', ['ticket_code' => $code]);

    //     return ['status' => 'success', 'message' => 'Ticket validado exitosamente.'];
    // }

    /**
     * Maneja la solicitud de escaneo de un ticket desde una API o ruta web.
     * Usa la lógica de processTicketValidation.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $code El código único del ticket.
     * @return \Illuminate\Http\JsonResponse
     */
    // public function scanTicket(Request $request)
    // {
    //     $code = $request->input('code');

    //     $ticket = PurchasedTicket::where('unique_code', $code)->first();
    //     if (! $ticket) {
    //         return response()->json(['status' => 'error', 'message' => 'Ticket no encontrado.'], 404);
    //     }

    //     if ($ticket->status !== 'valid') {
    //         return response()->json(['status' => 'error', 'message' => 'Ticket no es válido para escanear.'], 422);
    //     }

    //     $ticket->status = 'used';
    //     $ticket->scan_date = now();
    //     $ticket->save();

    //     return response()->json(['status' => 'success', 'message' => 'Ticket validado exitosamente.']);
    // }



    /**
     * Muestra la interfaz del escáner QR dentro de la aplicación.
     * Esta ruta ya no sería necesaria si la página del escáner es gestionada por Filament + Livewire.
     * Solo la mantengo por si la usas en otro contexto.
     */
    // public function showScannerInterface()
    // {
    //     return view('tickets.scanner_interface'); // Asumiendo que esta es la ruta de tu vista Blade original
    // }

    // METODO PARA SCANNER EN PRODUCCION
    // public function validate(Request $request)
    // {
    //     $code = $request->query('code');
    //     $valid = \App\Models\PurchasedTicket::where('unique_code', $code)->exists();

    //     return response()->json(['valid' => $valid]);
    // }
}