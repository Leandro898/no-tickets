@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
  <div class="max-w-md w-full bg-white shadow-xl rounded-xl p-8">
    <h2 class="text-2xl font-extrabold text-gray-900 mb-6 text-center">
      {{ __('Crear cuenta') }}
    </h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
      @csrf

      <!-- Nombre -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
          {{ __('Nombre') }}
        </label>
        <input
          id="name"
          name="name"
          type="text"
          value="{{ old('name') }}"
          required
          autofocus
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
        >
        @error('name')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
          {{ __('Correo electrónico') }}
        </label>
        <input
          id="email"
          name="email"
          type="email"
          value="{{ old('email') }}"
          required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
        >
        @error('email')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Botón -->
      <div>
        <button
          type="submit"
          class="w-full flex justify-center py-2 px-4 bg-purple-600 hover:bg-purple-700
                 text-white font-semibold rounded-md transition focus:outline-none focus:ring-2
                 focus:ring-offset-2 focus:ring-purple-500"
        >
          {{ __('Recibir enlace mágico') }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
