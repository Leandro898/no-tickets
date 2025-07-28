<x-filament::page>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Entradas para: {{ $evento->nombre }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @forelse ($entradas as $entrada)
            <div class="bg-white rounded shadow p-4">
                <div class="font-semibold">{{ $entrada->nombre }}</div>
                <div class="text-lg mb-2">${{ number_format($entrada->precio,0) }}</div>
                <div>Stock: {{ $entrada->stock_inicial }}</div>
                <div class="mt-2 flex gap-2">
                    <a href="{{ route('filament.admin.resources.entradas.edit', $entrada) }}" class="text-indigo-600">Editar</a>
                </div>
            </div>
        @empty
            <div>No hay entradas aún.</div>
        @endforelse
    </div>

    <a href="{{ route('filament.admin.resources.entradas.create', ['evento_id' => $evento->id]) }}"
       class="px-4 py-2 bg-violet-600 text-white rounded hover:bg-violet-700">
        + Nueva Entrada
    </a>

    @if($entradas->count())
        <a href="{{ route('filament.admin.resources.eventos.configure-seats', $evento->id) }}"
           class="ml-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Configurar mapa de asientos
        </a>
    @endif
</x-filament::page>
