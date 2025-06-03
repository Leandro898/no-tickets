<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->from('leandcief@gmail.com', 'Nombre del sitio')
                    ->subject('Correo de Prueba Laravel')
                    ->view('test-mail')
                    ->with('data', $this->data);
    }
}
