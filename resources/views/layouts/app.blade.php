<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/tickets-pro.png') }}">
  @vite('resources/css/app.css')
  
  @stack('styles')
  <!-- <style>
    html { overflow-y: scroll; }
  </style> -->
</head>
<body class="@yield('body-class', 'bg-purple-50 min-h-screen flex flex-col overflow-y-scroll')">

  {{-- HEADER / NAV --}}
  @include('layouts.front-nav')

  {{-- SLIDER (full width, fuera del main) --}}
  @yield('slider')

  {{-- CONTENIDO --}}
  <main class="flex-1 container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
  </main>

  {{-- FOOTER --}}
  <footer class="bg-gray-900 text-gray-300 text-sm py-6">
    <div class="container mx-auto text-center space-y-2">
      <p>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
      <div class="flex justify-center gap-4">
        <a href="#" class="hover:text-white">Política de privacidad</a>
        <a href="#"   class="hover:text-white">Términos de uso</a>
        <a href="#" class="hover:text-white">Contacto</a>
      </div>
    </div>
  </footer>

  @stack('scripts')

  <!-- Alpine.js para dropdowns y otros componentes -->
  <script src="//unpkg.com/alpinejs" defer></script>
  {{-- Menu flotante --}}
  @include('components.front-floating-menu')
  
</body>
</html>
