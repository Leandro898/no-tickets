<x-filament::page>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes
        </h2>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="space-y-6">

        {{-- Botón Volver a Detalles --}}
        <x-filament::button
            :href="\App\Filament\Resources\EventoResource\Pages\EventoDetalles::getUrl(['record' => $this->record->id])"
            color="gray"
            icon="heroicon-o-arrow-left"
            tag="a"
        >
            Volver a detalles
        </x-filament::button>

        {{-- Recaudación Global --}}
        <x-filament::card class="bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold text-primary-500">Recaudación</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Todavía no tienes ventas suficientes para armar un gráfico.
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-primary-500">$0</span>
                </div>
            </div>
        </x-filament::card>

        {{-- Grilla de estadísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- QRs generados --}}
            <x-filament::card class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-primary-500">QRs generados</h3>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">0</p>
            </x-filament::card>

            {{-- QRs validados --}}
            <x-filament::card class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-primary-500">QRs validados</h3>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">0</p>
            </x-filament::card>

            {{-- Ventas por producto --}}
            <x-filament::card class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-primary-500">Ventas por producto</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Ventas separadas por entradas, consumos, mesas y combos.
                </p>
            </x-filament::card>

            {{-- Ventas por integrante --}}
            <x-filament::card class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-primary-500">Ventas por integrante</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Obtén un detalle de qué ventas realizó cada miembro de tu equipo.
                </p>
            </x-filament::card>

            {{-- Ventas por medio de pago --}}
            <x-filament::card class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 md:col-span-2">
                <h3 class="text-xl font-bold text-primary-500">Ventas por medio de pago</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Obtén un detalle de cuánto vendiste por cada medio de pago.
                </p>
            </x-filament::card>

        </div>
    </div>
</x-filament::page>