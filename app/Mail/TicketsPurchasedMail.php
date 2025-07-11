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

class TicketsPurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $purchasedTickets;
    public $resetUrl;

    public function __construct(Order $order, $purchasedTickets, ?string $resetUrl = null)
    {
        $this->order = $order;
        $this->purchasedTickets = collect($purchasedTickets);
        $this->resetUrl = $resetUrl;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reenvio de entradas ' . $this->order->event->nombre . ' 🎟️',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchased_tickets',
            with: [
                'order' => $this->order,
                'purchasedTickets' => $this->purchasedTickets,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }


    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->purchasedTickets as $ticket) {
            $filePath = storage_path('app/public/' . $ticket->qr_path);

            if (file_exists($filePath)) {
                $attachments[] = Attachment::fromPath($filePath)
                    ->as('qr_ticket_' . $ticket->unique_code . '.png')
                    ->withMime('image/png');
            } else {
                \Log::warning('QR file not found for ticket: ' . $ticket->id . ' at path: ' . $filePath);
            }
        }

        return $attachments;
    }
}

