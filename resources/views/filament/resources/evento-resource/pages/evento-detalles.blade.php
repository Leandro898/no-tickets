<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle del evento
        </h2>
    </x-slot>

    {{-- Recaudación global --}}
    <div class="bg-gray-800 p-6 rounded-lg mb-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-bold text-purple-500">Recaudación global</h3>
                <p class="text-sm text-gray-400"># Unidades vendidas</p>
            </div>
            <div>
                <span class="text-2xl font-bold text-purple-500">$0</span>
                <span class="text-sm text-gray-400">0 de 100</span>
            </div>
        </div>
    </div>

    {{-- Botones principales --}}
    <div class="flex flex-wrap gap-3 mb-6">
        {{-- Botón Editar Stock --}}
        <a href="{{ route('filament.admin.resources.eventos.gestionar-entradas', ['record' => $evento->id]) }}"
            class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-edit mr-2"></i> Editar Stock
        </a>

        {{-- Botón Reportes --}}
        <a href="{{ \App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl(['record' => $evento->id]) }}"
            class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-chart-bar mr-2"></i> Reportes
        </a>

        {{-- Botón Editar Evento --}}
        <a href="{{ route('filament.admin.resources.eventos.edit', ['record' => $evento->id]) }}"
            class="bg-blue-600 hover:bg-blue-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-edit mr-2"></i> Editar Evento
        </a>

        {{-- Otros botones --}}
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-link mr-2"></i> Link
        </button>

        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-list-alt mr-2"></i> Lista digital
        </button>

        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-gift mr-2"></i> Enviar productos
        </button>

        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-user-friends mr-2"></i> Productos y cortesías del equipo
        </button>

        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-ban mr-2"></i> Suspender
        </button>

        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-gift mr-2"></i> Enviar cortesías
        </button>
    </div>
</x-filament::page>
