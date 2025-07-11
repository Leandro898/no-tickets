{{-- resources/views/comprar-entrada-datos.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center px-4 py-12">
    <div class="bg-white bg-opacity-95 border border-purple-100 rounded-2xl shadow-xl w-full max-w-2xl p-8">
        <h2 class="text-3xl font-extrabold text-purple-800 mb-6 text-center">Datos del comprador</h2>

        {{-- Resumen de selección previa --}}
        <div class="mb-8 bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between text-gray-700 mb-2">
                <span class="font-medium">Entrada:</span>
                <span>{{ $entrada->nombre }}</span>
            </div>
            <div class="flex justify-between text-gray-700 mb-2">
                <span class="font-medium">Cantidad:</span>
                <span>{{ $cantidad }}</span>
            </div>
            <div class="flex justify-between text-gray-900 font-bold text-lg">
                <span>Subtotal:</span>
                <span>${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Aquí apuntamos a storeDatos -->
        <form action="{{ route('eventos.comprar.split.storeDatos', $evento) }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">
            <input type="hidden" name="cantidad"    value="{{ $cantidad }}">

            <div>
                <label for="nombre" class="block text-sm font-medium text-purple-700 mb-1">Nombre completo *</label>
                <input id="nombre" name="nombre" type="text" required
                       class="w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-purple-700 mb-1">Email *</label>
                <input id="email" name="email" type="email" required
                       class="w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div>
                <label for="buyer_dni" class="block text-sm font-medium text-purple-700 mb-1">DNI (opcional)</label>
                <input id="buyer_dni" name="buyer_dni" type="text"
                       class="w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div>
                <label for="whatsapp" class="block text-sm font-medium text-purple-700 mb-1">WhatsApp (opcional)</label>
                <input id="whatsapp" name="whatsapp" type="text" placeholder="+54..."
                       class="w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div class="pt-4">
                <p class="text-center text-sm text-gray-600 mb-4">
                    Al finalizar, te enviaremos las entradas por <strong>Correo</strong> y <strong>WhatsApp</strong>.
                </p>
                <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg transition">
                    Proceder al pago
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
