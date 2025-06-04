<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasedTicket;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TicketValidationController extends Controller
{
    public function showValidationPage($code)
    {
        $ticket = PurchasedTicket::with('entrada.evento')->where('unique_code', $code)->first();

        if (!$ticket) {
            abort(404, 'Ticket no encontrado.');
        }

        return view('ticket_validation_page', compact('ticket'));
    }

    public function scanTicket(Request $request, $code)
    {
        $ticket = PurchasedTicket::where('unique_code', $code)->first();

        if (!$ticket) {
            return response()->json(['status' => 'error', 'message' => 'Ticket no encontrado.'], 404);
        }

        if ($ticket->status === 'used') {
            return response()->json(['status' => 'error', 'message' => 'Este ticket ya ha sido utilizado.'], 409);
        }

        if ($ticket->status === 'invalid') {
            return response()->json(['status' => 'error', 'message' => 'Este ticket no es válido.'], 410);
        }

        // Marcar el ticket como usado
        $ticket->status = 'used';
        $ticket->scan_date = now();
        $ticket->save();

        Log::info('Ticket escaneado y marcado como utilizado', ['ticket_code' => $code]);

        return response()->json(['status' => 'success', 'message' => 'Ticket validado exitosamente.'], 200);
    }

    /**
     * Muestra la interfaz del escáner QR dentro de la aplicación.
     * Esta es la página que usará el operador/guardia para escanear.
     */
    public function showScannerInterface()
    {
        return view('tickets.scanner_interface');
    }
}