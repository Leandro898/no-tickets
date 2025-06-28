@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto bg-white p-8 mt-16 rounded-xl shadow-lg flex flex-col items-center border border-gray-200">
        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
            <!-- Ícono de error -->
            <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-red-700 mb-2">Error al vincular Mercado Pago</h2>
        <p class="text-gray-700 mb-4">
            Ocurrió un error al intentar vincular tu cuenta de Mercado Pago.
        </p>
        @if(session('error'))
            <p class="text-sm text-gray-500 mb-4">{{ session('error') }}</p>
        @endif
        <a href="https://prueba.cyberespacio.online/admin/oauth-connect-page"
           class="inline-block px-5 py-2 rounded-md bg-purple-600 text-white font-semibold shadow hover:bg-purple-700 transition">
            Volver al panel
        </a>
    </div>
@endsection
