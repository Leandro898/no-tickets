<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Innova Ticket')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        html { overflow-y: scroll; }
    </style>

</head>
<body class="bg-gray-100 min-h-screen">
    {{-- NAV FUERA DEL CONTENEDOR --}}
    @include('layouts.front-nav')

    {{-- Ahora el contenido sí en el contenedor central --}}
    <div class="max-w-7xl mx-auto px-4 py-8">
        <main class="py-8">
            @yield('content')
        </main>
    </div>
    @livewireScripts
</body>
</html>
