<x-filament::page>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
    </div>

    {{-- Componente unificado --}}
    <livewire:entradas.gestion-entradas :evento_id="$evento->id" />

    {{-- Botón para ir a mapa de asientos --}}
    @if($evento->has_seats)
        <div class="mt-4">
            <a
                href="{{ route('filament.admin.resources.eventos.configure-seats', $evento->id) }}"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
                Configurar mapa de asientos
            </a>
        </div>
    @endif
</x-filament::page>
