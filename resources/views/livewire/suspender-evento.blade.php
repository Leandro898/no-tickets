<div>
    <x-filament::button
        type="button"
        icon="heroicon-o-x-circle"
        class="btn-detalles danger"
        wire:click="$set('mostrarModal', true)"
    >
        Suspender evento
    </x-filament::button>

    @if($mostrarModal)
        <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <h4 class="text-lg font-semibold mb-4 text-purple-800">¿Estás seguro?</h4>
                <p>Esta acción suspenderá el evento y no podrá vender más entradas.</p>
                <div class="mt-6 flex gap-2 justify-center">
                    <x-filament::button
                        color="danger"
                        wire:click="suspender"
                    >Sí, suspender</x-filament::button>
                    <x-filament::button
                        color="secondary"
                        wire:click="$set('mostrarModal', false)"
                    >Cancelar</x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
