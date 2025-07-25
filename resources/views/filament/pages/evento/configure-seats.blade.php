{{-- resources/views/filament/pages/evento/configure-seats.blade.php --}}
<x-filament::page
    heading="Configurar Mapa de Asientos: {{ $record->nombre }}"
>
    <p class="text-sm text-gray-600 mb-6">
        Entradas a x : {{ $entriesCount }}
    </p>

    <x-filament::card class="overflow-hidden">
        <div id="seat-map-app" data-event-id="{{ $record->id }}" data-bg-image-url="{{ $record->bg_image_url }}"></div>
    </x-filament::card>

    @vite('resources/js/app.js')
</x-filament::page>
