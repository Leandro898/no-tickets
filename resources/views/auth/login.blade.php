{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

{{-- Título de la página --}}
@section('title', 'Ingresar')

{{-- Aplicar el mismo fondo degradado que en otras vistas --}}
@section('body-class', 'bg-gradient-to-br from-purple-50 to-purple-100')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full bg-white shadow-xl rounded-xl p-8">
    <h2 class="text-2xl font-extrabold text-gray-900 mb-6 text-center">
      Ingresar
    </h2>

    {{-- Mostrar errores generales --}}
    @if($errors->any())
      <div class="mb-4 text-red-600 text-sm">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
      @csrf

      {{-- Email --}}
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
          Email
        </label>
        <input
          id="email"
          name="email"
          type="email"
          value="{{ old('email') }}"
          required
          autofocus
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                 focus:outline-none focus:ring-purple-500 focus:border-purple-500"
        />
      </div>

      {{-- Password con ojito --}}
      <div class="relative">
        <label for="password" class="block text-sm font-medium text-gray-700">
          Password
        </label>
        <input
          id="password"
          name="password"
          type="password"
          required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                 focus:outline-none focus:ring-purple-500 focus:border-purple-500 pr-12" 
        />
        {{-- Botón ojo con Iconify --}}
        <button type="button" id="togglePassword" 
                class="absolute right-0 pr-3 flex items-center text-gray-500
                       top-1/2 transform -translate-y-1/2">
            <iconify-icon icon="heroicons:eye" id="eye-icon" class="text-2xl"></iconify-icon>
        </button>
      </div>

      {{-- Remember + Olvidaste --}}
      <div class="flex items-center justify-between">
        <label class="inline-flex items-center text-sm text-gray-700">
          <input
            type="checkbox"
            name="remember"
            class="rounded text-purple-600 focus:ring-purple-500 border-gray-300"
          />
          <span class="ml-2">Mantener conectado</span>
        </label>

        @if(Route::has('password.request'))
          <a
            href="{{ route('password.request') }}"
            class="text-sm text-purple-700 hover:underline ml-4"
          >
            Olvidaste tu contraseña?
          </a>
        @endif
      </div>

      {{-- Botón Ingresar --}}
      <div>
        <button
          type="submit"
          class="w-full flex justify-center py-2 px-4 bg-purple-600 hover:bg-purple-700
                 text-white font-semibold rounded-md transition focus:outline-none
                 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
          Ingresar
        </button>
      </div>
    </form>

    {{-- Link a registro --}}
    <p class="mt-6 text-center text-sm text-gray-600">
      ¿No tienes cuenta?
      <a
        href="{{ route('register') }}"
        class="text-purple-700 hover:underline"
      >
        Registrar
      </a>
    </p>
  </div>
</div>

{{-- Scripts --}}
<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

{{-- Script para mostrar/ocultar contraseña --}}
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    
    passwordInput.setAttribute('type', type);

    // Cambiamos el icono según el estado
    if (type === 'password') {
        eyeIcon.setAttribute('icon', 'heroicons:eye');
    } else {
        eyeIcon.setAttribute('icon', 'heroicons:eye-slash');
    }
});
</script>
@endsection