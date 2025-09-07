{{-- resources/views/filament/pages/ticket-scanner.blade.php --}}
<x-filament-panels::page class="bg-violet-50 min-h-screen flex flex-col">

    <div class="flex-1 flex flex-col items-center justify-center">

        <h2 class="text-violet-800 text-2xl font-bold mb-6 mt-4 text-center tracking-tight">
            Enfocá el código QR
        </h2>

        <div class="bg-white rounded-2xl shadow-xl flex flex-col items-center p-4"
            style="min-width:320px; max-width:95vw;">
            <div
                class="relative aspect-square w-72 sm:w-80 md:w-96 rounded-xl overflow-hidden border-4 border-violet-500 flex items-center justify-center">
                <div id="reader" class="w-full h-full"></div>
            </div>
        </div>

        <div class="mt-8 text-center space-y-3">
            <button id="btnManualInput" type="button"
                class="font-semibold text-violet-700 hover:underline text-base bg-transparent border-none">
                Ingresar datos manualmente
            </button>
            <div class="text-gray-500 text-sm h-12">
                {{-- ¿Querés una mejor experiencia de escaneo?<br> --}}
                {{-- Escaneá más rápido y sin internet en nuestra app. --}}
            </div>
            {{-- <div class="flex gap-4 justify-center mt-2">
                <a href="#"
                    class="bg-violet-600 text-white rounded-xl px-5 py-2 text-sm font-bold shadow hover:bg-violet-700 transition">Play
                    Store</a>
                <a href="#"
                    class="bg-gray-200 text-violet-700 rounded-xl px-5 py-2 text-sm font-bold shadow hover:bg-gray-300 transition">App
                    Store</a>
            </div> --}}
        </div>
    </div>

    <script>
        window.csrfToken = "{{ csrf_token() }}";
        window.buscarTicketEndpoint  = "{{ route('admin.ticket-scanner.buscar') }}";
        window.validarTicketEndpoint = "{{ route('admin.ticket-scanner.validar') }}";
    </script>

    @vite('resources/js/scanner/index.js')

</x-filament-panels::page>
