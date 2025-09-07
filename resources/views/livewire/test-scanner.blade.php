<div x-data="{
        scanner: null,
        isScanning: false,
        start() {
            this.scanner = new Html5Qrcode('reader');
            this.scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 250 },
                (code) => {
                    Livewire.dispatch('codeScanned', { code });
                    this.stop();
                },
                (error) => console.warn(error)
            ).then(() => this.isScanning = true)
             .catch(err => console.error('Error al iniciar cámara:', err));
        },
        stop() {
            this.scanner?.stop().then(() => this.isScanning = false);
        }
    }"
    x-init="$nextTick(() => start())"
    class="p-6"
>
    <h2 class="text-lg font-bold mb-4">Escáner de QR - Prueba</h2>

    <div id="reader" class="border w-full max-w-md h-[300px] mx-auto mb-4 bg-black"></div>

    <p class="text-center mt-4 font-semibold text-blue-600">{{ $message }}</p>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</div>
