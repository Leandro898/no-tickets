{{-- resources/views/livewire/qr-scanner.blade.php --}}

<div
    x-data="{
        qrCodeScanner: null,
        isScanning: false,
        messageClasses: {
            'info': 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700',
            'success': 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700',
            'error': 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700',
        }
    }"
    x-init="
        // Aquí quitamos el 'if' directo. La función startScanner ya maneja si está corriendo.
        // Esto soluciona el 'Uncaught SyntaxError: Unexpected token 'if''
        $nextTick(() => {
            this.startScanner();
        });

        // Limpia el escáner cuando el componente Livewire se destruye (navegar a otra página de Filament)
        document.addEventListener('livewire:navigating', () => {
            this.stopScanner();
        });

        // Asegurarse de detener el escáner también si el componente es desmontado por otras razones
        Livewire.hook('element.removed', (el, component) => {
            if (component.id === '{{ $this->getId() }}' && this.qrCodeScanner) {
                this.stopScanner();
            }
        });
    "
>
    {{-- Contenido HTML del escáner --}}
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Scanner de QR/Barras</h2>

    <div
        class="mt-4 p-4 rounded-lg border text-center font-bold"
        x-bind:class="messageClasses[$wire.messageType || 'info']"
    >
        {{ $message }}
        @if ($scanResult)
            <br> <span class="text-sm font-normal">Último: {{ $scanResult }}</span>
        @endif
    </div>

    {{-- Agregamos un fondo para debug - quítalo si no lo necesitas una vez resuelto --}}
    <div id="reader" style="width: 100%; max-width: 500px; margin: 20px auto; border: 2px solid var(--primary-500); border-radius: 8px; overflow: hidden; height: 300px; background-color: #000;"></div>

    <div class="mt-4 flex justify-center space-x-4">
        <button
            x-show="!isScanning"
            @click="startScanner()"
            type="button"
            class="filament-button filament-button-size-md inline-flex items-center justify-center px-4 py-2 text-sm font-semibold tracking-tight rounded-lg border border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-primary-600 text-white hover:bg-primary-500 focus:ring-primary-600"
        >
            <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653A4.48 4.48 0 0 1 15 8.25V18.75A4.48 4.48 0 0 1 5.25 21.347M5.25 5.653A4.48 4.48 0 0 0 15 8.25V18.75A4.48 4.48 0 0 0 5.25 21.347M5.25 5.653C5.25 5.107 5.707 4.65 6.253 4.65H18.75c.547 0 .997.45.997 1.003V19.35c0 .553-.45.997-.997 1.003H6.253c-.547 0-.997-.45-.997-1.003M12 4.5l6 6m0-6l-6 6" />
            </svg>
            Iniciar Escáner
        </button>

        <button
            x-show="isScanning"
            @click="stopScanner()"
            type="button"
            class="filament-button filament-button-size-md inline-flex items-center justify-center px-4 py-2 text-sm font-semibold tracking-tight rounded-lg border border-gray-300 dark:border-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
        >
            <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Detener Escáner
        </button>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    @script
    <script>
        this.qrCodeScanner = null;

        this.startScanner = function() {
            if (this.qrCodeScanner && this.qrCodeScanner.isScanning) {
                console.log('DEBUG: Escáner ya está corriendo, no se reinicia.');
                return;
            }

            const readerElement = document.getElementById('reader');
            if (!readerElement) {
                console.error("DEBUG: Elemento 'reader' NO ENCONTRADO en el DOM. Esto es crítico.");
                window.Livewire.find('{{ $this->getId() }}').set('message', 'Error: Contenedor del escáner no encontrado.');
                window.Livewire.find('{{ $this->getId() }}').set('messageType', 'error');
                return;
            } else {
                console.log("DEBUG: Elemento 'reader' encontrado:", readerElement);
                console.log("DEBUG: readerElement offsetWidth:", readerElement.offsetWidth, "offsetHeight:", readerElement.offsetHeight);
            }

            this.qrCodeScanner = new Html5Qrcode("reader");
            console.log("DEBUG: Html5Qrcode instancia creada.");

            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                console.log(`DEBUG: Código escaneado: ${decodedText}`);
                window.Livewire.find('{{ $this->getId() }}').dispatch('codeScanned', { code: decodedText });
                this.stopScanner(); // Mantenemos esta línea para detener después de un escaneo exitoso.
            };

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                // Puedes ajustar otros settings si la cámara no se inicia bien
                // videoConstraints: {
                //     facingMode: { exact: "environment" } // Preferir la cámara trasera
                // }
            };

            console.log("DEBUG: Intentando iniciar escáner con config:", config);
            this.qrCodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                .then(() => {
                    this.isScanning = true;
                    console.log('DEBUG: Promesa de inicio del escáner RESUELTA. isScanning ahora es TRUE.');
                    window.Livewire.find('{{ $this->getId() }}').set('message', 'Escáner iniciado. Apunta la cámara al código QR.');
                    window.Livewire.find('{{ $this->getId() }}').set('messageType', 'info');
                })
                .catch(err => {
                    console.error(`DEBUG: Error al iniciar el escáner (Promesa RECHAZADA): ${err}`);
                    window.Livewire.find('{{ $this->getId() }}').set('message', 'Error al iniciar la cámara. Asegúrate de tener una cámara y de dar permisos.');
                    window.Livewire.find('{{ $this->getId() }}').set('messageType', 'error');
                    this.isScanning = false;
                    this.qrCodeScanner = null; // Liberar la instancia si falló al iniciar
                });
        };

        this.stopScanner = function() {
            if (this.qrCodeScanner && this.qrCodeScanner.isScanning) {
                this.qrCodeScanner.stop()
                    .then(() => {
                        console.log("DEBUG: Escáner detenido exitosamente.");
                        this.qrCodeScanner = null;
                        this.isScanning = false;
                        window.Livewire.find('{{ $this->getId() }}').set('message', 'Escáner detenido. Presiona "Iniciar Escáner" para reanudar.');
                        window.Livewire.find('{{ $this->getId() }}').set('messageType', 'info');
                    })
                    .catch(err => {
                        console.error("DEBUG: Error al detener el escáner:", err);
                        this.qrCodeScanner = null;
                        this.isScanning = false;
                        window.Livewire.find('{{ $this->getId() }}').set('message', 'Error al detener el escáner. Puede que ya estuviera inactivo.');
                        window.Livewire.find('{{ $this->getId() }}').set('messageType', 'error');
                    });
            } else {
                this.isScanning = false;
                console.log("DEBUG: No hay escáner activo para detener.");
                window.Livewire.find('{{ $this->getId() }}').set('message', 'No hay escáner activo para detener.');
                window.Livewire.find('{{ $this->getId() }}').set('messageType', 'info');
            }
        };

        Livewire.on('scannerProcessed', () => {
            console.log('DEBUG: Evento scannerProcessed recibido. Preparando reinicio...');
            this.isScanning = false;
            this.stopScanner();
            setTimeout(() => {
                this.startScanner();
            }, 3000);
        });
    </script>
    @endscript
</div>