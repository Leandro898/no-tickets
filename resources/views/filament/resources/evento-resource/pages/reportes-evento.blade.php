<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes
        </h2>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="space-y-6">

        {{-- Botón Volver a Detalles --}}
        <x-filament::button
            :href="\App\Filament\Resources\EventoResource\Pages\EventoDetalles::getUrl(['record' => $this->record->id])"
            color="gray"
            icon="heroicon-o-arrow-left"
            tag="a"
        >
            Volver a detalles
        </x-filament::button>

        <!-- ESTE ES EL COMPONENTE LIVEWIRE PARA VER EL GRAFICO DE VENTAS
        Y TAMBIEN MUESTRA QRs GENERADOS Y ESCANEADOS -->
        @livewire('reportes-evento', ['eventoId' => $this->record->id])

    </div>
</x-filament::page>