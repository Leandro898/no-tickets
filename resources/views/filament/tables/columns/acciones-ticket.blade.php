@php
$record = $getRecord();
@endphp

<div x-data="{ confirm: false, loading: false }" class="flex gap-2">
    {{-- Modal --}}
    <div x-show="confirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-xl w-[90%] max-w-md">
            <h2 class="text-lg font-bold mb-2">¿Reenviar entrada?</h2>
            <p class="mb-4">¿Querés reenviar el ticket a <strong>{{ $record->order->buyer_email }}</strong>?</p>
            <div class="flex justify-end gap-2">
                <button @click="confirm = false" class="px-3 py-1 text-gray-700 border rounded">Cancelar</button>
                <button
                    @click="
                        loading = true;
                        $wire.reenviarEntrada({{ $record->id }}).then(() => {
                            loading = false;
                            confirm = false;
                        });
                    "
                    class="px-4 py-1 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2">
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                    </svg>
                    <span x-show="!loading">Enviar</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Botón principal --}}
    <button @click="confirm = true" class="text-sm text-gray-700 hover:underline">
        Reenviar
    </button>
</div>