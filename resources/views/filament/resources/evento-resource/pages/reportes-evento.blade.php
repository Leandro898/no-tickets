<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes
        </h2>
    </x-slot>

    <!-- Botón "Volver a Detalles" -->
    <a href="{{ \App\Filament\Resources\EventoResource\Pages\EventoDetalles::getUrl(['record' => $this->record->id]) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray rounded-lg">
        <i class="fas fa-arrow-left mr-2"></i> Volver a detalles
    </a>

    <!-- Resumen general -->
    <div class="bg-gray-800 p-6 rounded-lg mb-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-bold text-purple-500">Recaudación</h3>
                <p class="text-sm text-gray-400">Todavía no tienes ventas suficientes para armar un gráfico.</p>
            </div>
            <div>
                <span class="text-2xl font-bold text-purple-500">$0</span>
            </div>
        </div>
    </div>

    <!-- Secciones dinámicas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Sección 1: QRs generados -->
        <div class="bg-gray-800 p-4 rounded-lg">
            <h3 class="text-xl font-bold">QRs generados</h3>
            <p class="text-sm text-gray-400">0</p>
        </div>

        <!-- Sección 2: QRs validados -->
        <div class="bg-gray-800 p-4 rounded-lg">
            <h3 class="text-xl font-bold">QRs validados</h3>
            <p class="text-sm text-gray-400">0</p>
        </div>

        <!-- Sección 3: Venta por producto -->
        <div class="bg-gray-800 p-4 rounded-lg">
            <h3 class="text-xl font-bold">Ventas por producto</h3>
            <p class="text-sm text-gray-400">Ventas separadas por entradas, consumos, mesas y combos.</p>
        </div>

        <!-- Sección 4: Venta por integrante -->
        <div class="bg-gray-800 p-4 rounded-lg">
            <h3 class="text-xl font-bold">Ventas por integrante</h3>
            <p class="text-sm text-gray-400">Obtén un detalle de qué ventas realizó cada miembro de tu equipo.</p>
        </div>

        <!-- Sección 5: Venta por medio de pago -->
        <div class="bg-gray-800 p-4 rounded-lg">
            <h3 class="text-xl font-bold">Ventas por medio de pago</h3>
            <p class="text-sm text-gray-400">Obtén un detalle de cuánto vendiste por cada medio de pago.</p>
        </div>
    </div>
</x-filament::page>