<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px;">

    <h2>Hola {{ $nombre }},</h2>

    <p>¡Gracias por tu compra en <strong>Innova Ticket</strong>! Hemos creado una cuenta para que puedas gestionar tus entradas.</p>

    <p>Por seguridad, te asignamos una contraseña temporal. Para acceder, es necesario que configures tu propia contraseña.</p>

    <p>
        <a href="{{ $resetLink }}" style="display:inline-block; background-color:#7f3fd2; color:white; padding:12px 20px; text-decoration:none; border-radius:5px;">
            Establecer mi contraseña
        </a>
    </p>

    <p>Una vez que configures tu contraseña, podrás acceder y ver tus entradas desde tu panel de usuario.</p>

    <p>Gracias por confiar en nosotros.<br><strong>Innova Ticket</strong></p>

</body>
</html>
