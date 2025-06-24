<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"></h2>
    </x-slot>

    <div x-data="{ mostrarModal: false, mostrarToast: false }" class="space-y-6">

        {{-- RECAUDACIÓN GLOBAL --}}
        <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold text-primary-500">Recaudación global</h3>
                    <p class="text-sm text-gray-500"># Unidades vendidas</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-primary-500">$0</span>
                    <span class="block text-sm text-gray-500">0 de 100</span>
                </div>
            </div>
        </div>

        {{-- BOTONES ACCIONES --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- FILA 1 --}}
            <x-filament::button
                :href="route('filament.admin.resources.eventos.gestionar-entradas', ['record' => $record->id])"
                color="info"
                icon="heroicon-o-pencil"
                tag="a"
                class="w-full h-12">
                Editar stock
            </x-filament::button>

            <x-filament::button
                :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl(['record' => $record->id])"
                color="primary"
                icon="heroicon-o-chart-bar"
                tag="a"
                class="w-full h-12">
                Reportes
            </x-filament::button>

            {{-- FILA 2 --}}
            <x-filament::button
                :href="route('filament.admin.resources.eventos.edit', ['record' => $record->id])"
                color="primary"
                icon="heroicon-o-pencil-square"
                tag="a"
                class="w-full h-12">
                Editar evento
            </x-filament::button>

            <x-filament::button
                x-on:click="mostrarModal = true"
                color="gray"
                icon="heroicon-o-link"
                tag="button"
                class="w-full h-12">
                Copiar link
            </x-filament::button>

            {{-- FILA 3 --}}
            <x-filament::button
                :href="App\Filament\Resources\EventoResource\Pages\ListaDigital::getUrl(['record' => $this->record->id])"
                icon="heroicon-o-list-bullet"
                tag="a"
            >
                Lista digital
            </x-filament::button>




            <x-filament::button
                color="gray"
                icon="heroicon-o-cube"
                type="button"
                class="w-full h-12">
                Enviar productos
            </x-filament::button>

            {{-- FILA 4 --}}
            <div class="md:col-span-2">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-users"
                    type="button"
                    class="w-full h-auto py-4 px-4 text-left flex items-start space-x-3">
                    <div>
                        <div class="font-medium">Productos y cortesías del equipo</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Habilita productos y cortesías para los miembros de tu equipo.
                        </div>
                    </div>
                </x-filament::button>
            </div>

            {{-- FILA 5 --}}
            <div x-data="{ cargando: false }" class="relative">
                <x-filament::button
                    color="danger"
                    icon="heroicon-o-x-circle"
                    type="button"
                    class="w-full h-12"
                    wire:click="abrirPrimerModal">
                    Suspender evento
                </x-filament::button>

                {{-- MODALES DE CONFIRMACIÓN DE SUSPENSIÓN --}}
                {{-- (sin cambios aquí para no duplicar contenido innecesario) --}}
            </div>

            {{-- FILA 6 --}}
            <x-filament::button
                color="gray"
                icon="heroicon-o-gift"
                type="button"
                class="w-full h-12">
                Enviar cortesías
            </x-filament::button>
        </div>

        {{-- MODAL COPIAR LINK --}}
        <div
            x-show="mostrarModal"
            x-transition
            x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md text-center">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Link del evento</h2>
                <input type="text"
                    value="{{ route('eventos.show', ['evento' => $record->id]) }}"
                    readonly
                    id="enlaceEvento"
                    class="w-full p-2 border rounded bg-gray-100 text-gray-700 mb-4" />

                <x-filament::button
                    color="primary"
                    icon="heroicon-o-clipboard"
                    @click="
                        navigator.clipboard.writeText(document.getElementById('enlaceEvento').value).then(() => {
                            mostrarToast = true;
                            mostrarModal = false;
                            setTimeout(() => mostrarToast = false, 2000);
                        });
                    ">
                    Copiar link
                </x-filament::button>
            </div>
        </div>

        {{-- TOAST DE CONFIRMACIÓN --}}
        <div
            x-show="mostrarToast"
            x-transition
            x-cloak
            class="fixed bottom-6 right-6 bg-primary-600 text-white px-4 py-3 rounded shadow-lg z-50">
            ✅ Link copiado al portapapeles
        </div>
    </div>
</x-filament::page>

