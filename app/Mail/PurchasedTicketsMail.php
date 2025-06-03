<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Models\Order;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchasedTicket;
use Illuminate\Support\Facades\Storage;

class PurchasedTicketsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, $purchasedTickets)
    {
        $this->order = $order;
        $this->purchasedTickets = $purchasedTickets;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tus entradas para ' . $this->order->event->nombre . ' han llegado! 🎉', // Asunto dinámico
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.purchased_tickets',
            with: [ // ¡AÑADIR ESTE BLOQUE!
                'order' => $this->order,
                'purchasedTickets' => $this->purchasedTickets,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     * Aquí es donde adjuntaremos los códigos QR.
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->purchasedTickets as $ticket) {
            // Asegúrate de que qr_path contenga la ruta correcta al archivo del QR (ej. qrcodes/codigo-unico.png)
            $filePath = storage_path('app/public/' . $ticket->qr_path);

            if (file_exists($filePath)) {
                $attachments[] = Attachment::fromPath($filePath)
                                ->as('qr_ticket_' . $ticket->unique_code . '.png') // Nombre del archivo adjunto
                                ->withMime('image/png'); // Tipo MIME del archivo
            } else {
                \Log::warning('QR file not found for ticket: ' . $ticket->id . ' at path: ' . $filePath);
            }
        }

        return $attachments;
    }
}
