<?php

// app/Mail/InvitacionEnviada.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchasedTicket;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvitacionEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $invitacion;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @param PurchasedTicket $invitacion
     * @return void
     */
    public function __construct(PurchasedTicket $invitacion)
    {
        $this->invitacion = $invitacion;
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        Log::info("DEBUG - BUILD: El método build() ha sido llamado para la invitación #{$this->invitacion->id}");


        // --- INICIO DEL CAMBIO ---
        // 1. Leer el contenido del archivo SVG del QR
        $qrContent = null;
        if (Storage::disk('public')->exists($this->invitacion->qr_path)) {
            $qrContent = Storage::disk('public')->get($this->invitacion->qr_path);
        }

        // 2. Preparar el QR como un Data URI para incrustarlo en el HTML
        $qrCodeDataUri = $qrContent
            ? 'data:image/svg+xml;base64,' . base64_encode($qrContent)
            : null;
        // --- FIN DEL CAMBIO ---

        try {
            Log::info("DEBUG - BUILD: Intentando cargar la vista PDF.");
            $pdfContent = Pdf::loadView('emails.invitacion-pdf', [
                'invitacion' => $this->invitacion,
                'qrCodeDataUri' => $qrCodeDataUri, // 3. Pasamos el Data URI a la vista
            ])->output();
            Log::info("DEBUG - BUILD: Contenido del PDF generado.");
        } catch (\Exception $e) {
            Log::error("ERROR - BUILD: Falló la carga de la vista 'emails.invitacion-pdf'. Asegúrate de que el archivo existe. Mensaje: " . $e->getMessage());
            return;
        }
        Log::info("DEBUG - BUILD: Generando contenido del PDF.");
        $pdfContent = Pdf::loadView('emails.invitacion-pdf', [
            'invitacion' => $this->invitacion,
            'qrCodeDataUri' => $qrCodeDataUri,
        ])->output();
        Log::info("DEBUG - BUILD: Contenido del PDF generado.");

        // Preparamos el email
        $email = $this->subject('Tu Invitación para ' . $this->invitacion->evento->nombre)
            ->view('emails.invitacion');

        // Adjuntamos el PDF desde la data en memoria
        $email->attachData($pdfContent, 'invitacion.pdf', [
            'mime' => 'application/pdf',
        ]);
        Log::info("DEBUG - BUILD: Archivo PDF adjuntado.");

        return $email;
    }
}
