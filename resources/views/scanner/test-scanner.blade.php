<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escáner de Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- Navbar mobile -->
    <div class="md:hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 px-4 py-3 text-lg font-bold z-50">
        Innova Ticket
    </div>

    <!-- Sidebar desktop -->
    <aside class="hidden md:flex w-64 bg-white border-r border-gray-200 flex-col">
        <div class="h-16 flex items-center justify-center border-b border-gray-100 text-xl font-bold">
            Innova Ticket
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-gray-700">
            <a href="/admin/eventos" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 {{ request()->is('admin/eventos*') ? 'bg-orange-100 text-orange-600 font-medium' : '' }}">
                🗓️ <span>Eventos</span>
            </a>
            <a href="/scanner-test" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 bg-blue-100 text-blue-600 font-medium">
                📷 <span>Scanner de Tickets</span>
            </a>
            <a href="/admin/cobros" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 {{ request()->is('admin/cobros*') ? 'bg-orange-100 text-orange-600 font-medium' : '' }}">
                💳 <span>Cobros</span>
            </a>
        </nav>
    </aside>

    <!-- Contenido -->
    <main class="flex-1 px-4 pt-20 md:pt-10 md:px-10 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Escáner de Tickets</h1>

        <div class="bg-white shadow rounded-xl p-6 max-w-xl mx-auto border">
            <!-- Lector QR -->
            <div id="qr-reader" class="mb-6 border rounded-md overflow-hidden"></div>

            <!-- Botones -->
            <div class="flex gap-4 justify-center mt-4 flex-wrap">
                <button id="btn-iniciar" onclick="iniciarScanner()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    🟢 Iniciar Escáner
                </button>

                <button id="btn-detener" onclick="detenerScanner()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 hidden">
                    🔴 Detener Escáner
                </button>
            </div>

            <!-- Resultado -->
            <div id="resultado" class="mt-6 text-center hidden">
                <div id="estado-mensaje" class="text-xl font-semibold mb-2"></div>
                <div id="info-ticket" class="text-gray-600">
                    <p><strong>Evento:</strong> <span id="evento">-</span></p>
                    <p><strong>Nombre:</strong> <span id="nombre">-</span></p>
                    <p><strong>Tipo:</strong> <span id="tipo">-</span></p>
                </div>

                <button onclick="reiniciarScanner()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    🔄 Escanear otro
                </button>
            </div>
        </div>
    </main>

    @include('livewire.floating-menu')

    <script>
        const qr = new Html5Qrcode("qr-reader");

        function iniciarScanner() {
            document.getElementById('btn-iniciar').classList.add('hidden');
            document.getElementById('btn-detener').classList.remove('hidden');

            qr.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                async qrCodeMessage => {
                    console.log("Código leído bruto:", qrCodeMessage);

                    let codigo = qrCodeMessage;
                    if (codigo.includes('/ticket/')) {
                        const partes = codigo.split('/');
                        codigo = partes.find(part => /^[0-9a-fA-F-]{36}$/.test(part)) || codigo;
                    }

                    try {
                        const response = await fetch('/validar-ticket', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ codigo })
                        });

                        const data = await response.json();
                        console.log("Respuesta del servidor:", data);

                        document.getElementById('estado-mensaje').textContent =
                            data.estado === 'valido' ? '✅ Ticket válido' :
                            data.estado === 'usado' ? '⚠️ Ticket ya usado' :
                            '❌ Ticket inválido';

                        document.getElementById('evento').textContent = data.evento || '-';
                        document.getElementById('nombre').textContent = data.nombre || '-';
                        document.getElementById('tipo').textContent = data.tipo || '-';

                        document.getElementById('resultado').classList.remove('hidden');
                        detenerScanner(); // detener escáner después de escanear
                    } catch (error) {
                        console.error("Error al validar:", error);
                    }
                }
            ).catch(err => console.error("Error al iniciar escáner:", err));
        }

        function detenerScanner() {
            qr.stop().then(() => {
                document.getElementById('btn-iniciar').classList.remove('hidden');
                document.getElementById('btn-detener').classList.add('hidden');
                console.log("Escáner detenido.");
            }).catch(err => console.error("Error al detener escáner:", err));
        }

        function reiniciarScanner() {
            document.getElementById('estado-mensaje').textContent = '';
            document.getElementById('evento').textContent = '-';
            document.getElementById('nombre').textContent = '-';
            document.getElementById('tipo').textContent = '-';
            document.getElementById('resultado').classList.add('hidden');
            iniciarScanner();
        }
    </script>
</body>
</html>
