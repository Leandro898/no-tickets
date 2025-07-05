<x-filament::page>
  <x-slot name="header">
    <h1 class="text-3xl font-bold text-gray-900">Ticket Scanner</h1>
  </x-slot>

  <script>
    window.scannerEndpoint = "{{ route('admin.ticket-scanner.scan') }}";
  </script>

  <div class="flex flex-col items-center py-10 space-y-6">
    <h2 class="text-2xl font-semibold text-purple-700">Escaneá tu entrada</h2>

    {{-- Contenedor del vídeo con altura fija --}}
    <div
      id="reader"
      class="relative w-full max-w-md h-64 bg-black rounded-lg overflow-hidden
             border-4 border-gray-300 transition-colors"
      style="position:relative;"
    >
      {{-- Overlay de mensajes --}}
      <div
        id="scanOverlay"
        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-80
               text-white text-2xl font-bold p-8 hidden"
        style="z-index:999;"
      ></div>
    </div>

    {{-- Botones justo debajo del reader --}}
    <div class="flex space-x-4">
      <x-filament::button
        id="startBtn"
        type="button"
        color="success"
        class="btn-detalles"
      >
        Iniciar cámara
      </x-filament::button>
      <x-filament::button
        id="stopBtn"
        type="button"
        color="danger"
        class="btn-detalles"
        disabled
      >
        Detener cámara
      </x-filament::button>
    </div>
  </div>

  @vite('resources/js/scanner-new.js')
</x-filament::page>
