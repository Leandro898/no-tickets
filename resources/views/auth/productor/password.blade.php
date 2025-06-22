<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear contraseña</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-800 to-black text-white">
    <div class="bg-black/80 p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-2xl font-bold mb-6">Creá tu contraseña</h1>

        <form method="POST" action="{{ route('registro.password') }}">
            @csrf

            <input type="password" name="password" required minlength="6"
                class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-700 text-white mb-4"
                placeholder="Ingresar contraseña">

            <input type="password" name="password_confirmation" required
                class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-700 text-white mb-4"
                placeholder="Repetir contraseña">

            <button type="submit"
                class="w-full bg-violet-600 hover:bg-violet-700 text-white py-2 rounded font-semibold">
                Continuar
            </button>
        </form>
    </div>
</body>
</html>
