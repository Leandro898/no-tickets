<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PurchasedTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function misEntradas()
    {
        $user = Auth::user();

        $tickets = PurchasedTicket::whereHas('order', function ($query) use ($user) {
            $query->where('buyer_email', $user->email);
        })->with('entrada.evento')->get();

        return view('tickets.user', compact('tickets'));
    }

    public function showQr(Request $request, PurchasedTicket $ticket)
    {
        $user = Auth::user();
        if (!$user || $ticket->order->buyer_email !== $user->email) {
            abort(403, 'No autorizado');
        }

        // Ruta al QR en storage/app/private/tickets/...
        $path = storage_path('app/private/tickets/entrada-' . $ticket->short_code . '.png');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/png'
        ]);
    }

    public function view(PurchasedTicket $ticket)
    {
        $ticket->loadMissing(['order.event']);

        // Solo el dueño puede verlo
        if (!$ticket->order || $ticket->order->buyer_email !== auth()->user()->email) {
            abort(403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.pdf', ['ticket' => $ticket])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('entrada-' . $ticket->short_code . '.pdf');
    }
}
