{{-- resources/views/filament/resources/evento-resource/pages/detalles.blade.php --}}
<x-filament::page>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <x-slot name="header">
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                {{ $record->nombre }}
            </h2>
        </x-slot>

        {{-- RECAUDACIÓN GLOBAL --}}
        <div tabindex="0" class="recaudacion-card">
            <div class="hidden sm:flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-purple-700">Recaudación global</h3>
                    <p class="text-purple-400 text-sm mt-1"># Unidades vendidas</p>
                </div>
                <div class="text-right">
                    <span class="font-extrabold text-3xl text-purple-700">${{ number_format($recaudacionTotal,2) }}</span>
                    <p class="text-purple-500 text-sm mt-1">{{ $ticketsVendidos }} de {{ $ticketsDisponibles }}</p>
                </div>
            </div>
            <div class="sm:hidden text-center">
                <span class="font-extrabold text-4xl text-purple-700">${{ number_format($recaudacionTotal,2) }}</span>
                <p class="font-semibold mt-3 text-lg text-purple-700">Recaudación global</p>
                <p class="text-purple-500 text-sm mt-1">{{ $ticketsVendidos }} de {{ $ticketsDisponibles }}</p>
            </div>
        </div>

        {{-- BOTONES DE ACCIÓN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-filament::button
                tag="a"
                :href="route('filament.admin.resources.eventos.gestionar-entradas',['record'=>$record->id])"
                icon="heroicon-o-pencil"
                class="btn-detalles"
            >Editar stock</x-filament::button>

            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl(['record'=>$record->id])"
                icon="heroicon-o-chart-bar"
                class="btn-detalles"
            >Reportes</x-filament::button>

            <x-filament::button
                tag="a"
                :href="route('filament.admin.resources.eventos.edit',['record'=>$record->id])"
                icon="heroicon-o-pencil-square"
                class="btn-detalles"
            >Editar evento</x-filament::button>

            <x-filament::button
                type="button"
                icon="heroicon-o-link"
                class="btn-detalles"
                x-on:click="mostrarModal = true"
            >Copiar link</x-filament::button>

            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\EventoResource\Pages\ListaDigital::getUrl(['record'=>$record->id])"
                icon="heroicon-o-list-bullet"
                class="btn-detalles"
            >Lista digital</x-filament::button>

            <x-filament::button
                type="button"
                icon="heroicon-o-x-circle"
                class="btn-detalles danger"
                wire:click="abrirPrimerModal"
            >Suspender evento</x-filament::button>
        </div>

        {{-- ... aquí sigue tu modal y toast exactamente igual ... --}}
    </div>
</x-filament::page>
