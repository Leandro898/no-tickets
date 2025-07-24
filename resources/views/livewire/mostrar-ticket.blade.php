<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Entrada Digital</title>
  <style>
    body {
      margin: 0;
      font-family: 'Arial', sans-serif;
      background: #f9f9f9;
      display: flex;
      justify-content: center;
      padding: 20px;
    }
    .ticket {
      background: #fff;
      border: 2px solid #c2c2c2;
      border-left: 6px solid #7f3fd2;
      border-radius: 12px;
      width: 100%;
      max-width: 600px;
      padding: 20px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    .ticket-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .ticket-title {
      color: #7f3fd2;
      font-size: 20px;
      font-weight: bold;
    }
    .ticket-event {
      font-size: 18px;    /* Más grande el short_code */
      color: #4b5563;
      font-weight: bold;
      letter-spacing: 2px;
    }
    .ticket-body {
      margin-top: 10px;
    }
    .ticket-info {
      font-size: 14px;
      line-height: 1.6;
    }
    .qr-container {
      margin-top: 20px;
      display: flex;
      justify-content: center;
    }
    .qr-container img {
      width: 180px;
      height: 180px;
      border: 3px solid #7f3fd2;
      border-radius: 8px;
      background: #fff;
      padding: 4px;
    }
    .ticket-footer {
      margin-top: 20px;
      font-size: 12px;
      text-align: center;
      color: #888;
    }
    .btn-purple {
      display: inline-block;
      margin-top: 16px;
      padding: 8px 16px;
      background: #7f3fd2;
      color: #fff;
      border-radius: 4px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="ticket">
    <div class="ticket-header">
      <div class="ticket-title">Innova Ticket</div>
      <div class="ticket-event">#{{ $ticket->short_code }}</div>
    </div>
    <div class="ticket-body">
      <div class="ticket-info">
        <strong>Evento:</strong> {{ $ticket->order->event->nombre }}<br />
        <strong>Tipo:</strong> {{ $ticket->ticket_type }}<br />
        <strong>Fecha:</strong>
          {{ \Carbon\Carbon::parse($ticket->order->event->fecha)->format('d/m/Y H:i') }}<br />
        <strong>Lugar:</strong> {{ $ticket->order->event->ubicacion }}<br />
        <strong>Comprador:</strong> {{ $ticket->order->buyer_full_name }}
      </div>
      <div class="qr-container">
        <img src="{{ route('ticket.qr', $ticket->id) }}" alt="QR Code" />
      </div>
      <a href="{{ route('ticket.descargar', $ticket->id) }}" class="btn-purple">
        Descargar entrada (PDF)
      </a>
      <div class="ticket-footer">
        Presenta esta entrada en la puerta del evento.<br />
        No es necesario imprimirla.
      </div>
    </div>
  </div>
</body>
</html>
