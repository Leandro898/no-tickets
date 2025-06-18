<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $evento->nombre }} - Detalles del Evento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <a href="/" class="text-blue-600 hover:underline mb-6 inline-block"> Volver a la lista de eventos</a>

        <div class="flex flex-col md:flex-row gap-8 mb-8">
            <div class="md:w-1/2">
                @if ($evento->imagen)
                    <img src="{{ asset('storage/' . $evento->imagen) }}" alt="Imagen del Evento: {{ $evento->nombre }}" class="rounded-lg shadow-lg w-full h-auto object-cover">
                @else
                    <img src="https://placehold.co/600x400/E0E0E0/6C6C6C?text=Imagen+del+Evento" alt="Placeholder de Imagen" class="rounded-lg shadow-lg w-full h-auto object-cover">
                @endif
            </div>
            <div class="md:w-1/2">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-4">{{ $evento->nombre }}</h1>
                <p class="text-gray-700 text-lg mb-4">{{ $evento->descripcion }}</p>
                
                <div class="mb-4">
                    <p class="text-gray-800 font-semibold flex items-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Ubicación: <span class="ml-1 font-normal">{{ $evento->ubicacion }}</span>
                    </p>
                    <p class="text-gray-800 font-semibold flex items-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Fecha Inicio: <span class="ml-1 font-normal">{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') }}</span>
                    </p>
                    <p class="text-gray-800 font-semibold flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Fecha Fin: <span class="ml-1 font-normal">{{ \Carbon\Carbon::parse($evento->fecha_fin)->format('d/m/Y H:i') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <hr class="my-8 border-gray-300">

        <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Entradas Disponibles</h2>
        @if ($evento->entradas->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($evento->entradas as $entrada)
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $entrada->nombre }}</h3>
                        <p class="text-gray-600 mb-3">{{ $entrada->descripcion }}</p>
                        <p class="text-2xl font-extrabold text-blue-600 mb-3">ARS$ {{ number_format($entrada->precio, 2, ',', '.') }}</p>
                        <p class="text-gray-500 text-sm mb-2">Stock disponible: {{ $entrada->stock_actual }}</p>
                        @if ($entrada->max_por_compra)
                            <p class="text-gray-500 text-sm mb-2">Máximo por compra: {{ $entrada->max_por_compra }}</p>
                        @endif
                        @if ($entrada->disponible_desde || $entrada->disponible_hasta)
                            <p class="text-gray-500 text-sm mb-4">Venta:
                                @if ($entrada->disponible_desde) Desde {{ $entrada->disponible_desde->format('d/m H:i') }} @endif
                                @if ($entrada->disponible_hasta) Hasta {{ $entrada->disponible_hasta->format('d/m H:i') }} @endif
                            </p>
                        @endif

                        @if ($entrada->stock_actual > 0)
                            {{-- Validaciones de fecha en el frontend --}}
                            @php
                                $now = \Carbon\Carbon::now();
                                $saleActive = true;
                                if ($entrada->disponible_desde && $now->lt($entrada->disponible_desde)) {
                                    $saleActive = false;
                                }
                                if ($entrada->disponible_hasta && $now->gt($entrada->disponible_hasta)) {
                                    $saleActive = false;
                                }
                            @endphp

                            @if ($saleActive)
                                {{-- ESTE ES EL ENLACE CLAVE: Apunta a la ruta 'comprar.entrada' y le pasa el ID del evento --}}
                                <a href="{{ route('eventos.comprar.split', ['evento' => $evento->id]) }}"
                                   class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg text-center w-full transition-colors duration-300">
                                    Comprar Entradas
                                </a>
                            @else
                                <span class="inline-block bg-gray-400 text-white font-bold py-3 px-6 rounded-lg text-center w-full cursor-not-allowed">
                                    Venta No Disponible
                                </span>
                            @endif

                        @else
                            <span class="inline-block bg-red-500 text-white font-bold py-3 px-6 rounded-lg text-center w-full cursor-not-allowed">
                                Agotado
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 text-center">No hay entradas disponibles para este evento aún.</p>
        @endif
    </div>
</body>
</html>

