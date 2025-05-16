<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle del evento
        </h2>
    </x-slot>

    <!-- Resumen general -->
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

    <!-- Botones de acción -->
    <div class="grid grid-cols-2 gap-4">
        <!-- Botón Editar evento -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-edit mr-2"></i> Editar evento
        </button>

        <!-- Botón Link -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-link mr-2"></i> Link
        </button>

        <!-- Botón Lista digital -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-list-alt mr-2"></i> Lista digital
        </button>

        <!-- Botón Enviar productos -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-gift mr-2"></i> Enviar productos
        </button>

        <!-- Botón Productos y cortesías del equipo -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-user-friends mr-2"></i> Productos y cortesías del equipo
        </button>

        <!-- Botón Suspender -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-ban mr-2"></i> Suspender
        </button>

        <!-- Botón Enviar cortesías -->
        <button class="bg-gray-800 hover:bg-gray-700 text-gray font-bold py-2 px-4 rounded">
            <i class="fas fa-gift mr-2"></i> Enviar cortesías
        </button>
    </div>
</x-filament::page>