{{-- resources/views/auth/passwords/setup.blade.php --}}
@extends('layouts.app')

@section('title', 'Configurar contraseña')

@section('content')
<div class="max-w-md mx-auto py-12">
    <h1 class="text-2xl font-bold mb-6 text-center">Crea tu contraseña</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.setup.store') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500"
                   placeholder="Introduce tu contraseña" />
            @error('password')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500"
                   placeholder="Repite tu contraseña" />
        </div>

        <button type="submit"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded">Guardar contraseña</button>
    </form>
</div>
@endsection
