<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Nueva carpeta privada para los PDFs
        $folder = storage_path('app/private/tickets');

        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        // Por cada ticket, genera (o usa) el PDF y lo adjunta
        foreach ($this->order->purchasedTickets as $ticket) {
            $filePath = $folder . "/entrada-{$ticket->short_code}.pdf";
            if (!file_exists($filePath)) {
                $pdf = Pdf::loadView('tickets.pdf', ['ticket' => $ticket])
                    ->setPaper('a4', 'portrait');
                $pdf->save($filePath);
            }
            $email->attach($filePath, [
                'as'   => "entrada-{$ticket->short_code}.pdf",
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}
