<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escáner de Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- Sidebar estilo Filament -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-gray-100 text-xl font-bold">
            Innova Ticket
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 text-gray-700">
            <a href="/admin/eventos" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
                🗓️ <span>Eventos</span>
            </a>
            <a href="/scanner-test" class="flex items-center gap-2 p-2 rounded bg-blue-100 text-blue-600 font-medium">
                📷 <span>Scanner de Tickets</span>
            </a>
            <a href="/admin/oauth-connect-page" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
                💳 <span>Cobros</span>
            </a>
        </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="flex-1 p-10 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Escáner de Tickets</h1>

        <div class="bg-white shadow rounded-xl p-6 max-w-xl mx-auto border">
            <div id="qr-reader" class="mb-6 border rounded-md overflow-hidden"></div>
              <div id="resultado-validacion" class="hidden text-center space-y-4">
                  <div id="estado-icono" class="text-5xl">✅</div>
                  <h2 id="estado-texto" class="text-xl font-bold text-gray-800">Ticket válido</h2>
                  <p id="info-ticket" class="text-gray-600 text-sm">
                      <strong>Evento:</strong> <span id="evento-nombre"></span><br>
                      <strong>Nombre:</strong> <span id="asistente-nombre"></span><br>
                      <strong>Tipo:</strong> <span id="tipo-ticket"></span>
                  </p>

                  <button onclick="reiniciarParaOtro()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                      🔄 Escanear otro
                  </button>
              </div>

            <div class="flex gap-4 justify-center">
                <div class="flex gap-4 justify-center mt-4">
                    <button id="btn-iniciar" onclick="iniciarScanner()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 hidden">
                        🟢 Iniciar Escáner
                    </button>

                    <button id="btn-detener" onclick="detenerScanner()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 hidden">
                        🔴 Detener Escáner
                    </button>
                </div>
            </div>

        </div>
    </main>

    <script>
        const qr = new Html5Qrcode("qr-reader");
        let scannerActivo = false;

        const btnIniciar = document.getElementById('btn-iniciar');
        const btnDetener = document.getElementById('btn-detener');
        const resultado = document.getElementById('resultado-validacion');
        const qrReader = document.getElementById('qr-reader');

        function iniciarScanner() {
            if (scannerActivo) return;

            qr.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                qrCodeMessage => {
                    console.log("Código leído:", qrCodeMessage);

                    fetch("/validar-ticket", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').getAttribute("content")
                        },
                        body: JSON.stringify({ codigo: qrCodeMessage })
                    })
                    .then(response => response.json())
                    .then(data => {
                        mostrarResultado({
                            estado: data.estado,
                            evento: data.evento ?? '-',
                            nombre: data.nombre ?? '-',
                            tipo: data.tipo ?? '-'
                        });
                        detenerScanner();
                    })
                    .catch(error => {
                        console.error("Error al validar el ticket:", error);
                        alert("Error de validación. Intenta nuevamente.");
                    });
                }
            ).then(() => {
                scannerActivo = true;
                btnIniciar.classList.add('hidden');
                btnDetener.classList.remove('hidden');
            }).catch(err => console.error("Error al iniciar escáner:", err));
        }

        function detenerScanner() {
            if (!scannerActivo) return;

            qr.stop().then(() => {
                scannerActivo = false;
                btnIniciar.classList.remove('hidden');
                btnDetener.classList.add('hidden');
            }).catch(err => console.error("Error al detener escáner:", err));
        }

        function mostrarResultado(data) {
            qrReader.classList.add('hidden');
            resultado.classList.remove('hidden');

            document.getElementById('estado-icono').textContent =
                data.estado === 'valido' ? '✅' :
                data.estado === 'usado' ? '⚠️' :
                '❌';

            document.getElementById('estado-texto').textContent =
                data.estado === 'valido' ? 'Ticket válido' :
                data.estado === 'usado' ? 'Ticket ya usado' :
                'Ticket inválido';

            document.getElementById('evento-nombre').textContent = data.evento;
            document.getElementById('asistente-nombre').textContent = data.nombre;
            document.getElementById('tipo-ticket').textContent = data.tipo;
        }

        function reiniciarParaOtro() {
            resultado.classList.add('hidden');
            qrReader.classList.remove('hidden');
            iniciarScanner();
        }

        document.addEventListener("DOMContentLoaded", () => {
            btnIniciar.classList.remove('hidden');
            btnDetener.classList.add('hidden');
            resultado.classList.add('hidden');
        });
    </script>



</body>
</html>
