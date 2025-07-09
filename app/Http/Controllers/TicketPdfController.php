<?php

namespace App\Http\Controllers;

use App\Models\PurchasedTicket;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketPdfController extends Controller
{
    public function download(PurchasedTicket $ticket)
    {
        // 1) Asegurarnos de tener montadas las relaciones necesarias
        $ticket->loadMissing(['order.event']);

        // 2) Autorización: sólo el comprador original puede descargar su ticket
        if (!$ticket->order || $ticket->order->buyer_email !== Auth::user()->email) {
            abort(403, 'No tenés permiso para ver este ticket.');
        }

        // 3) Generar el PDF desde la vista 'tickets.pdf'
        //    (que debe contener tu <div class="ticket-code">#{{ $ticket->short_code }}</div>
        //     y el QR generado con {!! QrCode::generate($ticket->short_code) !!})
        $pdf = Pdf::loadView('tickets.pdf', ['ticket' => $ticket])
            ->setPaper('a4', 'portrait'); // <- opcional

        // 4) Descargar usando el short_code en el nombre de archivo
        return $pdf->download('entrada-' . $ticket->short_code . '.pdf');
    }
}
