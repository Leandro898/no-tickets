<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprar Entradas - {{ $evento->nombre }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10 px-4">
    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg p-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Comprar Entradas para <span class="text-blue-600">{{ $evento->nombre }}</span></h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>¡Error!</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('comprar.store', ['evento' => $evento->id]) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-sm text-gray-700">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-blue-200" required>
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-blue-200" required>
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700">Teléfono (opcional)</label>
                    <input type="text" name="buyer_phone" value="{{ old('buyer_phone') }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700">DNI (opcional)</label>
                    <input type="text" name="buyer_dni" value="{{ old('buyer_dni') }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                </div>
            </div>

            <h2 class="text-xl font-semibold mt-8 mb-2 text-center">Selecciona tus Entradas</h2>

            @forelse ($entradas as $entrada)
                <div class="border rounded-lg p-4 mb-3">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <h3 class="text-lg font-bold">{{ $entrada->nombre }}</h3>
                            <p class="text-gray-600">{{ $entrada->descripcion }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-600 text-xl font-bold">ARS$ {{ number_format($entrada->precio, 2, ',', '.') }}</p>
                            <p class="text-sm text-gray-500">Stock: {{ $entrada->stock_actual }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-2">
                        <div class="text-sm text-gray-500">
                            @if ($entrada->max_por_compra)
                                Máx. por compra: {{ $entrada->max_por_compra }}
                            @endif
                            @if ($entrada->disponible_desde || $entrada->disponible_hasta)
                                <br>
                                Venta:
                                @if ($entrada->disponible_desde) desde {{ $entrada->disponible_desde->format('d/m H:i') }} @endif
                                @if ($entrada->disponible_hasta) hasta {{ $entrada->disponible_hasta->format('d/m H:i') }} @endif
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    onclick="decreaseCantidad('{{ $entrada->id }}')"
                                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded">
                                −
                            </button>

                            <input type="text"
                                   id="cantidad_{{ $entrada->id }}"
                                   name="cantidades[{{ $entrada->id }}]"
                                   value="{{ old('cantidades.'.$entrada->id, 0) }}"
                                   class="w-16 text-center border border-gray-300 rounded py-1 px-2 focus:outline-none"
                                   readonly>

                            <button type="button"
                                    onclick="increaseCantidad('{{ $entrada->id }}', {{ $entrada->max_por_compra ?: $entrada->stock_actual }})"
                                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded">
                                +
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-600">No hay entradas disponibles para este evento.</p>
            @endforelse

            <button type="submit" class="w-full py-3 mt-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded">
                Proceder al Pago
            </button>
        </form>
    </div>

    <script>
        function increaseCantidad(id, max) {
            const input = document.getElementById(`cantidad_${id}`);
            let val = parseInt(input.value) || 0;
            if (val < max) {
                input.value = val + 1;
            }
        }

        function decreaseCantidad(id) {
            const input = document.getElementById(`cantidad_${id}`);
            let val = parseInt(input.value) || 0;
            if (val > 0) {
                input.value = val - 1;
            }
        }
    </script>
</body>
</html>
