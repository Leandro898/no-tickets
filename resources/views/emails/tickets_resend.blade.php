<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reenvío de Entradas</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; color: #1a202c; padding: 30px;">

  <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:30px;">
    <h2 style="color:#7c3aed;">¡Te reenviamos tus entradas! 🎟️</h2>
    <p>Hola <strong>{{ $order->buyer_full_name }}</strong>,</p>
    <p>
      Adjuntamos nuevamente tus entradas en formato <strong>PDF</strong>.<br>
      Recuerda que también podés verlas o descargarlas desde tu panel de usuario.
    </p>
    
    <div style="text-align:center;margin:28px 0;">
      <a href="{{ route('mis-entradas') }}"
         style="display:inline-block;padding:12px 30px;background:#1dd570;color:#fff;
                text-decoration:none;font-weight:bold;border-radius:6px;font-size:16px;">
        Ver mis entradas
      </a>
    </div>
    
    <h3 style="color:#7c3aed;margin-top:32px;">¿Cómo usar tus entradas?</h3>
    <ol style="padding-left:18px; color:#444;">
      <li>Descargá los archivos <strong>PDF adjuntos</strong> (uno por cada ticket).</li>
      <li>Presentá el PDF en tu celular o impreso en el evento (el QR está incluido).</li>
      <li>No compartas tu entrada, el QR es personal y único.</li>
    </ol>
    <p style="margin-top:32px;">¡Nos vemos en el evento!<br>El equipo de <strong>Tickets Pro</strong></p>
  </div>

</body>
</html>
