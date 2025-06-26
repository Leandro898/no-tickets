<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ config('app.name','Innova Ticket') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
  <div class="min-h-screen">

    {{-- Incluyo UNA sola vez tu menú común --}}
    @include('layouts.front-nav')

    <main class="py-12">
      @yield('content')
    </main>
  </div>
</body>
</html>
