<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificá tu correo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-800 to-black text-white">
    <div class="bg-black/80 p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-2xl font-bold mb-4">Verificá tu correo</h1>

        <p class="text-sm text-gray-300 mb-4">
            Enviamos un código a <strong>{{ session('registro_email') }}</strong>
        </p>

        <form method="POST" action="{{ route('registro.verificacion') }}" class="mt-6">
            @csrf
            <div class="flex justify-center gap-2">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="codigo[]" maxlength="1" required
                        class="w-10 h-12 text-2xl text-center bg-gray-800 text-white rounded border border-gray-600 focus:outline-none focus:ring-2 focus:ring-violet-500"
                    >
                @endfor
            </div>

            <button type="submit"
                class="mt-6 bg-violet-600 hover:bg-violet-700 text-white font-semibold py-2 px-6 rounded">
                Verificar
            </button>
        </form>

        <!-- CONTADOR PARA REENVIAR CODIGO -->
        <p id="contador" class="text-center mt-4 text-sm text-purple-400">
            Enviar nuevo código en <span id="tiempo">60</span>s
        </p>

        <form method="POST" action="{{ route('registro.reenviar') }}">
            @csrf
            <button id="btn-reenviar" type="submit" class="mt-2 text-sm text-purple-300 hover:underline hidden">
                Enviar nuevo código
            </button>
        </form>


        @if($errors->any())
            <p class="mt-4 text-red-400 text-sm">{{ $errors->first() }}</p>
        @endif
    </div>
    <!-- CONTADOR PARA REENVIO DE CODIGO PARA VERIFICAR EMAIL -->
    <script>
        let segundosRestantes = 60;
        const contadorSpan = document.getElementById('contador');
        const btnReenviar = document.getElementById('btn-reenviar');

        const intervalo = setInterval(() => {
            segundosRestantes--;
            contadorSpan.textContent = segundosRestantes;

            if (segundosRestantes <= 0) {
                clearInterval(intervalo);
                btnReenviar.disabled = false;
                contadorSpan.textContent = '';
                btnReenviar.textContent = 'Enviar nuevo código';
            }
        }, 1000);

    // AGREGAR CONTADOR DE REENVIO DE CODIGO DE SEGURIDAD
    document.addEventListener('DOMContentLoaded', function () {
        let tiempoRestante = 60;

        const contador = document.getElementById('tiempo');
        const parrafoContador = document.getElementById('contador');
        const botonReenviar = document.getElementById('btn-reenviar');

        const intervalo = setInterval(() => {
            tiempoRestante--;
            contador.textContent = tiempoRestante;

            if (tiempoRestante <= 0) {
                clearInterval(intervalo);
                parrafoContador.classList.add('hidden');
                botonReenviar.classList.remove('hidden');
            }
        }, 1000);
    });
    </script>
</body>
</html>
