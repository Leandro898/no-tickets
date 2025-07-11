<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class TicketsResendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $email = $this
            ->subject("Reenvío de entradas – {$this->order->event->nombre} 🎟️")
            ->view('emails.tickets_resend')
            ->with(['order' => $this->order]);

        foreach ($this->order->purchasedTickets as $ticket) {
            $path = storage_path("app/public/{$ticket->qr_path}");
            if (file_exists($path)) {
                $email->attach($path, [
                    'as'   => "entrada-{$ticket->unique_code}.png",
                    'mime' => 'image/png',
                ]);
            }
        }

        return $email;
    }
}
