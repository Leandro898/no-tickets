<x-filament::page>
    {{-- Cabecera: título + botón volver alineado a la derecha --}}
    <div class="mb-8 flex justify-between items-center">
        
        <a
            href="{{ url('/admin/eventos/' . $evento->slug . '/detalles') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 text-gray-700 shadow transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver a detalles del evento
        </a>
    </div>

    {{-- Componente de gestión (puedes dejarlo debajo o integrarlo en las cards según tu lógica) --}}
    <livewire:entradas.gestion-entradas :evento_id="$evento->id" />

    {{-- Botón para ir a mapa de asientos --}}
    @if($evento->has_seats)
        <div class="mt-6">
            <a
                href="{{ route('filament.admin.resources.eventos.configure-seats', $evento->slug) }}"

                class="px-6 py-3 bg-green-600 text-white rounded shadow-md hover:bg-green-700 font-semibold transition inline-flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 018 0v2M7 7a4 4 0 118 0v2a4 4 0 11-8 0V7z" />
                </svg>
                Configurar mapa de asientos
            </a>
        </div>
    @endif
</x-filament::page>
