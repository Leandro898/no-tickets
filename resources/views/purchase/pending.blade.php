<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Pendiente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <h1 class="text-3xl font-bold text-yellow-600 mb-4">Pago Pendiente ⏳</h1>
        <p class="text-gray-700 mb-4">Tu pago está pendiente de confirmación. Te notificaremos por correo electrónico cuando se apruebe.</p>
        <p class="text-gray-700 mb-2">Número de Orden: <span class="font-bold">{{ $order->id }}</span></p>
        <a href="{{ route('eventos.show', $order->event_id) }}" class="mt-8 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Volver al Evento
        </a>
    </div>
</body>
</html>