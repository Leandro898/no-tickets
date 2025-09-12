<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Invitación</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }

        .content {
            text-align: center;
        }

        .qr-code {
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $invitacion->evento->nombre }}</h1>
        </div>

        <div class="content">
            <p>¡Hola, <strong>{{ $invitacion->buyer_name }}</strong>!</p>
            <p class="pb-6">Esta es tu invitación. Presenta el siguiente código QR en el acceso al evento.</p>

            <div class="qr-code">
                @if ($qrCodeDataUri)
                <img src="{{ $qrCodeDataUri }}" alt="Código QR de tu entrada" style="width: 250px; height: 250px;">
                @else
                <p style="color: red; font-weight: bold;">Error: No se pudo cargar el código QR.</p>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Este es un ticket de acceso único y personal. No lo compartas.</p>
            <p>&copy; {{ date('Y') }} Tickets Pro.</p>
        </div>
    </div>
</body>

</html>