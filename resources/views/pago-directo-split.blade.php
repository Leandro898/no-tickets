@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10 px-6 bg-white shadow-lg rounded-lg mt-10">
    <h1 class="text-3xl font-bold mb-6 text-center">Comprar entradas - {{ $evento->nombre }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('eventos.pago.directo.store', $evento) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Nombre completo</label>
            <input type="text" name="nombre" class="w-full border border-gray-300 rounded p-2" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" class="w-full border border-gray-300 rounded p-2" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Entradas</label>
            @foreach($evento->entradas as $entrada)
                <div class="flex items-center justify-between mb-2">
                    <span>{{ $entrada->titulo }} (${{ $entrada->precio }})</span>
                    <input type="number" name="entradas[{{ $entrada->id }}][cantidad]" min="0" max="10" class="w-20 border border-gray-300 rounded p-1">
                </div>
            @endforeach
        </div>

        <button type="submit" class="mt-6 w-full bg-blue-600 text-white font-semibold py-2 rounded hover:bg-blue-700">
            Ir a pagar con Mercado Pago
        </button>
    </form>
</div>
@endsection

