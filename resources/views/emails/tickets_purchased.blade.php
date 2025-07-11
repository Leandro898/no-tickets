<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>¡Gracias por tu compra!</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; color: #1a202c; padding: 30px;">

  <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:30px;">
    <h2 style="color:#4c1d95;">¡Gracias por tu compra! 🎟️</h2>
    <p>Hola <strong>{{ $order->buyer_full_name }}</strong>,</p>
    <p>Has adquirido entradas para:</p>
    <ul>
      <li><strong>Evento:</strong> {{ $order->event->nombre }}</li>
      <li><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($order->event->fecha)->format('d/m/Y H:i') }}</li>
      <li><strong>Total pagado:</strong> {{ number_format($order->total_amount,2,',','.') }} ARS</li>
    </ul>
    <hr>
    <h3>Tus entradas:</h3>
    <p>Hemos adjuntado <strong>{{ $order->purchasedTickets->count() }}</strong> archivos PNG con los códigos QR.</p>
    <ol>
      <li>Abre los archivos adjuntos.</li>
      <li>Guarda los códigos QR.</li>
      <li>Muéstralos en la entrada del evento.</li>
    </ol>
    <p>Saludos,<br>El equipo de {{ config('app.name') }}</p>
  </div>

</body>
</html>
