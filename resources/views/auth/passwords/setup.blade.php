{{-- resources/views/auth/passwords/setup.blade.php --}}
@extends('layouts.app')

@section('title', 'Configurar contraseña')
@section('body-class', 'bg-gradient-to-br from-purple-50 to-purple-100 overflow-y-scroll')

@section('content')
    <div x-data="{ loading: false, showPassword: false, showPasswordConfirmation: false }" class="max-w-md mx-auto py-12">
        <h1 class="text-2xl font-bold mb-6 text-center">Crea tu contraseña</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.setup.store') }}" @submit.prevent="loading = true; $el.submit()"
            class="space-y-6">
            @csrf

            {{-- Nueva contraseña --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Nueva contraseña
                </label>
                <div class="relative mt-1">
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required minlength="8"
                        placeholder="Introduce tu contraseña"
                        class="block w-full pr-12 px-3 py-2
                   border border-gray-300 rounded-md shadow-sm
                   focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 focus:outline-none">
                        <template x-if="!showPassword">
                            {{-- ojo tachado --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.879 9.879A3 3 0 0115 12m0 0a3 3 0 01-3 3m3-3a3 3 0 00-3-3" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </template>
                        <template x-if="showPassword">
                            {{-- ojo abierto --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </template>
                    </button>
                </div>
                <p class="mt-1 text-sm text-gray-500">Mínimo 8 caracteres.</p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmar contraseña --}}
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirmar contraseña
                </label>
                <div class="relative mt-1">
                    <input id="password_confirmation" name="password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'" required placeholder="Repite tu contraseña"
                        class="block w-full pr-12 px-3 py-2
                   border border-gray-300 rounded-md shadow-sm
                   focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 focus:outline-none">
                        <template x-if="!showPasswordConfirmation">
                            {{-- ojo tachado --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.879 9.879A3 3 0 0115 12m0 0a3 3 0 01-3 3m3-3a3 3 0 00-3-3" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </template>
                        <template x-if="showPasswordConfirmation">
                            {{-- ojo abierto --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </template>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botón de envío --}}
            <div>
                <button type="submit" :disabled="loading"
                    class="w-full flex items-center justify-center py-2 px-4
                       bg-purple-600 hover:bg-purple-700 text-white font-semibold
                       rounded-md transition focus:outline-none focus:ring-2 focus:ring-purple-500
                       disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Guardar contraseña</span>
                    <span x-show="loading" class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        <span>Cargando…</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
