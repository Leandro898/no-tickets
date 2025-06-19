<x-filament::page>
    <h1 class="text-2xl font-bold text-center text-purple-700 mb-4">Escaneá tu entrada</h1>
    <div class="flex justify-center">
        <video id="camera" autoplay playsinline class="rounded-lg shadow-lg ring-4 ring-purple-500 w-full max-w-md"></video>
    </div>

    <script src="{{ asset('js/scanner.js') }}"></script>
</x-filament::page>
