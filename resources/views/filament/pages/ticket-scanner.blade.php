{{-- resources/views/filament/pages/ticket-scanner.blade.php --}}
<x-filament-panels::page class="bg-violet-50 min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col items-center justify-center">

        <h2 class="text-violet-800 text-2xl font-bold mb-6 mt-4 text-center tracking-tight">
            Enfocá el código QR
        </h2>

        <div class="bg-white rounded-2xl shadow-xl flex flex-col items-center p-4"
             style="min-width:320px; max-width:95vw;">
            <div class="relative aspect-square w-72 sm:w-80 md:w-96 rounded-xl overflow-hidden border-4 border-violet-500 flex items-center justify-center bg-gray-100">
                <div id="reader" class="w-full h-full"></div>
                <div
                    id="scanOverlay"
                    class="absolute inset-0 flex flex-col items-center justify-center bg-violet-600 bg-opacity-80 text-white text-lg font-medium p-4 hidden z-20"
                >
                    <svg class="w-10 h-10 animate-spin mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
                    </svg>
                    <span>Escaneando…</span>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center space-y-3">
            <a href="#" class="font-semibold text-violet-700 hover:underline text-base">
                Ingresar datos manualmente
            </a>
            <div class="text-gray-500 text-sm">
                ¿Querés una mejor experiencia de escaneo?<br>
                Escaneá más rápido y sin internet en nuestra app.
            </div>
            <div class="flex gap-4 justify-center mt-2">
                <a href="#" class="bg-violet-600 text-white rounded-xl px-5 py-2 text-sm font-bold shadow hover:bg-violet-700 transition">Play Store</a>
                <a href="#" class="bg-gray-200 text-violet-700 rounded-xl px-5 py-2 text-sm font-bold shadow hover:bg-gray-300 transition">App Store</a>
            </div>
        </div>
    </div>

    <script>
        window.scannerEndpoint = "{{ route('admin.ticket-scanner.scan') }}";
    </script>
    @vite('resources/js/scanner-new.js')
</x-filament-panels::page>
