<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Innova Ticket')</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 text-gray-900">
    {{-- Header global --}}
    <header class="bg-white shadow p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-purple-600">Innova Ticket</a>
            <nav class="space-x-4">
                <a href="{{ url('/') }}" class="hover:underline">Inicio</a>
                <a href="{{ url('/eventos') }}" class="hover:underline">Eventos</a>
                <a href="{{ url('/contacto') }}" class="hover:underline">Contacto</a>
            </nav>
        </div>
    </header>

    {{-- Contenido dinámico --}}
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>

