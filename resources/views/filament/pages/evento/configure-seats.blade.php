<x-filament::page>
  <div class="flex flex-col min-h-screen">
    {{-- 1) Botón de “Ver evento” --}}
    <div class="flex-shrink-0 flex justify-center p-4">
      <x-filament::button
        color="primary"
        icon="heroicon-o-eye"
        tag="a"
        target="_blank"
        href="{{ url('eventos/' . $evento->slug . '/checkout-seats') }}"
      >
        Ver evento en el front
      </x-filament::button>
    </div>

    {{-- 2) Punto de montaje de Vue (ocupa todo el espacio restante) --}}
    <div class="flex-1 px-4 overflow-auto flex flex-col">
      {{-- 2.1) Componente Vue --}}
      <div
        id="seat-map-app"
        class="flex-1 w-full h-full min-h-[400px]"
        data-evento-slug="{{ $evento->slug }}"
        data-initial-bg-image-url="{{ $evento->bg_image_url ?? '' }}"
      ></div>
    </div>
  </div>

  @vite('resources/js/app.js')
</x-filament::page>
