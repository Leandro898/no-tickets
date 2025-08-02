<x-filament::page>
    <div class="flex flex-col items-center justify-center min-h-[88vh] pt-8">

        <x-filament::button color="primary" icon="heroicon-o-eye" tag="a" target="_blank"
            href="{{ url('/eventos/' . $evento->slug . '/checkout-seats') }}" class="mb-6">
            Ver evento en el front
        </x-filament::button>

        {{-- Card centrada y elegante --}}
        <x-filament::card class="overflow-visible w-full max-w-3xl shadow-xl border-0 bg-white rounded-2xl">
            <div id="seat-map-app" data-evento-slug="{{ $evento->slug }}"
                data-initial-bg-image-url="{{ $evento->bg_image_url ?? '' }}" class="w-full"></div>
        </x-filament::card>

    </div>

    @vite('resources/js/app.js')
</x-filament::page>
