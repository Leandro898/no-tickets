@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto mt-16 bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h2 class="text-2xl font-bold mb-3 text-gray-800 flex items-center gap-2">
            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
            </svg>
            Cobros / Mercado Pago
        </h2>

        <p class="text-gray-600 mb-6">
            Conectá tu cuenta de Mercado Pago para recibir los pagos directamente en tu billetera.
        </p>

        @if (auth()->check() && auth()->user()->hasMercadoPagoAccount())
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded mb-6">
                <p class="font-semibold mb-1">¡Tu cuenta de Mercado Pago ya está conectada!</p>
                <p>ID usuario MP: <span class="font-mono text-gray-700">{{ auth()->user()->mp_user_id }}</span></p>
                <p class="text-xs text-gray-500">Token actualizado:
                    {{ auth()->user()->mp_expires_in ? auth()->user()->mp_expires_in->diffForHumans() : 'N/A' }}</p>
            </div>
            <form action="{{ route('mercadopago.unlink') }}" method="POST" class="flex justify-end">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded shadow font-semibold transition">
                    Desvincular Cuenta
                </button>
            </form>
        @else
            <div class="flex flex-col items-center">
                <a href="{{ route('mercadopago.connect') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#009ee3] text-white rounded-lg shadow font-semibold hover:bg-[#0077b6] transition mb-3">
                    <!-- Logo Mercado Pago real -->
                    <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/MercadoPago_Logo.png" alt="Mercado Pago"
                        class="w-7 h-7 object-contain bg-white rounded p-0.5" />
                    Conectar con Mercado Pago
                </a>
                <p class="text-sm text-gray-500 mt-2 text-center max-w-xs">
                    Si no conectás tu cuenta, no podrás recibir pagos por tus ventas.
                </p>
            </div>
        @endif
    </div>
@endsection
