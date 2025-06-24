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
    }

    .ticket-footer {
      margin-top: 20px;
      font-size: 12px;
      text-align: center;
      color: #888;
    }
  </style>
</head>
<body>
  <div class="ticket">
    <div class="ticket-header">
      <div class="ticket-title">Innova Ticket</div>
      <div class="ticket-event">#T-AY3P41</div>
    </div>
    <div class="ticket-body">
      <div class="ticket-info">
        <strong>Evento:</strong> Nombre del Evento<br />
        <strong>Tipo:</strong> Entrada General<br />
        <strong>Fecha:</strong> 30/03/2025 - 22:22hs<br />
        <strong>Lugar:</strong> Comodoro, Argentina<br />
        <strong>Comprador:</strong> Agustín Riquelme
      </div>

      <div class="qr-container">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=T-AY3P41" alt="QR Code" />
      </div>

      <div class="ticket-footer">
        Presenta esta entrada en la puerta del evento.<br />
        No es necesario imprimirla.
      </div>
    </div>
  </div>
</body>
</html>
