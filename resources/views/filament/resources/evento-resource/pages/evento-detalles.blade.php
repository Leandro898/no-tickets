{{-- resources/views/filament/resources/evento-resource/pages/detalles.blade.php --}}
<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $record->nombre }}
        </h2>
    </x-slot>

    <div x-data="{ mostrarModal: false, mostrarToast: false }" class="space-y-12">

        {{-- RECAUDACIÓN GLOBAL --}}
        <div class="recaudacion-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="recaudacion-title">Recaudación global</h3>
                    <p class="recaudacion-subtitle"># Unidades vendidas</p>
                </div>
                <div class="text-right">
                    <span class="recaudacion-monto">$0</span>
                    <p class="recaudacion-tickets">0 de 100</p>
                </div>
            </div>
        </div>



        {{-- Espaciador de 8 rem (128px) --}}
        <div class="h-12"></div>

        {{-- BOTONES DE ACCIÓN --}}
        {{-- BOTONES DE ACCIÓN --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 justify-items-center">
            <x-filament::button tag="a" :href="route('filament.admin.resources.eventos.gestionar-entradas', ['record' => $record->id])" icon="heroicon-o-pencil" class="btn-detalles">
                Editar stock
            </x-filament::button>

            <x-filament::button tag="a" :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl(['record' => $record->id])" icon="heroicon-o-chart-bar" class="btn-detalles">
                Reportes
            </x-filament::button>

            <x-filament::button tag="a" :href="route('filament.admin.resources.eventos.edit', ['record' => $record->id])" icon="heroicon-o-pencil-square" class="btn-detalles">
                Editar evento
            </x-filament::button>

            <x-filament::button type="button" icon="heroicon-o-link" class="btn-detalles"
                x-on:click="mostrarModal = true">
                Copiar link
            </x-filament::button>

            <x-filament::button tag="a" :href="\App\Filament\Resources\EventoResource\Pages\ListaDigital::getUrl(['record' => $record->id])" icon="heroicon-o-list-bullet" class="btn-detalles">
                Lista digital
            </x-filament::button>

            <x-filament::button type="button" icon="heroicon-o-x-circle" color="danger" class="btn-detalles"
                wire:click="abrirPrimerModal">
                Suspender evento
            </x-filament::button>
        </div>



        {{-- MODAL: Copiar enlace --}}
        <div x-show="mostrarModal" x-cloak x-transition @click.self="mostrarModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-primary rounded-lg shadow-lg w-full max-w-md p-6 text-center">
                <h4 class="text-lg font-semibold mb-4">Link del evento</h4>
                <input x-ref="inputEl" type="text" readonly
                    value="{{ route('eventos.show', ['evento' => $record->id]) }}"
                    class="w-full border border-gray-300 rounded p-2 mb-4 bg-gray-50 text-gray-700" />
                <x-filament::button type="button" color="primary" icon="heroicon-o-clipboard"
                    x-on:click="
                        navigator.clipboard.writeText($refs.inputEl.value)
                            .then(() => {
                                mostrarToast = true;
                                mostrarModal = false;
                                setTimeout(() => mostrarToast = false, 2000);
                            });
                    ">
                    Copiar link
                </x-filament::button>
            </div>
        </div>

        {{-- TOAST: Confirmación de copia --}}
        <div x-show="mostrarToast" x-cloak x-transition.opacity
            class="fixed bottom-6 right-6 z-50 flex items-center bg-white border border-gray-200 px-4 py-2 rounded-lg shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8.414 8.414a1 1 0 01-1.414 0L3.293 10.707a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
            <span class="text-gray-800 font-medium">Link copiado al portapapeles</span>
        </div>

    </div>
</x-filament::page>
