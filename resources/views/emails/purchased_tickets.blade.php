<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reenvio de Entradas</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; color: #1a202c; padding: 30px;">

    <!-- ESTA PLANTILLA AHORA SIRVE PARA EL REENVIO DE EMAILS DESDE EL PANEL DE CLIENTES-->
    <div style="max-width: 600px; margin: auto; background-color: #fff; border-radius: 8px; padding: 30px;">
        <h2 style="color: #4c1d95;">¡Reenviamos tus entradas! 🎉</h2>

        <p>Hola <strong>{{ $order->buyer_full_name }}</strong>,</p>

        <p>¡Te llegaron las entradas nuevamente!</p>

        <p><strong>Has adquirido entradas para el evento:</strong> {{ $order->event->nombre }}<br>
        <strong>Fecha del evento:</strong> {{ \Carbon\Carbon::parse($order->event->fecha)->format('d/m/Y H:i') }}<br>
        <strong>Total pagado:</strong> {{ number_format($order->total_amount, 2, ',', '.') }} ARS</p>

        <hr>

        <h3>Tus entradas:</h3>
        <p>Hemos adjuntado <strong>{{ $purchasedTickets->count() }}</strong> archivos PNG a este correo. Cada archivo contiene el código QR de una de tus entradas.</p>

        <h4>Para acceder a tus entradas:</h4>
        <ol>
            <li>Abre los archivos adjuntos en este correo.</li>
            <li>Guarda los códigos QR en tu dispositivo.</li>
            <li>Presenta el QR correspondiente en la entrada del evento.</li>
        </ol>

        <hr>

        @if($resetUrl)
        <div style="margin: 20px 0;">
            <a href="{{ $resetUrl }}" style="background-color: #8b5cf6; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
                Establecer mi contraseña
            </a>
        </div>
        @endif

        <hr>

        <p>Si tenés alguna pregunta o necesitás ayuda, no dudes en contactarnos.</p>

        <p>¡Esperamos verte allí!</p>

        <p>Saludos,<br>
        El equipo de {{ config('app.name') }}</p>

        <p style="font-size: 12px; color: #6b7280;">Este es un correo automático. Por favor, no respondas a esta dirección.</p>
    </div>
</body>
</html>
