{{-- resources/views/filament/resources/evento-resource/pages/gestionar-entradas.blade.php --}}

@php
    use App\Filament\Resources\EntradaResource;
    use App\Filament\Resources\EventoResource\Pages\EventoDetalles;
@endphp

<x-filament::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">

            <x-filament::button :href="EventoDetalles::getUrl(['record' => $evento->id])" color="warning" icon="heroicon-o-arrow-left" tag="a" size="sm"
                class="px-6 py-4 bg-purple-700 hover:bg-purple-800 transition shadow-xl">
                Volver a Detalles
            </x-filament::button>
        </div>

        {{-- Subtítulo --}}
        <p class="text-lg font-medium pb-12">
            Entradas del evento: <span class="font-semibold">{{ $evento->nombre }}</span>
        </p>

        {{-- Grid de cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($evento->entradas as $entrada)
                <x-filament::card
                    class="flex flex-col justify-between border-2 border-purple-100 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-200 p-5">
                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-purple-800 mb-2">
                            {{ $entrada->nombre }}
                        </h2>

                        <div class="flex items-center justify-between text-gray-700">
                            <span class="font-semibold">Precio:</span>
                            <span class="text-lg font-bold text-green-600">ARS$
                                {{ number_format($entrada->precio, 2) }}</span>
                        </div>

                        @if ($entrada->max_por_compra)
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Máx. por compra:</span>
                                <span class="font-semibold">{{ $entrada->max_por_compra }}</span>
                            </div>
                        @endif

                        @if ($entrada->disponible_hasta)
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Válido hasta:</span>
                                <span class="font-semibold">
                                    {{ $entrada->disponible_hasta->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-gray-700">
                            <span>Stock actual:</span>
                            <span class="font-semibold">{{ $entrada->stock_actual }}</span>
                        </div>
                    </div>

                    <x-filament::button :href="\App\Filament\Resources\EntradaResource::getUrl('edit', ['record' => $entrada->id])" size="lg" icon="heroicon-o-pencil"
                        class="mt-6 w-full bg-purple-700 hover:bg-purple-800 transition shadow-lg" tag="a">
                        Editar Entrada
                    </x-filament::button>
                </x-filament::card>
            @empty
                <div class="col-span-full bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-600">
                    No hay entradas registradas para este evento.
                </div>
            @endforelse
        </div>
    </div>
</x-filament::page>
