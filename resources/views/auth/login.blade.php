{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

{{-- Esto hará que el <body> use tu degradado púrpura en lugar de gris --}}
@section('body-class', 'bg-gradient-to-br from-purple-50 to-purple-100')

@section('content')
<div class="flex items-center justify-center min-h-screen px-4 py-12">
    <div class="bg-white rounded-lg shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-purple-700 mb-6 text-center">Ingresar</h1>

        {{-- Errores de validación --}}
        @if($errors->any())
            <div class="mb-4 text-red-600">
                <ul class="text-sm list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}"
                       required autofocus
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600" />
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input id="password" name="password" type="password"
                       required
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600" />
            </div>

            {{-- Remember + Olvidaste --}}
            <div class="flex items-center justify-between mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember"
                           class="rounded text-purple-600 focus:ring-purple-500 border-gray-300" />
                    <span class="ml-2 text-sm text-gray-600">Mantener conectado</span>
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-purple-700 hover:underline">
                        Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            {{-- Botón --}}
            <button type="submit"
                    class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 rounded-lg transition-shadow shadow-md hover:shadow-lg">
                Ingresar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="text-purple-700 hover:underline">Registrar</a>
        </p>
    </div>
</div>
@endsection
