<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class PurchaseWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $resetUrl;

    public function __construct(Order $order, string $resetUrl)
    {
        $this->order    = $order;
        $this->resetUrl = $resetUrl;
    }

    public function build()
    {
        return $this
            ->subject("¡Bienvenido a " . config('app.name') . "! 🎉")
            ->view('emails.purchase_welcome')
            ->with([
                'order'    => $this->order,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
