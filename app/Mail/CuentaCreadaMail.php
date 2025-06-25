<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class CuentaCreadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $resetLink;
    public $purchasedTickets;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @param string $nombre
     * @param string|null $resetLink
     * @param array $purchasedTickets
     */
    public function __construct($nombre, $resetLink = null, $purchasedTickets = [])
    {
        $this->nombre = $nombre;
        $this->resetLink = $resetLink;
        $this->purchasedTickets = $purchasedTickets;
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject('¡Gracias por tu compra – Innova Ticket!')
                      ->view('emails.cuenta-creada')
                      ->with([
                          'nombre' => $this->nombre,
                          'link' => $this->resetLink,
                          'tickets' => $this->purchasedTickets,
                      ]);

        foreach ($this->purchasedTickets as $ticket) {
            $filePath = storage_path('app/public/' . $ticket->qr_path);

            if (file_exists($filePath)) {
                $email->attach($filePath, [
                    'as' => 'entrada-' . $ticket->id . '.png',
                    'mime' => 'image/png',
                ]);
            }
        }

        return $email;
    }
}
