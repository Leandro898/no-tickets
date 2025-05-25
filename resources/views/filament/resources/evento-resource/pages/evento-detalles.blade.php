<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
           
        </h2>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="space-y-6">

        {{-- RECAUDACIÓN GLOBAL --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold text-primary-500">Recaudación global</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400"># Unidades vendidas</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-primary-500">$0</span>
                    <span class="block text-sm text-gray-500 dark:text-gray-400">0 de 100</span>
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
                class="w-full h-12"
            >
                Editar stock
            </x-filament::button>

            <x-filament::button
                :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl(['record' => $record->id])"
                color="primary"
                icon="heroicon-o-chart-bar"
                tag="a"
                class="w-full h-12"
            >
                Reportes
            </x-filament::button>

            {{-- FILA 2 --}}
            <x-filament::button
                :href="route('filament.admin.resources.eventos.edit', ['record' => $record->id])"
                color="primary"
                icon="heroicon-o-pencil-square"
                tag="a"
                class="w-full h-12"
            >
                Editar evento
            </x-filament::button>

            <x-filament::button
                href="#"
                onclick="event.preventDefault(); copiarAlPortapapeles('{{ route('comprar.entrada', ['evento' => $record->id]) }}')"
                color="gray"
                icon="heroicon-o-link"
                tag="a"
                class="w-full h-12"
            >
                Copiar link
            </x-filament::button>

            {{-- FILA 3 --}}
            <x-filament::button
                color="gray"
                icon="heroicon-o-list-bullet"
                type="button"
                class="w-full h-12"
            >
                Lista digital
            </x-filament::button>

            <x-filament::button
                color="gray"
                icon="heroicon-o-cube"
                type="button"
                class="w-full h-12"
            >
                Enviar productos
            </x-filament::button>

            {{-- FILA 4: PRODUCTOS Y CORTESÍAS --}}
            <div class="md:col-span-2">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-users"
                    type="button"
                    class="w-full h-auto py-4 px-4 text-left flex items-start space-x-3"
                >
                    <div>
                        <div class="font-medium">Productos y cortesías del equipo</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Habilita productos y cortesías para los miembros de tu equipo.
                        </div>
                    </div>
                </x-filament::button>
            </div>

            {{-- FILA 5: SUSPENDER EVENTO --}}
            <div x-data="{ confirmarEliminacion: false, cargando: false }" class="relative">
                <x-filament::button
                    color="danger"
                    icon="heroicon-o-x-circle"
                    type="button"
                    class="w-full h-12"
                    x-on:click="
                        confirmarEliminacion = true;
                        cargando = true;
                        setTimeout(() => cargando = false, 400);
                    "
                >
                    Suspender evento
                </x-filament::button>

                <!-- Modal -->
                <div
                    x-show="confirmarEliminacion"
                    x-transition.opacity.duration.100ms
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
                >
                    <div
                        x-show="confirmarEliminacion"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                        @click.away="confirmarEliminacion = false"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md mx-4 p-6 space-y-4"
                    >
                        <template x-if="!cargando">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800 dark:text-white">¿Suspender evento?</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                    Esta acción suspenderá el evento permanentemente. ¿Estás seguro de continuar?
                                </p>

                                <div class="flex justify-end gap-3 mt-6">
                                    <x-filament::button
                                        @click="confirmarEliminacion = false"
                                        color="gray"
                                        type="button"
                                        class="px-4"
                                    >
                                        Cancelar
                                    </x-filament::button>

                                    <form wire:submit.prevent="eliminarEvento">
                                        <x-filament::button
                                            type="submit"
                                            color="danger"
                                            class="px-4"
                                        >
                                            Sí, suspender
                                        </x-filament::button>
                                    </form>
                                </div>
                            </div>
                        </template>

                        <template x-if="cargando">
                            <div class="flex flex-col items-center justify-center py-6">
                                <svg class="animate-spin h-8 w-8 text-gray-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Preparando para suspender...</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- FILA 6 --}}
            <x-filament::button
                color="gray"
                icon="heroicon-o-gift"
                type="button"
                class="w-full h-12"
            >
                Enviar cortesías
            </x-filament::button>

        </div> {{-- Fin del grid de botones --}}

    </div> {{-- Fin del contenedor principal --}}

    {{-- TOAST de confirmación --}}
    <div
        id="toast-copiado"
        x-show="mostrarToast"
        x-data="{ mostrarToast: false, mostrar() { this.mostrarToast = true; setTimeout(() => this.mostrarToast = false, 2500); } }"
        x-init="$watch('mostrarToast', value => value && mostrar())"
        x-transition
        x-cloak
        class="fixed bottom-6 right-6 flex items-center space-x-2 bg-primary-600 text-white px-4 py-3 rounded-lg shadow-lg z-50"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L9 13.414l4.707-4.707z" clip-rule="evenodd" />
        </svg>
        <span>URL copiada al portapapeles</span>
    </div>

    {{-- SCRIPT COPIAR AL PORTAPAPELES --}}
    <script>
        function copiarAlPortapapeles(url) {
            navigator.clipboard.writeText(url)
                .then(() => {
                    const toast = document.getElementById('toast-copiado');
                    if (toast && toast.__x) {
                        toast.__x.$data.mostrarToast = true;
                    }
                })
                .catch(() => alert('Error al copiar la URL'));
        }
    </script>
</x-filament::page>
