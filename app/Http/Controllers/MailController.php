<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class MailController extends Controller
{
    public function index()
    {
        try {
            Mail::to('neuquenrenault@gmail.com')->send(new TestMail([
                'title' => 'El Título',
                'body' => 'El cuerpo del mensaje',
            ]));

            return 'Correo enviado correctamente.';
        } catch (\Exception $e) {
            return 'Error al enviar correo: ' . $e->getMessage();
        }
    }
}
