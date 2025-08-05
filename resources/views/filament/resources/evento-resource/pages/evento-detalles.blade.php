{{-- resources/views/filament/resources/evento-resource/pages/detalles.blade.php --}}
<x-filament::page>
    {{-- Contenedor Alpine con estado --}}
    <div x-data="{ mostrarModal: false, mostrarToast: false }">

        {{-- CONTENIDO PRINCIPAL --}}
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
                        <span
                            class="font-extrabold text-3xl text-purple-700">${{ number_format($recaudacionTotal, 2) }}</span>
                        <p class="text-purple-500 text-sm mt-1">{{ $ticketsVendidos }} de {{ $ticketsDisponibles }}</p>
                    </div>
                </div>
                <div class="sm:hidden text-center">
                    <span
                        class="font-extrabold text-4xl text-purple-700">${{ number_format($recaudacionTotal, 2) }}</span>
                    <p class="font-semibold mt-3 text-lg text-purple-700">Recaudación global</p>
                    <p class="text-purple-500 text-sm mt-1">{{ $ticketsVendidos }} de {{ $ticketsDisponibles }}</p>
                </div>
            </div>

            {{-- ESPACIADOR --}}

            <div class="h-8"></div> {{-- Espaciador de 80px de alto --}}

<<<<<<< HEAD
            {{-- BOTONES DE ACCIÓN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-filament::button tag="a" :href="route('filament.admin.resources.eventos.edit', ['record' => $record->id])" icon="heroicon-o-pencil-square"
                    class="btn-detalles">Editar evento</x-filament::button>

                <x-filament::button tag="a" :href="route('filament.admin.resources.eventos.gestionar-entradas', ['record' => $record->id])" icon="heroicon-o-pencil"
                    class="btn-detalles">Editar Entradas</x-filament::button>

                <x-filament::button tag="a" :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl([
                    'record' => $record->id,
                ])" icon="heroicon-o-chart-bar"
                    class="btn-detalles">Reportes</x-filament::button>

                <x-filament::button type="button" icon="heroicon-o-link" class="btn-detalles"
                    x-on:click="mostrarModal = true">Copiar link</x-filament::button>

                <x-filament::button tag="a" :href="\App\Filament\Resources\EventoResource\Pages\ListaDigital::getUrl(['record' => $record->id])" icon="heroicon-o-list-bullet"
                    class="btn-detalles">Lista digital</x-filament::button>

                {{-- <x-filament::button type="button" wire:click="suspender">
                    Suspender evento
                </x-filament::button> --}}
                <livewire:suspender-evento :eventoId="$record->id" />

=======

            {{-- BOTONES DE ACCIÓN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-filament::button tag="a" :href="route('filament.admin.resources.eventos.edit', ['record' => $record->slug])" icon="heroicon-o-pencil-square"
                    class="btn-detalles">
                    Editar evento
                </x-filament::button>



                <x-filament::button tag="a" :href="\App\Filament\Resources\EntradaResource\Pages\ManageEntradas::getUrl([
                    'slug' => $record->slug,
                ])" icon="heroicon-o-pencil" class="btn-detalles">
                    Editar Entradas
                </x-filament::button>









                <x-filament::button tag="a" :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl([
                    'record' => $record->slug,
                ])" icon="heroicon-o-chart-bar"
                    class="btn-detalles">Reportes</x-filament::button>

                <x-filament::button type="button" icon="heroicon-o-link" class="btn-detalles"
                    x-on:click="mostrarModal = true">Copiar link</x-filament::button>

                <x-filament::button tag="a" :href="\App\Filament\Resources\EventoResource\Pages\ListaDigital::getUrl([
                    'record' => $record->slug,
                ])" icon="heroicon-o-list-bullet"
                    class="btn-detalles">Lista digital</x-filament::button>

                {{-- <x-filament::button type="button" wire:click="suspender">
                    Suspender evento
                </x-filament::button> --}}
                <livewire:suspender-evento :eventoId="$record->id" />

>>>>>>> ajustes-seats
            </div>
        </div>

        {{-- MODAL: Copiar enlace (fuera del space-y-8) --}}
        <div x-show="mostrarModal" x-cloak @click.self="mostrarModal = false"
            x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center overflow-hidden w-screen h-screen">
            <div class="bg-white rounded-lg shadow-md w-full max-w-md p-6 text-center m-4">
                <h4 class="text-lg font-semibold mb-4 text-purple-800">Link del evento</h4>
                <input x-ref="inputEl" type="text" readonly
                    value="{{ route('eventos.show', ['evento' => $record->slug]) }}"
                    class="w-full border border-gray-300 rounded p-2 mb-4 bg-gray-50 text-gray-700 select-all cursor-pointer"
                    @click="$refs.inputEl.select()" />
                <x-filament::button type="button" color="primary" icon="heroicon-o-clipboard" class="w-full bg-primary"
                    x-on:click="
                        navigator.clipboard.writeText($refs.inputEl.value)
                            .then(() => {
                                mostrarToast = true;
                                mostrarModal = false;
                                setTimeout(() => mostrarToast = false, 2000);
                            });
                    ">Copiar
                    link</x-filament::button>
            </div>
        </div>

        {{-- TOAST: Confirmación de copia (fuera del space-y-8) --}}
        <div x-show="mostrarToast" x-cloak x-transition.opacity.duration.700ms
            class="fixed bottom-6 right-6 z-50 flex items-center bg-white bg-opacity-95 backdrop-blur-sm border border-green-400 px-5 py-3 rounded-lg shadow-lg ring-1 ring-green-500/40"
            style="min-width: 240px;">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mr-3 flex-shrink-0"
                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8.414 8.414a1 1 0 01-1.414 0L3.293 10.707a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
            <span class="text-green-900 font-semibold tracking-wide select-none">Link copiado al portapapeles</span>
        </div>

    </div>
</x-filament::page>
