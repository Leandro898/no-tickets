<x-filament::page>
    <div class="space-y-6">

        {{-- Título --}}
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
            Entradas del evento: {{ $evento->nombre }}
        </h2>

        {{-- Botón Volver a Detalles --}}
        <x-filament::button
            :href="\App\Filament\Resources\EventoResource\Pages\EventoDetalles::getUrl(['record' => $evento->id])"
            color="primary"
            icon="heroicon-o-arrow-left"
            tag="a"
            size="sm"
        >
            Volver a Detalles
        </x-filament::button>

        {{-- Listado de Entradas --}}
        @if($evento->entradas && $evento->entradas->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($evento->entradas as $entrada)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-5 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nombre</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $entrada->nombre }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Stock actual</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $entrada->stock_actual }}</p>
                        </div>

                        {{-- Botón Editar --}}
                        <div>
                            <x-filament::button
                                :href="route('filament.admin.resources.entradas.edit', ['record' => $entrada->id])"
                                color="info"
                                icon="heroicon-o-pencil"
                                tag="a"
                                size="sm"
                                class="w-full"
                            >
                                Editar Entrada
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
                <p class="text-gray-600 dark:text-gray-300">No hay entradas registradas para este evento.</p>
            </div>
        @endif

    </div>
</x-filament::page>