<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escáner de Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: sans-serif;
            background-color: #f9fafb;
            color: #111827;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 240px;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sidebar h2 {
            font-size: 18px;
            font-weight: bold;
            color: #8b5cf6;
        }
        .sidebar a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 0;
            display: block;
        }
        .sidebar a.active {
            color: #8b5cf6;
            font-weight: bold;
        }
        .content {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        video {
            border: 4px solid #8b5cf6;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #8b5cf6;
        }
        #result {
            margin-top: 20px;
            font-size: 26px;
            font-weight: bold;
            text-align: center;
        }
        #reiniciar {
            margin-top: 20px;
            font-size: 18px;
            padding: 10px 20px;
            background-color: #e5e7eb;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Innova Ticket</h2>
        <a href="/admin/eventos">📅 Eventos</a>
        <a href="/scanner-test" class="active">🎫 Scanner de Tickets</a>
        <a href="/admin/oauth-connect-page">💰 Cobros</a>
    </div>

    <div class="content">
        <h1>Escaneá tu entrada</h1>
        <div id="reader" style="width: 500px;"></div>
        <div id="result"></div>
        <button id="reiniciar" onclick="reiniciarEscaneo()">🔄 Reiniciar escaneo</button>
    </div>

    <script>
        console.log("💡 Script cargado correctamente");

        let escaneando = true;

        function onScanSuccess(decodedText, decodedResult) {
            if (!escaneando) return;
            escaneando = false;

            console.log("QR detectado:", decodedText);

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/validar-ticket', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ codigo: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                const result = document.getElementById('result');
                if (data.estado === 'valido') {
                    result.innerHTML = '✅ Entrada válida<br>' + data.nombre;
                    result.style.color = 'green';
                } else if (data.estado === 'usado') {
                    result.innerHTML = '⚠️ Entrada ya usada';
                    result.style.color = 'orange';
                } else {
                    result.innerHTML = '❌ Código inválido';
                    result.style.color = 'red';
                }
            })
            .catch(err => {
                console.error("Error al validar:", err);
            });
        }

        function reiniciarEscaneo() {
            escaneando = true;
            document.getElementById('result').innerHTML = '';
        }

        const qrScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        qrScanner.render(onScanSuccess);
    </script>
</body>
</html>
