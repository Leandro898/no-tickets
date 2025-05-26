<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Entradas para {{ $evento->nombre }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
        <h1 class="text-2xl font-bold mb-4 text-center">Comprar Entradas para: {{ $evento->nombre }}</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('comprar.store', $evento->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Tu Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Tu Email:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            {{-- Campos opcionales para teléfono y DNI (Asegúrate de que existan en tu Order model y validación) --}}
            <div class="mb-4">
                <label for="buyer_phone" class="block text-gray-700 text-sm font-bold mb-2">Teléfono (opcional):</label>
                <input type="text" id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="buyer_dni" class="block text-gray-700 text-sm font-bold mb-2">DNI (opcional):</label>
                <input type="text" id="buyer_dni" name="buyer_dni" value="{{ old('buyer_dni') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <h2 class="text-xl font-semibold mt-6 mb-3">Selecciona tus Entradas:</h2>
            @forelse ($entradas as $entrada)
                <div class="border rounded-lg p-4 mb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold">{{ $entrada->nombre }}</h3>
                        <p class="text-gray-600">{{ $entrada->descripcion }}</p>
                        <p class="text-xl font-bold text-blue-600">ARS$ {{ number_format($entrada->precio, 2, ',', '.') }}</p>
                        <p class="text-gray-500 text-sm">Stock: {{ $entrada->stock_actual }}</p>
                        @if ($entrada->max_por_compra)
                            <p class="text-gray-500 text-sm">Máx. por compra: {{ $entrada->max_por_compra }}</p>
                        @endif
                        @if ($entrada->disponible_desde || $entrada->disponible_hasta)
                            <p class="text-gray-500 text-sm">Venta:
                                @if ($entrada->disponible_desde) Desde {{ $entrada->disponible_desde->format('d/m H:i') }} @endif
                                @if ($entrada->disponible_hasta) Hasta {{ $entrada->disponible_hasta->format('d/m H:i') }} @endif
                            </p>
                        @endif
                    </div>
                    <div class="w-24">
                        <label for="cantidad_{{ $entrada->id }}" class="sr-only">Cantidad para {{ $entrada->nombre }}</label>
                        <input type="number"
                               id="cantidad_{{ $entrada->id }}"
                               name="cantidades[{{ $entrada->id }}]"
                               min="0"
                               max="{{ $entrada->max_por_compra ?: $entrada->stock_actual }}"
                               value="{{ old('cantidades.'.$entrada->id, 0) }}"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-center">
                    </div>
                </div>
            @empty
                <p class="text-gray-600 text-center">No hay entradas disponibles para este evento en este momento.</p>
            @endforelse

            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline w-full mt-6">
                Proceder al Pago
            </button>
        </form>
    </div>
</body>
</html>