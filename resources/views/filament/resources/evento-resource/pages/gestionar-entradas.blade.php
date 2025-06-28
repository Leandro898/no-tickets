{{-- resources/views/filament/resources/evento-resource/pages/gestionar-entradas.blade.php --}}

@php
    use App\Filament\Resources\EntradaResource;
    use App\Filament\Resources\EventoResource\Pages\EventoDetalles;
@endphp

<x-filament::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            
            <x-filament::button
                :href="EventoDetalles::getUrl(['record' => $evento->id])"
                color="warning"
                icon="heroicon-o-arrow-left"
                tag="a"
                size="sm"
            >
                Volver a Detalles
            </x-filament::button>
        </div>

        {{-- Subtítulo --}}
        <p class="text-lg font-medium">
            Entradas del evento: <span class="font-semibold">{{ $evento->nombre }}</span>
        </p>

        {{-- Grid de cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($evento->entradas as $entrada)
                <x-filament::card class="flex flex-col justify-between">
                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $entrada->nombre }}
                        </h2>

                        <p class="text-md text-gray-600">
                            Precio:
                            <span class="font-medium">ARS$ {{ number_format($entrada->precio, 2) }}</span>
                        </p>

                        @if($entrada->max_por_compra)
                        <p class="text-md text-gray-600">
                            Máximo por compra:
                            <span class="font-medium">{{ $entrada->max_por_compra }}</span>
                        </p>
                        @endif

                        @if($entrada->disponible_hasta)
                        <p class="text-md text-gray-600">
                            Válido hasta:
                            <span class="font-medium">
                                {{ $entrada->disponible_hasta->format('d/m/Y H:i') }}
                            </span>
                        </p>
                        @endif

                        <p class="text-md text-gray-600">
                            Stock actual:
                            <span class="font-medium">{{ $entrada->stock_actual }}</span>
                        </p>
                    </br>
                    </div>

                    <x-filament::button
                        :href="\App\Filament\Resources\EntradaResource::getUrl('edit', ['record' => $entrada->id])"
                        size="sm"
                        icon="heroicon-o-pencil"
                        class="mt-4 w-full"
                        tag="a"
                    >
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
