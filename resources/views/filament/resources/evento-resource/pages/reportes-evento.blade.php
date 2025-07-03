<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes
        </h2>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="space-y-6">

        {{-- Botón Volver a Detalles --}}
        <div class="flex justify-end">
            <x-filament::button
                :href="\App\Filament\Resources\EventoResource\Pages\EventoDetalles::getUrl(['record' => $this->record->id])"
                icon="heroicon-o-arrow-left"
                tag="a"
                class="px-6 py-4 bg-purple-700 hover:bg-purple-800 transition shadow-xl"
            >
                Ir a detalles
            </x-filament::button>
        </div>

        <!-- ESTE ES EL COMPONENTE LIVEWIRE PARA VER EL GRAFICO DE VENTAS
        Y TAMBIEN MUESTRA QRs GENERADOS Y ESCANEADOS -->
        @livewire('reportes-evento', ['eventoId' => $this->record->id])

    </div>
    <div class="espacio">
        
    </div>
</x-filament::page>