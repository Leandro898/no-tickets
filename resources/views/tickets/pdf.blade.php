{{-- resources/views/tickets/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entrada PDF</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
            color: #111827;
        }
        .ticket-container {
            max-width: 480px;
            margin: auto;
            background-color: #ffffff;
            border: 2px solid #8b5cf6;
            border-radius: 12px;
            padding: 24px;
            position: relative;
        }
        .ticket-code {
            position: absolute;
            top: 20px;
            right: 24px;
            font-size: 13px;
            font-weight: bold;
            color: #4b5563;
        }
        h2 {
            text-align: center;
            color: #7c3aed;
            margin-bottom: 20px;
        }
        .info p {
            font-size: 14px;
            margin: 4px 0;
        }
        .info p strong {
            color: #111827;
        }
        .qr {
            text-align: center;
            margin: 24px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        {{-- 1) MOSTRAR EL SHORT_CODE --}}
        <div class="ticket-code">#{{ $ticket->short_code }}</div>

        <h2>Innova Ticket</h2>

        <div class="info">
            <p><strong>Evento:</strong> {{ $ticket->order->event->nombre ?? 'Evento' }}</p>
            <p><strong>Tipo:</strong> {{ $ticket->ticket_type ?? 'Entrada' }}</p>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($ticket->order->event->fecha)->format('d/m/Y H:i') ?? '' }}</p>
            <p><strong>Lugar:</strong> {{ $ticket->order->event->ubicacion ?? '---' }}</p>
            <p><strong>Comprador:</strong> {{ $ticket->order->buyer_full_name ?? '' }}</p>
        </div>

        <div class="qr">
            {{-- 2) GENERAR QR “AL VUELO” CON EL SHORT_CODE --}}
            @php use SimpleSoftwareIO\QrCode\Facades\QrCode; @endphp
            {!! QrCode::format('png')
                   ->size(200)
                   ->generate($ticket->short_code) !!}
        </div>

        <div class="footer">
            Presenta esta entrada en el acceso al evento.<br>
            No es necesario imprimirla si tenés tu celular.
        </div>
    </div>
</body>
</html>
