<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Exitosa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <h1 class="text-3xl font-bold text-green-600 mb-4">¡Compra Exitosa! 🎉</h1>
        <p class="text-gray-700 mb-2">Tu pago ha sido procesado correctamente.</p>
        <p class="text-gray-700 mb-4">Número de Orden: <span class="font-bold">{{ $order->id }}</span></p>

        <h2 class="text-xl font-semibold mt-6 mb-3">Tus Entradas:</h2>
        @if ($order->purchasedTickets->isNotEmpty())
            <ul class="list-disc list-inside text-left mx-auto max-w-xs">
                @foreach ($order->purchasedTickets as $ticket)
                    <li>Entrada #{{ $ticket->id }} (Código: {{ $ticket->unique_code }})</li>
                    {{-- Opcional: Mostrar la imagen del QR si quieres que el usuario la vea directamente --}}
                    {{-- <img src="{{ Storage::url($ticket->qr_path) }}" alt="QR Code" class="w-32 h-32 mx-auto my-2"> --}}
                @endforeach
            </ul>
            <p class="mt-4 text-sm text-gray-600">Revisa tu correo electrónico para los detalles y tus tickets con QR.</p>
        @else
            <p class="text-gray-600">Aún no se han generado los tickets para esta orden. Por favor, espera unos minutos o contacta a soporte.</p>
        @endif

        <a href="{{ route('eventos.show', $order->event_id) }}" class="mt-8 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Volver al Evento
        </a>
    </div>
</body>
</html>