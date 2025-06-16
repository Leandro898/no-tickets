<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eventos Disponibles</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Eventos Disponibles</h1>

    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($eventos as $evento)
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $evento->nombre }}</h2>
                <p class="text-gray-700 mb-4">{{ \Illuminate\Support\Str::limit($evento->descripcion, 100) }}</p>
                <p class="text-sm text-gray-600 mb-2"><strong>Ubicación:</strong> {{ $evento->ubicacion }}</p>
                <p class="text-sm text-gray-600 mb-4"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') }}</p>

                <a href="{{ route('eventos.comprar', ['evento' => $evento->id]) }}"
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Ver Entradas
                </a>
            </div>
        @endforeach
    </div>
</body>
</html>
