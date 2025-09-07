<?php

namespace App\Http\Controllers;

use App\Models\PurchasedTicket;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketPdfController extends Controller
{
    public function download(PurchasedTicket $ticket)
    {
        // Aseguramos relaciones
        $ticket->loadMissing(['order.event']);

        // Solo el comprador puede descargar
        if (!$ticket->order || $ticket->order->buyer_email !== Auth::user()->email) {
            abort(403, 'No tenés permiso para ver este ticket.');
        }

        // Generamos PDF
        $pdf = Pdf::loadView('tickets.pdf', ['ticket' => $ticket])
            ->setPaper('a4', 'portrait');

        // Descargar PDF
        return $pdf->download('entrada-' . $ticket->short_code . '.pdf');
    }

    // --- Nuevo método para visualizar en navegador ---
    public function view(PurchasedTicket $ticket)
    {
        $ticket->loadMissing(['order.event']);

        // Solo el comprador puede ver
        if (!$ticket->order || $ticket->order->buyer_email !== Auth::user()->email) {
            abort(403, 'No tenés permiso para ver este ticket.');
        }

        // Generamos PDF
        $pdf = Pdf::loadView('tickets.pdf', ['ticket' => $ticket])
            ->setPaper('a4', 'portrait');

        // Mostrar PDF en el navegador
        return $pdf->stream('entrada-' . $ticket->short_code . '.pdf');
    }
}
