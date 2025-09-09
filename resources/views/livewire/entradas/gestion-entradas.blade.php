<div class="">

    {{-- Grid de tarjetas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- Tarjeta para agregar entrada --}}
        <div
            wire:click="openCreate"
            class="flex flex-col items-center justify-center border-2 border-dashed border-purple-400 rounded-lg cursor-pointer hover:bg-purple-50 min-h-[220px] transition-colors">
            <div class="text-4xl text-purple-500 mb-2">+</div>
            <span class="text-purple-700 font-semibold">Agregar nueva entrada</span>
        </div>

        {{-- Tarjetas de las entradas existentes --}}
        @forelse ($entradas as $e)
        <div class="relative bg-white border border-gray-100 rounded-2xl shadow-md flex flex-col justify-between min-h-[270px] p-0 overflow-hidden hover:shadow-lg transition-shadow">

            {{-- Botón editar --}}
            <button
                wire:click.stop="openEdit({{ $e->id }})"
                class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-300 transition"
                title="Editar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 5.487a2.2 2.2 0 113.11 3.11L9.067 19.5H5v-4.067l11.862-11.946z" />
                </svg>
            </button>

            {{-- Encabezado: Nombre tipo badge --}}
            <div class="pt-8 px-7 pb-1">
                <span class="inline-block bg-purple-50 text-purple-700 text-xs font-bold rounded-full px-3 py-1 mb-2 uppercase tracking-widest shadow-sm">
                    Entrada: {{ $e->nombre }}
                </span>

                {{-- Precio --}}
                <div class="text-4xl font-extrabold text-gray-900 mb-1 mt-2 text-center leading-tight">
                    ${{ number_format($e->precio,0) }}
                </div>
            </div>

            {{-- Datos: Stock y qty --}}
            <div class="flex flex-row justify-around px-7 py-2 text-gray-600 border-t border-gray-50">
                <div class="flex flex-col items-center">
                    <span class="text-xs font-bold uppercase">Disponibles</span>
                    <span class="text-base font-semibold">{{ $e->stock_actual }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-xs font-bold uppercase">Vendidas</span>
                    <span class="text-base font-semibold">{{ $e->stock_inicial - $e->stock_actual }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-xs font-bold uppercase">Total</span>
                    <span class="text-base font-semibold">{{ $e->stock_inicial ?? 'N/A' }}</span>
                </div>
            </div>

            {{-- Estado y estadísticas --}}
            <div class="flex justify-between items-center px-7 py-3 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex items-center gap-2 text-{{ ($e->stock_actual > 0) ? 'green' : 'red' }}-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="10" />
                    </svg>
                    <span class="text-xs font-semibold">
                        @if ($e->stock_actual > 0)
                        Disponible
                        @else
                        Sin stock
                        @endif
                    </span>
                </div>
                {{-- Se muestra el conteo de vendidas o el botón para reponer stock --}}
                @if ($e->stock_actual > 0)
                <span class="text-xs text-gray-500 font-semibold">{{ $e->stock_inicial - $e->stock_actual }} / {{ $e->stock_inicial ?? 'N/A' }} vendidas</span>
                @else
                <button
                    wire:click.stop="restock({{ $e->id }})"
                    class="px-4 py-1 text-xs font-bold text-white uppercase tracking-wider bg-purple-600 rounded-full hover:bg-purple-700 transition shadow-md">
                    Reponer Stock
                </button>
                @endif
            </div>
        </div>
        @empty
        {{-- No mostrar nada si no hay entradas, ya está la tarjeta para agregar --}}
        @endforelse

    </div>

    {{-- Modal de creación/edición --}}
    @if($showModal)
    <div
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[9999]"
        wire:click.away="closeModal">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-xl relative">
            <button wire:click="closeModal" class="absolute right-6 top-6 text-2xl text-gray-400 hover:text-gray-600 transition">
                &times;
            </button>
            <h2 class="font-bold text-3xl mb-6 text-gray-800">
                {{ $entrada_id ? 'Editar Entrada' : 'Nueva Entrada' }}
            </h2>
            <form wire:submit.prevent="save" class="space-y-6">
                {{-- Nombre y precio/stock --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">
                        Nombre de la Entrada <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model.defer="nombre"
                        type="text"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="Ej: General, VIP" />
                    @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-base font-semibold text-gray-700 mb-1">
                            Precio <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model.defer="precio"
                            type="number"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Ej: 100" />
                        @error('precio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-gray-700 mb-1">
                            Stock inicial <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model.defer="stock_inicial"
                            type="number"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Ej: 50"
                            {{ $entrada_id ? 'readonly' : '' }} />
                        @error('stock_inicial') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Nuevo campo para agregar más stock, solo visible en el modo de edición --}}
                @if ($entrada_id)
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">
                        Agregar más stock
                    </label>
                    <input
                        wire:model.defer="stock_a_agregar"
                        type="number"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="Ej: 20" />
                    @error('stock_a_agregar') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                @endif


                <div class="flex justify-end gap-2 mt-6">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-gray-800 font-semibold hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2 rounded-lg bg-purple-600 text-white font-bold hover:bg-purple-700 transition">
                        {{ $entrada_id ? 'Guardar Cambios' : 'Agregar Entrada' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>