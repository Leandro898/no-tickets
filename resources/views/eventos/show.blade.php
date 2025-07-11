<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $evento->nombre }} – Detalles del Evento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-purple-50 to-purple-100">
    <div class="max-w-7xl mx-auto px-4 py-10">

        {{-- Botón Volver --}}
        <div class="flex justify-end mb-12">
            <a href="{{ route('eventos.index') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-purple-50 border border-purple-200
                      text-purple-700 font-semibold px-6 py-2 rounded-lg shadow transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
                Volver a la lista de eventos
            </a>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">

            {{-- Columna Izquierda --}}
            <div class="flex-1">
                {{-- Título --}}
                <h1 class="text-4xl font-extrabold text-purple-700 mb-8 tracking-tight">
                    {{ $evento->nombre }}
                </h1>

                {{-- Imagen centrada --}}
                <div class="w-full flex justify-center mb-8">
                    <div class="bg-purple-50 rounded-xl ring-2 ring-purple-200 shadow p-2">
                        @if ($evento->imagen)
                            <img src="{{ asset('storage/' . $evento->imagen) }}"
                                 alt="Banner del evento {{ $evento->nombre }}"
                                 class="w-full max-w-md max-h-[300px] object-contain rounded-lg" />
                        @else
                            <div class="w-64 h-40 bg-gray-100 flex items-center justify-center rounded-lg">
                                <span class="text-gray-400">Sin imagen disponible</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Contador --}}
                <div class="mb-8">
                    <div class="inline-block bg-purple-600 text-white font-semibold px-5 py-2 rounded-lg shadow">
                        {{-- Aquí iría tu componente de contador dinámico --}}
                        Faltan 29 Días | 9 Horas | 44 Minutos | 7 Segundos
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="bg-white rounded-2xl p-8 shadow mb-10">
                    <h2 class="text-2xl font-bold text-purple-700 mb-4">Acerca del evento</h2>
                    <div x-data="{ expanded: false }" class="text-gray-800 space-y-4">
                        {{-- Resumen --}}
                        <div x-show="!expanded">
                            {{ \Illuminate\Support\Str::limit($evento->descripcion, 240) }}
                            @if (strlen($evento->descripcion) > 240)
                                <button @click="expanded = true"
                                        class="mt-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold
                                               px-4 py-2 rounded-lg transition">
                                    Ver más
                                </button>
                            @endif
                        </div>
                        {{-- Texto completo --}}
                        <div x-show="expanded" x-cloak>
                            {{ $evento->descripcion }}
                            <button @click="expanded = false"
                                    class="mt-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold
                                           px-4 py-2 rounded-lg transition">
                                Ver menos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna Derecha --}}
            <div class="w-full lg:w-96 flex-shrink-0">

                {{-- Precio mínimo --}}
                <div class="bg-purple-600 text-white rounded-2xl p-8 text-center mb-8 shadow-lg">
                    <div class="text-lg font-medium">Entradas desde</div>
                    <div class="text-4xl font-extrabold my-2">
                        ${{ number_format($evento->entradas->min('precio'), 0, ',', '.') }}
                    </div>
                </div>

                {{-- Tarjetas de entradas con selector de cantidad --}}
                <div class="space-y-4 mb-8">
                    @foreach ($evento->entradas as $entrada)
                        <form action="{{ route('eventos.comprar.split.store', $evento) }}"
                              method="POST"
                              x-data="{ qty: 1 }"
                              class="bg-white rounded-xl p-5 shadow border border-purple-100">
                            @csrf
                            <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">

                            {{-- Cabecera: fecha y nombre --}}
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d M, H:i') }} hs
                                </div>
                                <div class="font-semibold text-purple-700">
                                    {{ $entrada->nombre }}
                                </div>
                            </div>

                            {{-- Controles de cantidad + subtotal + botón --}}
                            <div class="flex items-center justify-between">
                                {{-- Spinner de cantidad --}}
                                <div class="flex items-center space-x-2">
                                    <button type="button"
                                            @click="qty = Math.max(1, qty - 1)"
                                            class="w-8 h-8 flex items-center justify-center rounded bg-purple-100
                                                   text-purple-700 hover:bg-purple-200">−</button>
                                    <input type="number"
                                           name="cantidad"
                                           x-model.number="qty"
                                           min="1"
                                           max="{{ $entrada->stock_actual }}"
                                           class="w-12 text-center rounded border border-gray-300" />
                                    <button type="button"
                                            @click="qty = Math.min({{ $entrada->stock_actual }}, qty + 1)"
                                            class="w-8 h-8 flex items-center justify-center rounded bg-purple-100
                                                   text-purple-700 hover:bg-purple-200">+</button>
                                </div>

                                {{-- Subtotal dinámico y botón comprar --}}
                                <div class="flex items-center space-x-4">
                                    <div class="text-lg font-extrabold text-gray-900"
                                         x-text="`$${(qty * {{ $entrada->precio }}).toLocaleString('de-DE')}`">
                                        <!-- JS lo reemplaza -->
                                    </div>
                                    <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white font-bold
                                                   px-6 py-2 rounded-lg transition">
                                        Comprar
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>

                {{-- Acordeones extra --}}
                <div class="space-y-3">
                    @foreach (['Medios de pago', 'Estacionamiento'] as $info)
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center justify-between w-full px-4 py-3 bg-purple-50
                                           rounded-lg focus:outline-none text-purple-800 font-medium">
                                {{ $info }}
                                <svg :class="{ 'rotate-180': open }"
                                     class="w-5 h-5 transform transition-transform"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-collapse class="mt-2 text-gray-700 bg-white rounded-lg p-4 shadow">
                                Información sobre <strong>{{ strtolower($info) }}</strong>.
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</body>

</html>
