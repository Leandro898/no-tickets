@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="bg-white shadow-xl rounded-xl w-full max-w-4xl p-8 border border-purple-100">
        <h2 class="text-2xl font-bold text-purple-700 mb-8 text-center">Datos del comprador</h2>

        <form action="{{ route('eventos.comprar.split.store', $evento->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                <input type="text" name="nombre" class="border border-gray-300 rounded-md p-3 focus:ring-purple-500 focus:border-purple-500" required>
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-700 mb-1">DNI *</label>
                <input type="text" name="buyer_dni" class="border border-gray-300 rounded-md p-3 focus:ring-purple-500 focus:border-purple-500" required>
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-700 mb-1">WhatsApp (opcional)</label>
                <input type="text" name="whatsapp" placeholder="+54..." class="border border-gray-300 rounded-md p-3 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" class="border border-gray-300 rounded-md p-3 focus:ring-purple-500 focus:border-purple-500" required>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-800 mb-2 block">Seleccioná tus entradas</label>

                <select name="entradas[0][id]" class="w-full border border-gray-300 rounded-md p-3 focus:ring-purple-500 focus:border-purple-500 mb-3">
                    @foreach ($evento->entradas as $entrada)
                    <option value="{{ $entrada->id }}">
                        {{ $entrada->nombre }} — ${{ number_format($entrada->precio, 2, ',', '.') }}
                    </option>
                    @endforeach
                </select>

                <input type="number" name="entradas[0][cantidad]" min="1" value="1" class="w-full border border-gray-300 rounded-md p-3 focus:ring-purple-500 focus:border-purple-500" required>
            </div>


            <div class="md:col-span-2 pt-4">
                <p class="text-center text-sm text-gray-600 mb-4">
                    Al finalizar, te enviaremos las entradas por <strong>Correo</strong> y <strong>WhatsApp</strong>.
                </p>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-md transition">
                    Proceder al pago
                </button>
            </div>
        </form>
    </div>
</div>
@endsection