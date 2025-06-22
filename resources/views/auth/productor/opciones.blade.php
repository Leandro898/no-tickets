<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrarse | Productor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-800 to-black text-white">
    <div class="bg-black/80 p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-2xl font-bold mb-6">Elegí cómo registrarte</h1>

        <div class="space-y-4">
            <!-- Email -->
            <a href="{{ route('registro.email') }}"
                class="w-full flex items-center justify-center gap-2 bg-white text-black py-2 px-4 rounded font-medium hover:bg-gray-100">
                ✉️ Continuar con Email
            </a>

            <!-- Google (placeholder por ahora) -->
            <button disabled
                class="w-full flex items-center justify-center gap-2 bg-gray-700 text-white py-2 px-4 rounded font-medium opacity-50 cursor-not-allowed">
                🌐 Continuar con Google
            </button>

            <!-- Apple (placeholder por ahora) -->
            <button disabled
                class="w-full flex items-center justify-center gap-2 bg-gray-700 text-white py-2 px-4 rounded font-medium opacity-50 cursor-not-allowed">
                🍎 Continuar con Apple
            </button>
        </div>
    </div>
</body>
</html>
