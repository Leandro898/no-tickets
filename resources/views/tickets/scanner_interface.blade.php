<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escáner de Tickets QR (ZXing)</title>
    <script type="text/javascript" src="https://unpkg.com/@zxing/browser@latest"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
            background-color: #f0f2f5;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
            padding: 25px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 25px;
        }

        #scanner-video-container {
            width: 100%;
            max-width: 500px; /* Tamaño máximo del video */
            margin: 0 auto;
            border: 2px solid #007bff;
            border-radius: 8px;
            overflow: hidden;
            position: relative; /* Necesario para posicionar el video */
            height: 375px; /* Altura fija para el contenedor del video (4:3 aspect ratio para 500px width) */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #000; /* Fondo negro si no hay video */
        }

        #scanner-video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ajusta el video para cubrir el contenedor */
        }

        #result-container {
            margin-top: 25px;
            padding: 15px;
            border-radius: 8px;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3em;
            font-weight: bold;
        }

        .valid-scan {
            background-color: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }

        .invalid-scan {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
        }

        .info-scan {
            background-color: #e0f2f7;
            border: 1px solid #2196f3;
            color: #2196f3;
        }

        .loading-text {
            color: #555;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Escáner de Tickets (ZXing)</h1>
        <div id="scanner-video-container">
            <video id="scanner-video"></video>
        </div>
        <div id="result-container" class="info-scan">
            <span class="loading-text">Cargando escáner...</span>
        </div>
        <button id="startButton">Iniciar Escáner</button>
        <button id="stopButton" style="display:none;">Detener Escáner</button>
    </div>

    <script type="text/javascript">
        // Instancia de la librería ZXing
        const codeReader = new ZXingBrowser.BrowserQRCodeReader();

        // Referencias a elementos del DOM
        const videoElement = document.getElementById('scanner-video');
        const resultContainer = document.getElementById('result-container');
        const startButton = document.getElementById('startButton');
        const stopButton = document.getElementById('stopButton');

        let controls = null; // Para guardar los controles del escáner

        // Función para mostrar mensajes en el contenedor de resultados
        function displayResult(message, type = 'info') {
            resultContainer.textContent = message;
            resultContainer.className = 'result-container'; // Reset class
            if (type === 'valid') {
                resultContainer.classList.add('valid-scan');
            } else if (type === 'invalid') {
                resultContainer.classList.add('invalid-scan');
            } else {
                resultContainer.classList.add('info-scan');
            }
        }

        // Función para manejar el éxito del escaneo
        const onScanSuccess = async (result, error) => {
            if (result) {
                // Si el escáner detecta un QR
                const decodedText = result.getText();
                console.log(`Código QR detectado: ${decodedText}`); // <-- ¡REVISA ESTO EN LA CONSOLA!

                // Detener el escáner para procesar el resultado
                if (controls) {
                    await controls.stop();
                    stopButton.style.display = 'none';
                    startButton.style.display = 'block';
                    startButton.disabled = true; // Deshabilitar hasta que se muestre el resultado
                }

                let uniqueCode;

                // Lógica para extraer el uniqueCode (adaptada para URLs o solo código)
                // NO REDIRIGIR AUTOMÁTICAMENTE A LA URL ESCANEADA
                if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                    try {
                        const url = new URL(decodedText);
                        const pathSegments = url.pathname.split('/').filter(segment => segment);
                        // Asume que el código es el penúltimo segmento si la URL es /ticket/CODIGO/validate
                        // o el último si es /ticket/CODIGO
                        if (pathSegments.length >= 2 && pathSegments[pathSegments.length - 1] === 'validate') {
                            uniqueCode = pathSegments[pathSegments.length - 2];
                        } else if (pathSegments.length >= 1) { // Podría ser solo /ticket/CODIGO
                            uniqueCode = pathSegments[pathSegments.length - 1];
                        }
                    } catch (e) {
                        console.error("Error al parsear URL:", e);
                        uniqueCode = null; // Fallback si la URL es inválida
                    }
                } else {
                    // Si el QR solo contiene el código único directamente
                    uniqueCode = decodedText;
                }

                if (!uniqueCode) {
                    displayResult("Código QR inválido: No se pudo extraer el código del ticket.", 'invalid');
                    setTimeout(() => {
                        displayResult("Esperando siguiente QR...", 'info');
                        startScanner(); // Reiniciar automáticamente para el siguiente intento
                    }, 3000);
                    return;
                }

                displayResult(`Validando ticket: ${uniqueCode}...`, 'info');

                // Enviar el código al backend de Laravel para validación
                fetch('{{ route('ticket.scan', ['code' => 'PLACEHOLDER']) }}'.replace('PLACEHOLDER', uniqueCode), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Error desconocido del servidor.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        displayResult(`Ticket válido: ${data.message}`, 'valid');
                    } else {
                        displayResult(`Ticket inválido: ${data.message}`, 'invalid');
                    }
                })
                .catch(error => {
                    console.error('Error al enviar el código al servidor o procesar la respuesta:', error);
                    displayResult(`Error de conexión o validación: ${error.message}`, 'invalid');
                })
                .finally(() => {
                    // Reiniciar el escáner después de un breve retraso
                    setTimeout(() => {
                        displayResult("Esperando siguiente QR...", 'info');
                        startButton.disabled = false; // Habilitar botón de iniciar
                        startScanner(); // Reiniciar automáticamente
                    }, 3000); // Muestra el resultado por 3 segundos
                });
            }
            // No manejar errores aquí en onScanSuccess, ZXing tiene un callback separado para eso
        };

        // Función para iniciar el escáner
        const startScanner = async () => {
            try {
                // Listar dispositivos de video disponibles (cámaras)
                const videoInputDevices = await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();
                let selectedDeviceId;

                if (videoInputDevices.length > 0) {
                    // Intenta encontrar la cámara trasera si está disponible
                    const rearCamera = videoInputDevices.find(device => device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('trasera'));
                    selectedDeviceId = rearCamera ? rearCamera.deviceId : videoInputDevices[0].deviceId; // Usa la trasera o la primera disponible
                } else {
                    displayResult("No se encontraron cámaras.", 'invalid');
                    startButton.disabled = true;
                    return;
                }

                displayResult("Iniciando escáner...", 'info');
                startButton.disabled = true; // Deshabilitar botón de inicio mientras se inicia

                // Iniciar el escaneo continuo
                controls = await codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, onScanSuccess);

                displayResult("Escáner iniciado. Apunte la cámara al QR.", 'info');
                startButton.style.display = 'none';
                stopButton.style.display = 'block';
                stopButton.disabled = false; // Habilitar botón de detener

            } catch (err) {
                console.error("Error al iniciar el escáner:", err);
                displayResult(`Error: ${err.message || 'No se pudo acceder a la cámara o iniciar el escáner.'}`, 'invalid');
                startButton.style.display = 'block';
                startButton.disabled = false;
                stopButton.style.display = 'none';
            }
        };

        // Función para detener el escáner
        const stopScanner = async () => {
            if (controls) {
                try {
                    await controls.stop();
                    displayResult("Escáner detenido.", 'info');
                    startButton.style.display = 'block';
                    startButton.disabled = false;
                    stopButton.style.display = 'none';
                } catch (err) {
                    console.error("Error al detener el escáner:", err);
                    displayResult("Error al detener el escáner.", 'invalid');
                }
            }
        };

        // Event listeners para los botones
        startButton.addEventListener('click', startScanner);
        stopButton.addEventListener('click', stopScanner);

        // Iniciar el escáner automáticamente al cargar la página
        // Puedes cambiar esto para que el usuario presione el botón "Iniciar Escáner" manualmente.
        window.addEventListener('load', startScanner);

        // Limpiar el escáner al salir de la página
        window.addEventListener('beforeunload', () => {
            if (controls) {
                controls.stop();
            }
        });
    </script>
</body>
</html>