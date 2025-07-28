<div class="space-y-6">
    {{-- Botón Crear --}}
    <button
        wire:click="openCreate"
        class="w-full px-4 py-3 bg-purple-600 text-white rounded text-lg font-semibold hover:bg-purple-700"
    >
        + Nueva Entrada
    </button>

    {{-- Lista de Entradas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse ($entradas as $e)
            <div class="relative bg-white rounded shadow p-4">
                <button
                    wire:click="openEdit({{ $e->id }})"
                    class="absolute top-2 right-2 p-1 hover:bg-gray-100 rounded"
                    title="Editar"
                >✎</button>

                <div class="font-semibold">{{ $e->nombre }}</div>
                <div class="text-lg mb-2">${{ number_format($e->precio,0) }}</div>
                <div>Stock: {{ $e->stock_inicial }}</div>
            </div>
        @empty
            <div>No hay entradas aún.</div>
        @endforelse
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
                <h2 class="font-bold text-xl mb-4">
                    {{ $entrada_id ? 'Editar Entrada' : 'Crear Entrada' }}
                </h2>

                <div class="mb-4">
                    <label class="block text-sm mb-1">Nombre</label>
                    <input
                        wire:model.defer="nombre"
                        type="text"
                        class="w-full border p-2 rounded"
                        placeholder="Ej: General, VIP"
                    />
                    @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm mb-1">Precio</label>
                    <input
                        wire:model.defer="precio"
                        type="number"
                        class="w-full border p-2 rounded"
                        placeholder="Ej: 100"
                    />
                    @error('precio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm mb-1">Stock inicial</label>
                    <input
                        wire:model.defer="stock_inicial"
                        type="number"
                        class="w-full border p-2 rounded"
                        placeholder="Ej: 50"
                    />
                    @error('stock_inicial') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 border rounded hover:bg-gray-100"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="save"
                        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
                    >
                        {{ $entrada_id ? 'Guardar Cambios' : 'Crear Entrada' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
