<x-filament::page>
    <p class="text-sm text-gray-600 mb-6">
        Entradas a × : {{ $entriesCount }}
    </p>

    <x-filament::card class="overflow-hidden">
        <div id="seat-map-app" 
        data-evento-slug="{{ $evento->slug }}"
        data-initial-bg-image-url="{{ $evento->bg_image_url ?? '' }}"></div>
    </x-filament::card>

    @vite('resources/js/app.js')
</x-filament::page>
