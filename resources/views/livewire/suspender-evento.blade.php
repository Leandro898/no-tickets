<div>
    {{-- Botón principal en tu grid --}}
    <x-filament::button
        type="button"
        color="danger"
        wire:click="abrirModal"
        class="btn-detalles"
    >
        Suspender evento
    </x-filament::button>

    {{-- Modal de confirmación Livewire --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <h4 class="text-lg font-semibold mb-4 text-red-700">
                    ¿Estás seguro de eliminar este evento?
                </h4>
                <p class="mb-4">Esta acción es irreversible.</p>
                <div class="flex justify-center gap-2">
                    <x-filament::button
                        type="button"
                        color="danger"
                        wire:click="suspender"
                    >
                        Sí, eliminar
                    </x-filament::button>
                    <x-filament::button
                        type="button"
                        wire:click="cancelar"
                    >
                        Cancelar
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
