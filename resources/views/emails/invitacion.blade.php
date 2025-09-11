{{-- resources/views/emails/invitacion.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>¡Invitación para {{ $invitacion->evento->nombre }}!</title>
</head>

<body style="margin:0;padding:0;background:#f5f0ff;font-family:Arial,sans-serif;color:#333;">
    <div style="max-width:600px;margin:0 auto;padding:20px;">

        {{-- HEADER --}}
        <div style="background:#7f3fd2;padding:15px;text-align:center;border-radius:8px 8px 0 0;">
            <span style="font-size:24px;font-weight:bold;color:#ffffff;line-height:1.2;">
                {{ config('app.name') }}
            </span>
        </div>

        {{-- CUADRO PRINCIPAL --}}
        <div style="background:#ffffff;border-radius:8px;border:1px solid #e0dbf5;overflow:hidden;">

            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                    {{-- DETALLES A LA IZQUIERDA --}}
                    <td width="60%" style="background:#ffffff;padding:20px;vertical-align:top;font-size:14px;color:#555;line-height:1.6;">
                        <h3 style="margin-top:0;margin-bottom:16px;font-size:18px;color:#7f3fd2;">
                            ¡Hola, {{ $invitacion->buyer_name }}!
                        </h3>
                        <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#555;">
                            Has sido registrado como invitado para el evento **{{ $invitacion->evento->nombre }}**.
                        </p>
                        <ul style="list-style:none;padding:0;margin:0 0 20px 0;font-size:14px;line-height:1.6;color:#555;">
                            <li>• <strong>Evento:</strong> {{ $invitacion->evento->nombre }}</li>
                            <li>• <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($invitacion->evento->fecha)->format('d/m/Y H:i') }}</li>
                            <li>• <strong>Ubicación:</strong> {{ $invitacion->evento->direccion }}</li>
                        </ul>
                        <div style="text-align:left;">
                            <a href="{{ route('ticket.validate', ['code' => $invitacion->unique_code]) }}"
                                style="display:inline-block;padding:12px 24px;background:#1dd570;color:#fff;
                        text-decoration:none;font-weight:bold;border-radius:6px;font-size:14px;"
                                target="_blank">
                                Ver mi invitación
                            </a>
                        </div>
                    </td>

                    {{-- IMAGEN A LA DERECHA --}}
                    <td width="40%" style="background:#ffffff;padding:20px;text-align:center;vertical-align:top;">
                        <img
                            src="{{ asset('storage/' . $invitacion->evento->imagen) }}"
                            alt="Poster {{ $invitacion->evento->nombre }}"
                            style="display:block;width:100%;max-width:160px;height:auto;border-radius:4px;" />
                    </td>
                </tr>
            </table>

        </div> {{-- fin cuadro principal --}}

        {{-- CONSEJOS --}}
        <div style="background:#ffffff;border-radius:8px;border:1px solid #e0dbf5;padding:20px;margin-top:20px;">
            <h3 style="margin-top:0;margin-bottom:12px;font-size:16px;color:#7f3fd2;">Algunos consejos</h3>
            <ul style="font-size:14px;color:#555;line-height:1.6;margin:0 0 0 20px;padding:0;">
                <li style="margin-bottom:8px;">Presenta el QR de la invitación desde tu celular al llegar.</li>
                <li style="margin-bottom:8px;">Te recordamos que esta invitación es personal e intransferible.</li>
                <li style="margin-bottom:8px;">Mantén tu código QR con buena iluminación para facilitar el escaneo.</li>
            </ul>
        </div>

        {{-- FOOTER --}}
        <div style="text-align:center;font-size:12px;color:#777;margin-top:20px;">
            <p style="margin:0 0 8px;">
                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </p>
            <p style="margin:0;">
                <a href="{{ url('/') }}" style="color:#7f3fd2;text-decoration:none;margin:0 6px;">Inicio</a>
                ·
                <a href="{{ url('/contacto') }}" style="color:#7f3fd2;text-decoration:none;margin:0 6px;">Contacto</a>
            </p>
        </div>

    </div>
</body>

</html>