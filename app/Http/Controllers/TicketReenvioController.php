<?php

namespace App\Http\Controllers;

use App\Models\PurchasedTicket;
// 1) cambiar la use al Mailable de reenvío
use App\Mail\TicketsResendMail;
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

        // 2) usar el Mailable correcto
        Mail::to($email)
            ->send(new TicketsResendMail($order));

        return back()->with('success', 'Entrada reenviada correctamente.');
    }
}
