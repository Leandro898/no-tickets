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
                href="#"
                onclick="event.preventDefault(); copiarAlPortapapeles('{{ route('comprar.entrada', ['evento' => $record->id]) }}')"
                color="gray"
                icon="heroicon-o-link"
                tag="a"
                class="w-full h-12">
                Copiar link
            </x-filament::button>

            {{-- FILA 3 --}}
            <x-filament::button
                color="gray"
                icon="heroicon-o-list-bullet"
                type="button"
                class="w-full h-12">
                Lista digital
            </x-filament::button>

            <x-filament::button
                color="gray"
                icon="heroicon-o-cube"
                type="button"
                class="w-full h-12">
                Enviar productos
            </x-filament::button>

            {{-- FILA 4: PRODUCTOS Y CORTESÍAS --}}
            <div class="md:col-span-2">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-users"
                    type="button"
                    class="w-full h-auto py-4 px-4 text-left flex items-start space-x-3">
                    <div>
                        <div class="font-medium">Productos y cortesías del equipo</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Habilita productos y cortesías para los miembros de tu equipo.
                        </div>
                    </div>
                </x-filament::button>
            </div>

            {{-- FILA 5: SUSPENDER EVENTO --}}
            {{-- Modificamos el x-on:click para llamar al método de Livewire --}}
            <div x-data="{ cargando: false }" class="relative">
                <x-filament::button
                    color="danger"
                    icon="heroicon-o-x-circle"
                    type="button"
                    class="w-full h-12"
                    wire:click="abrirPrimerModal" {{-- Aquí llamamos al nuevo método PHP --}}>
                    Suspender evento
                </x-filament::button>

                <div
                    x-show="$wire.mostrarPrimerModal" {{-- Controlado por la propiedad de Livewire --}}
                    x-transition.opacity.duration.100ms
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
                    <div
                        x-show="$wire.mostrarPrimerModal"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                        @click.away="$wire.cerrarModales()" {{-- Para cerrar si se hace clic fuera --}}
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md mx-4 p-6 space-y-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">¿Estás seguro?</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                Esta acción no es reversible. Todas las compras realizadas serán reembolsadas y los clientes notificados sobre la suspensión del evento.
                            </p>

                            <div class="flex justify-end gap-3 mt-6">
                                <x-filament::button
                                    wire:click="cerrarModales" {{-- Cerrar ambos modales --}}
                                    color="gray"
                                    type="button"
                                    class="px-4">
                                    Cancelar
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="entenderConsecuencias" {{-- Abre el segundo modal --}}
                                    color="danger"
                                    type="button"
                                    class="px-4">
                                    Entiendo las consecuencias
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    x-show="$wire.mostrarSegundoModal" {{-- Controlado por la propiedad de Livewire --}}
                    x-transition.opacity.duration.100ms
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
                    <div
                        x-show="$wire.mostrarSegundoModal"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                        @click.away="$wire.cerrarModales()" {{-- Para cerrar si se hace clic fuera --}}
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md mx-4 p-6 space-y-4">
                        <form wire:submit.prevent="confirmarSuspension"> {{-- Formulario para la confirmación final --}}
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Para confirmar, escribe "Reembolsar todas las compras"</h2>
                            <input
                                type="text"
                                wire:model.defer="confirmacionTexto"
                                placeholder="Reembolsar todas las compras"
                                class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 mt-4 border-gray-300 dark:border-gray-600" />

                            <div class="flex justify-end gap-3 mt-6">
                                <x-filament::button
                                    wire:click="cerrarModales" {{-- Cerrar ambos modales --}}
                                    color="gray"
                                    type="button"
                                    class="px-4">
                                    Cancelar
                                </x-filament::button>

                                <x-filament::button
                                    type="submit" {{-- Este botón envía el formulario --}}
                                    color="danger"
                                    class="px-4">
                                    Suspender evento
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- FILA 6 --}}
            <x-filament::button
                color="gray"
                icon="heroicon-o-gift"
                type="button"
                class="w-full h-12">
                Enviar cortesías
            </x-filament::button>

        </div> {{-- Fin del grid de botones --}}

    </div> {{-- Fin del contenedor principal --}}

    {{-- TOAST de confirmación (mantener tal cual si funciona) --}}
    <div
        id="toast-copiado"
        x-show="mostrarToast"
        x-data="{ mostrarToast: false, mostrar() { this.mostrarToast = true; setTimeout(() => this.mostrarToast = false, 2500); } }"
        x-init="$watch('mostrarToast', value => value && mostrar())"
        x-transition
        x-cloak
        class="fixed bottom-6 right-6 flex items-center space-x-2 bg-primary-600 text-white px-4 py-3 rounded-lg shadow-lg z-50">
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