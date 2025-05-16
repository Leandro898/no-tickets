<x-filament::page>
    <h2 class="text-2xl font-bold mb-4">Entradas del evento: {{ $evento->nombre }}</h2>

    <!-- Botón para ir a detalles -->
    <a href="{{ \App\Filament\Resources\EventoResource\Pages\EventoDetalles::getUrl(['record' => $evento->id]) }}"
       class="inline-block mt-4 bg-purple-600 hover:bg-purple-500 text-gray px-4 py-2 rounded">
        Volver a Detalles
    </a>

    <!-- Listado de entradas -->
    @foreach ($evento->entradas as $entrada)
        <div class="mb-4 p-4 border rounded bg-white dark:bg-gray-800">
            <p><strong>Nombre:</strong> {{ $entrada->nombre }}</p>
            <p><strong>Stock actual:</strong> {{ $entrada->stock_actual }}</p>

            <a href="{{ route('filament.admin.resources.entradas.edit', ['record' => $entrada->id]) }}"
               class="text-blue-500 hover:underline">Editar Entrada</a>
        </div>
    @endforeach
</x-filament::page>