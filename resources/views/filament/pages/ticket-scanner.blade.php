{{-- resources/views/filament/pages/ticket-scanner.blade.php --}}
<x-filament-panels::page>
    {{-- Slot “heading” muestra el título con el estilo estándar --}}
    <x-slot name="heading">
        Ticket Scanner
    </x-slot>

    {{-- Slot “subheading” si quieres una línea extra debajo --}}
    <x-slot name="subheading">
        Escaneá tu entrada con tu cámara
    </x-slot>

    {{-- Tu script: define la URL de escaneo --}}
    <script>
        window.scannerEndpoint = "{{ route('admin.ticket-scanner.scan') }}";
    </script>

    {{-- Contenido de tu escáner --}}
    <div class="flex flex-col items-center py-10 space-y-6">
        <div
          id="reader"
          class="relative w-full max-w-md h-64 bg-black rounded-lg overflow-hidden
                 border-4 border-gray-300 transition-colors"
        >
          <div
            id="scanOverlay"
            class="absolute inset-0 flex items-center justify-center 
                   bg-black bg-opacity-80 text-white text-2xl font-bold p-8 hidden"
            style="z-index:999;"
          ></div>
        </div>
        <div class="flex space-x-4">
          <x-filament::button id="startBtn" color="success">Iniciar cámara</x-filament::button>
          <x-filament::button id="stopBtn" color="danger" disabled>Detener cámara</x-filament::button>
        </div>
    </div>

    {{-- Tu JS compilado por Vite --}}
    @vite('resources/js/scanner-new.js')
</x-filament-panels::page>
