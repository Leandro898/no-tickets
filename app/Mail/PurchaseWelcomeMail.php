<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class PurchaseWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $resetUrl;

    /**
     * @param  Order   $order
     * @param  string  $resetUrl
     */
    public function __construct(Order $order, string $resetUrl)
    {
        $this->order    = $order;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Construye el correo.
     */
    public function build()
    {
        $mail = $this
            ->subject("¡Bienvenido a " . config('app.name') . "! 🎉")
            ->view('emails.purchase_welcome')
            ->with([
                'order'    => $this->order,
                'resetUrl' => $this->resetUrl,
            ]);

        foreach ($this->order->purchasedTickets as $ticket) {
            // Construimos el nombre de fichero a partir de short_code
            $filename = "entrada-{$ticket->short_code}.pdf";
            $filePath = storage_path("app/private/tickets/{$filename}");

            // <-- Aquí registras la ruta que está usando PHP:
            \Log::info("PurchaseWelcomeMail va a adjuntar: {$filePath}");
            
            if (is_file($filePath) && is_readable($filePath)) {
                $mail->attach($filePath, [
                    'as'   => $filename,
                    'mime' => 'application/pdf',
                ]);
            } else {
                \Log::warning("No pude adjuntar el PDF {$filePath}");
            }
        }

        return $mail;
    }
}
