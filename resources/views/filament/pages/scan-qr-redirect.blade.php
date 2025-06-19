<x-filament::page>
    <div class="flex flex-col items-center justify-center min-h-[60vh]">
        <h1 class="text-2xl font-bold text-purple-700 mb-4">Escaneá tu entrada</h1>

        <div class="rounded-xl shadow-lg ring-4 ring-purple-600 overflow-hidden w-full max-w-md">
            <video id="camera" autoplay playsinline class="w-full h-auto"></video>
        </div>
    </div>

    <script>
        const video = document.getElementById('camera');

        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(error => {
                console.error('No se pudo acceder a la cámara:', error);
                alert('Error al acceder a la cámara');
            });
    </script>
</x-filament::page>
