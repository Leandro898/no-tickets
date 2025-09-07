<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; padding: 25px; border: 1px solid #ddd; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #555; line-height: 1.6; }
        ul { list-style: none; padding: 0; text-align: left; margin: 20px auto; max-width: 400px; }
        li { margin-bottom: 10px; font-size: 1.1em; }
        li strong { color: #333; display: inline-block; width: 120px; }
        .status-message { margin-top: 25px; padding: 15px; border-radius: 8px; font-size: 1.2em; font-weight: bold; }
        .status-valid { background-color: #d4edda; border: 1px solid #28a745; color: #155724; }
        .status-invalid { background-color: #f8d7da; border: 1px solid #dc3545; color: #721c24; }
        .scan-button {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2em;
            margin-top: 30px;
            transition: background-color 0.3s ease;
        }
        .scan-button:hover {
            background-color: #0056b3;
        }
        .scan-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .info-block {
            background-color: #f0f8ff;
            border: 1px solid #b0e0e6;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            color: #4682b4;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalles del Ticket</h1>

        @if(isset($ticket))
            <ul>
                <li><strong>Código:</strong> {{ $ticket->unique_code }}</li>
                <li><strong>Evento:</strong> {{ $ticket->entrada->evento->nombre ?? 'N/A' }}</li>
                <li><strong>Tipo de Entrada:</strong> {{ $ticket->entrada->nombre ?? 'N/A' }}</li>
                <li><strong>Fecha Evento:</strong> {{ $ticket->entrada->evento->fecha_evento ? \Carbon\Carbon::parse($ticket->entrada->evento->fecha_evento)->format('d/m/Y H:i') : 'N/A' }}</li>
                <li><strong>Comprador:</strong> {{ $ticket->order->buyer_name ?? 'N/A' }} ({{ $ticket->order->buyer_email ?? 'N/A' }})</li>
            </ul>

            <div class="status-message {{ $isValid ? 'status-valid' : 'status-invalid' }}">
                <p><strong>Estado:</strong> {{ $statusMessage }}</p>
                @if($isValid)
                    <p>¡Este ticket es válido!</p>
                @else
                    <p>¡ACCESO DENEGADO!</p>
                @endif
            </div>

            @if($isValid)
                <button id="scanButton" class="scan-button">Marcar Ticket como Usado</button>
                <div class="info-block">
                    <p>Presione este botón SÓLO si confirma que el ticket está siendo usado y la persona está ingresando al evento.</p>
                    <p>Una vez usado, no se podrá volver a utilizar.</p>
                </div>
            @else
                 <div class="info-block">
                    <p>Este ticket no puede ser utilizado.</p>
                 </div>
            @endif

        @else
            <h2 class="status-message status-invalid">Ticket no encontrado.</h2>
            <p>El código proporcionado no corresponde a ningún ticket válido en nuestro sistema.</p>
        @endif
    </div>

    {{-- Script para la funcionalidad del botón "Marcar Ticket como Usado" --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#scanButton').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Procesando...');

                // Asegúrate de que esta URL coincida con tu ruta POST en web.php
                $.ajax({
                    url: '{{ route('ticket.scan', ['code' => $ticket->unique_code]) }}', // <-- ESTA ES LA RUTA DE API
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}' // Laravel CSRF token para proteger la ruta POST
                    },
                    success: function(response) {
                        alert(response.message);
                        if (response.status === 'success') {
                            // Actualizar la UI para reflejar que fue usado
                            $('.status-message')
                                .removeClass('status-valid')
                                .addClass('status-invalid')
                                .html('<p><strong>Estado:</strong> Este ticket ya ha sido utilizado.</p><p>¡ACCESO DENEGADO!</p>'); // Cambia el mensaje
                            button.hide(); // Oculta el botón una vez usado
                            $('.info-block').html('<p>Este ticket ya fue utilizado y no puede ser usado de nuevo.</p>');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido al validar el ticket.';
                        alert(errorMessage);
                        button.prop('disabled', false).text('Marcar Ticket como Usado'); // Habilitar de nuevo en caso de error de red o similar
                    }
                });
            });
        });
    </script>
</body>
</html>