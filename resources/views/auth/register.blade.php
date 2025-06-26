{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white rounded-lg shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-purple-700 mb-6 text-center">Crear cuenta</h1>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul class="text-sm">
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-gray-700">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700">Contraseña</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700">Confirmar Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <button type="submit"
                class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 rounded transition">
                Registrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-purple-700 hover:underline">Ingresar</a>
        </p>
    </div>
</div>
@endsection
