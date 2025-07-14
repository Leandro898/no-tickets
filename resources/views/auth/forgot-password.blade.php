{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.app')

{{-- Usamos tu degradado en todo el body --}}
@section('body-class', 'bg-gradient-to-br from-purple-50 to-purple-100')

@section('content')
<div class="flex items-center justify-center min-h-screen px-4 py-12">
  <div class="bg-white rounded-3xl shadow-lg w-full max-w-md p-8">
    <h1 class="text-3xl font-extrabold text-purple-700 mb-6 text-center">
      Recuperar contraseña
    </h1>

    {{-- 1) Aquí interceptamos el status de la sesión --}}
    @if(session('status'))
      <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
        {{ session('status') === 'passwords.sent'
           ? '¡Enlace para recuperar tu contraseña enviado con éxito!
           Revisa tu Email'
           : session('status')
        }}
      </div>
    @endif

    {{-- 2) Formulario de envío de email --}}
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
      @csrf

      <div>
        <label for="email" class="block text-gray-700 mb-1">Email registrado</label>
        <input id="email" name="email" type="email" required autofocus
               class="w-full rounded-lg border border-gray-300 px-4 py-2
                      focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" />
      </div>

      <button type="submit"
              class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold
                     py-2 rounded-lg shadow transition">
        Enviar enlace
      </button>
    </form>

    <div class="mt-4 text-center">
      <a href="{{ route('login') }}"
         class="text-purple-700 hover:underline text-sm">
        ← Volver a Ingresar
      </a>
    </div>
  </div>
</div>
@endsection
