<?php

namespace App\Http\Controllers;

use App\Models\PurchasedTicket;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketPdfController extends Controller
{
    public function download(PurchasedTicket $ticket)
    {
        // Cargar relaciones si no están cargadas
        $ticket->loadMissing('order.event');

        // Seguridad: solo el dueño del ticket puede descargarlo
        if (!$ticket->order || $ticket->order->buyer_email !== Auth::user()->email) {
            abort(403, 'No tenés permiso para ver este ticket.');
        }

        // Generar PDF con la vista 'tickets.pdf'
        $pdf = Pdf::loadView('tickets.pdf', ['ticket' => $ticket]);

        return $pdf->download('entrada-' . $ticket->id . '.pdf');
    }

}
