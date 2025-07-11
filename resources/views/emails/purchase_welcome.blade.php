<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¡Bienvenido a Innova Ticket!</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; color: #1a202c; padding: 30px;">

  <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:30px;">
    <h2 style="color:#4c1d95;">¡Hola, {{ $order->buyer_full_name }}!</h2>
    <p>¡Gracias por tu compra en <strong>{{ config('app.name') }}</strong>! Hemos creado una cuenta para que puedas gestionar tus entradas.</p>
    <p>Por seguridad, te asignamos una contraseña temporal. Para acceder, es necesario que configures tu propia contraseña:</p>
    <p style="text-align:center; margin:20px 0;">
      <a href="{{ $resetUrl }}" style="
         display:inline-block; background-color:#7f3fd2; color:#fff;
         padding:12px 20px; text-decoration:none; border-radius:5px;
      ">
        Establecer mi contraseña
      </a>
    </p>
    <hr>
    <p>Adjuntamos las entradas que acabás de comprar como archivos PNG en este correo.</p>
    <p>¡Nos vemos en el evento!</p>
    <p>Saludos,<br>El equipo de {{ config('app.name') }}</p>
  </div>

</body>
</html>
