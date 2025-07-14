<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Innova Ticket')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/css/filament/admin/filament.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html { overflow-y: scroll; }
    </style>
</head>
<body class="@yield('body-class','bg-gradient-to-br from-purple-50 to-purple-100') min-h-screen">
    @include('layouts.front-nav')

    {{-- SLIDER FUERA DEL CONTENEDOR, pegado al nav --}}
    @hasSection('slider')
        @yield('slider')
    @endif

    {{-- Contenedor solo para el resto del contenido --}}
    <div class="max-w-7xl mx-auto px-4 py-8">
        <main>
            @yield('content')
            @include('components.front-floating-menu')
        </main>
    </div>
    @livewireScripts
    @stack('scripts')
    @include('partials.footer')
</body>
</html>
