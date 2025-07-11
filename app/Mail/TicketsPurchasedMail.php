<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class TicketsPurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $purchasedTickets;

    /**
     * @param Order $order
     * @param \Illuminate\Support\Collection|array $purchasedTickets
     */
    public function __construct(Order $order, $purchasedTickets)
    {
        $this->order             = $order;
        $this->purchasedTickets  = collect($purchasedTickets);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Gracias por tu compra – {$this->order->event->nombre} 🎟️",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets_purchased',  // <--- corregido aquí
            with: [
                'order'            => $this->order,
                'purchasedTickets' => $this->purchasedTickets,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        foreach ($this->purchasedTickets as $ticket) {
            $filePath = storage_path("app/public/{$ticket->qr_path}");
            if (file_exists($filePath)) {
                $attachments[] = Attachment::fromPath($filePath)
                    ->as("entrada-{$ticket->unique_code}.png")
                    ->withMime('image/png');
            }
        }
        return $attachments;
    }
}
