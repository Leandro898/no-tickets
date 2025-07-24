<x-filament::page>
    <h2 class="text-2xl font-bold mb-4">Configurar Mapa de Asientos: {{ $evento->nombre }}</h2>
    <div id="seat-map-app" data-event-id="{{ $evento->id }}"></div>
    @vite('resources/js/app.js')
</x-filament::page>
