{{-- resources/views/filament/resources/evento-resource/pages/gestionar-entradas.blade.php --}}

@php
    use App\Filament\Resources\EventoResource\Pages\EventoDetalles;
    use App\Filament\Resources\EntradaResource;
@endphp

<x-filament::page>
    <div class="space-y-10">
        {{-- Header: botón volver --}}
        <x-filament::button
            :href="EventoDetalles::getUrl(['record' => $evento->id])"
            size="sm"
            icon="heroicon-o-arrow-left"
            tag="a"
            class="px-6 py-3 bg-[#7c3aed] text-white rounded-lg shadow-sm hover:bg-[#8b5cf6] transition;"
        >
            Ir a detalles
        </x-filament::button>

        {{-- Sub-título --}}
        <p class="text-xl font-semibold text-gray-700">
            Entradas del evento:
            <span class="text-[#7c3aed]">{{ $evento->nombre }}</span>
        </p>

        {{-- Grid de cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($evento->entradas as $entrada)
                <x-filament::card class="border border-[#d9d6fc] bg-gradient-to-br from-[#f5f3ff] to-white
                                        rounded-2xl shadow-sm hover:shadow-md transition">

                    {{-- info --}}
                    <div class="space-y-3">
                        <h2 class="text-lg font-bold text-[#7c3aed]">{{ $entrada->nombre }}</h2>

                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Precio:</span>
                            <span class="font-bold text-[#16a34a]">
                                ARS$ {{ number_format($entrada->precio, 2) }}
                            </span>
                        </div>

                        @if ($entrada->max_por_compra)
                            <div class="flex justify-between text-gray-600">
                                <span>Máx. por compra:</span>
                                <span class="font-semibold">{{ $entrada->max_por_compra }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-gray-600 h-6">
                            <span>Válido hasta:</span>
                            <span class="font-semibold">
                                {{ $entrada->disponible_hasta ? $entrada->disponible_hasta->format('d/m/Y H:i') : '' }}
                            </span>
                        </div>
                        

                        <div class="flex justify-between text-gray-700">
                            <span>Stock actual:</span>
                            <span class="font-semibold">{{ $entrada->stock_actual }}</span>
                        </div>
                    </div>

                    {{-- botón editar --}}
                    <x-filament::button
                        :href="EntradaResource::getUrl('edit', ['record' => $entrada->id])"
                        size="sm"
                        icon="heroicon-o-pencil"
                        tag="a"
                        class="w-full mt-6 bg-[#7c3aed] text-white rounded-lg
                               hover:bg-[#8b5cf6] shadow-sm transition"
                    >
                        Editar entrada
                    </x-filament::button>
                </x-filament::card>
            @empty
                <div class="col-span-full bg-[#f9fafb] border border-gray-200 rounded-2xl p-8 text-center text-gray-500">
                    No hay entradas registradas para este evento.
                </div>
            @endforelse
        </div>
    </div>
</x-filament::page>
