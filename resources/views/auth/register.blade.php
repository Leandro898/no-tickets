{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('body-class', 'bg-gradient-to-br from-purple-50 to-purple-100 overflow-y-scroll')

@section('content')
<div x-data="{ role: 'cliente' }" class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white rounded-xl shadow-xl p-8">

        {{-- Selección de tipo de cuenta --}}
        <div class="flex justify-center mb-4">
            <button
                :class="role === 'cliente' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-800'"
                class="px-4 py-2 rounded-l-lg font-semibold focus:outline-none"
                @click="role = 'cliente'">
                Cliente
            </button>
            <button
                :class="role === 'productor' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-800'"
                class="px-4 py-2 rounded-r-lg font-semibold focus:outline-none"
                @click="role = 'productor'">
                Productor
            </button>
        </div>

        {{-- Texto explicativo dinámico --}}
        <div class="text-center text-base text-gray-700 font-medium mb-6" x-cloak>
            <template x-if="role === 'cliente'">
                <span>
                    ¿Solo quieres comprar entradas?  
                    Regístrate como cliente para acceder a tus compras, recibir tus tickets y disfrutar de los eventos fácilmente.
                </span>
            </template>
            <template x-if="role === 'productor'">
                <span>
                    ¿Quieres organizar y vender tus propios eventos?  
                    Regístrate como productor para publicar, gestionar y controlar la venta de entradas de tus eventos.
                </span>
            </template>
        </div>

        @if ($errors->any())
    <div class="mb-4 text-red-600">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


        {{-- Formulario --}}
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="role" :value="role">

            {{-- Nombre --}}
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
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                        focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                >
                @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Correo electrónico --}}
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
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                        focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                >
                @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Campos extra solo para productor --}}
            <div x-show="role === 'productor'" x-transition>
                <label for="telefono" class="block text-sm font-medium text-gray-700">
                    Teléfono de contacto
                </label>
                <input
                    id="telefono"
                    name="telefono"
                    type="text"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                        focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                >
                {{-- Puedes agregar más campos, ej: nombre del emprendimiento, web, etc. --}}
            </div>

            {{-- Botón --}}
            <div>
                <button
                    type="submit"
                    class="w-full flex justify-center py-2 px-4 bg-purple-600 hover:bg-purple-700
                        text-white font-semibold rounded-md transition focus:outline-none
                        focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                >
                    <span x-text="role === 'cliente' ? 'Crear cuenta como Cliente' : 'Crear cuenta como Productor'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
