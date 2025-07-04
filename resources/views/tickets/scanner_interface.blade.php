<x-filament-panels::page>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Scanner de Tickets</h2>
    </x-slot>

    <div class="text-center mb-4 text-purple-700 font-semibold text-lg">Escaneá tu entrada</div>

    <div style="text-align:center; margin-bottom:20px;">
        <button id="startBtn" style="background-color:#2563eb; color:white; font-weight:bold; padding:10px 20px; border-radius:8px; border:none; cursor:pointer;">
            Iniciar Cámara
        </button>
        <button id="stopBtn" style="background-color:#dc2626; color:white; font-weight:bold; padding:10px 20px; border-radius:8px; border:none; cursor:pointer;" disabled>
            Detener Cámara
        </button>
    </div>
    

    <div class="flex justify-center">
        <video id="camera" autoplay playsinline class="rounded-lg shadow-lg ring-4 ring-purple-500 w-full max-w-md"></video>
    </div>

    <script src="{{ asset('js/scanner.js') }}"></script>
</x-filament-panels::page>
