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

    public function __construct(Order $order, $purchasedTickets)
    {
        $this->order            = $order;
        $this->purchasedTickets = collect($purchasedTickets);
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
            view: 'emails.tickets_purchased',
            with: [
                'order'            => $this->order,
                'purchasedTickets' => $this->purchasedTickets,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        $folder = storage_path('app/private/tickets');

        // Asegura que la carpeta exista (importante para el primer uso)
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        foreach ($this->purchasedTickets as $ticket) {
            $filePath = $folder . "/entrada-{$ticket->short_code}.pdf";

            // Si no existe, lo genera. Si existe, lo reutiliza.
            if (!file_exists($filePath)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.pdf', ['ticket' => $ticket])
                    ->setPaper('a4', 'portrait');
                $pdf->save($filePath);
            }

            $attachments[] = Attachment::fromPath($filePath)
                ->as("entrada-{$ticket->short_code}.pdf")
                ->withMime('application/pdf');
        }
        return $attachments;
    }
}
