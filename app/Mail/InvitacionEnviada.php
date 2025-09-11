<?php

// app/Mail/InvitacionEnviada.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchasedTicket;
use Illuminate\Mail\Mailables\Attachment;

class InvitacionEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $invitacion;

    public function __construct(PurchasedTicket $invitacion)
    {
        $this->invitacion = $invitacion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Invitación para ' . $this->invitacion->evento->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitacion', // Debes crear esta vista
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('public', $this->invitacion->qr_path)
                ->as('invitacion-qr.svg')
                ->withMime('image/svg+xml'),
        ];
    }
}
