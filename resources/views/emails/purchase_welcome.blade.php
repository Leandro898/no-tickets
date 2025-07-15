<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Te damos la Bienvenida a Tickets Pro!</title>
</head>
<body style="background: #f3f4f6; padding: 32px 0; font-family: 'Segoe UI', Arial, sans-serif; color: #1a202c;">

  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:540px;margin:auto;background:#fff;border-radius:18px;box-shadow:0 4px 24px rgba(76,29,149,0.07);">
    <tr>
      <td style="padding:40px 32px 24px 32px;text-align:center;">
        <h2 style="color:#7c3aed;font-size:2em;font-weight:900;margin:0 0 14px 0;">
          ¡Hola, {{ $order->buyer_full_name }}!
        </h2>
        <p style="font-size:1.13em;line-height:1.6;color:#333;">
          ¡Gracias por tu compra en <b>{{ config('app.name') }}</b>!<br>
          Hemos creado una cuenta para que puedas gestionar tus entradas.
        </p>
        <p style="color:#666;margin:26px 0 16px 0;">
          Por seguridad, te asignamos una contraseña temporal.<br>
          Para acceder, es necesario que configures tu propia contraseña:
        </p>
        <a href="{{ $resetUrl }}"
           style="display:inline-block;background:#7c3aed;color:#fff;font-weight:600;font-size:1.13em;
                  padding:15px 38px;border-radius:9px;text-decoration:none;box-shadow:0 2px 10px rgba(124,60,237,0.10);margin-bottom:34px;">
          Establecer mi contraseña
        </a>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 20px 32px;">
        <hr style="border:none;border-top:1px solid #ede9fe;margin:34px 0 22px 0;">
        <p style="color:#555;font-size:1em;margin-bottom:5px;">
          Adjuntamos las entradas que acabás de comprar como archivos <b>PDF</b> en este correo.
        </p>
        <p style="color:#555;font-size:1em;margin:16px 0 0 0;">
          ¡Nos vemos en el evento!
        </p>
        <p style="color:#a3a3a3;font-size:.98em;margin-top:20px;">
          Saludos,<br>
          <b>El equipo de {{ config('app.name') }}</b>
        </p>
      </td>
    </tr>
    <tr>
      <td style="background:#ede9fe;padding:14px 32px 12px 32px;border-radius:0 0 18px 18px;font-size:.98em;color:#6d28d9;text-align:center;">
        <!-- Footer simple sin enlace -->
      </td>
    </tr>
  </table>
</body>
</html>
