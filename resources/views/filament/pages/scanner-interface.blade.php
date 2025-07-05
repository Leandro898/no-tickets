<x-filament::page>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-extrabold text-gray-900">Scanner de Tickets</h1>
        </div>
    </x-slot>

    <div class="flex justify-center py-12">
        <div class="w-full max-w-lg bg-white shadow-xl rounded-2xl p-6 space-y-6">
            <h2 class="text-2xl font-semibold text-center text-purple-700">
                Escaneá tu entrada
            </h2>

            <div class="flex justify-center space-x-4">
                <x-filament::button id="startBtn" color="success" class="btn-detalles px-6 py-3">
                    Iniciar cámara
                </x-filament::button>
                <x-filament::button id="stopBtn" color="danger" class="btn-detalles px-6 py-3" disabled>
                    Detener cámara
                </x-filament::button>
            </div>

            <div id="scanResult" class="h-8 text-center text-lg font-medium"></div>

            <div class="relative overflow-hidden rounded-xl bg-black">
                <video
                    id="camera"
                    autoplay
                    playsinline
                    class="w-full h-64 object-cover"
                ></video>

                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="w-2/3 h-2/3 border-4 border-dashed border-purple-500 rounded-lg animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inyectamos la URL del endpoint al JS --}}
    <script>
        window.scannerScanUrl = "{{ route('admin.scanner.scan') }}";
    </script>
    
    @vite('resources/js/scanner.js')
</x-filament::page>
