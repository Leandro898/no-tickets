<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Innova Ticket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-purple-700">Innova Ticket</a>
            <nav class="space-x-4">
                <a href="{{ url('/eventos') }}" class="text-gray-700 hover:text-purple-700 font-medium">Eventos</a>
                <a href="{{ url('/login') }}" class="text-gray-700 hover:text-purple-700 font-medium">Ingresar</a>
            </nav>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="max-w-xl mx-auto mt-24 bg-white p-10 rounded-lg shadow text-center">
        <h1 class="text-4xl font-bold text-purple-700 mb-4">Bienvenido a Innova Ticket</h1>
        <p class="text-gray-700 text-lg">Tu plataforma para vender entradas fácil y rápido.</p>
        <a href="{{ url('/eventos') }}"
           class="inline-block mt-6 bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-6 rounded">
           Ver eventos
        </a>
    </main>

</body>
</html>

