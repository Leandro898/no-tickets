<?php

namespace App\Http\Controllers;

use App\Models\PurchasedTicket;
use App\Mail\PurchasedTicketsMail;
use Illuminate\Support\Facades\Mail;

class TicketReenvioController extends Controller
{
    public function reenviar(PurchasedTicket $ticket)
    {
        $order = $ticket->order;
        $email = $order->buyer_email;

        if (!$email) {
            return back()->with('error', 'El ticket no tiene email asociado.');
        }

        Mail::to($email)->send(new PurchasedTicketsMail($order, [$ticket]));

        return back()->with('success', 'Entrada reenviada correctamente.');
    }
}
