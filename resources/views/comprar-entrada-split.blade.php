@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Comprar entradas para: {{ $evento->titulo }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('eventos.comprar.split.store', $evento) }}">
        @csrf

        <div class="mb-4">
            <label for="nombre" class="block text-sm font-medium">Nombre completo</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium">Email</label>
            <input type="email" name="email" id="email" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label for="buyer_dni" class="block text-sm font-medium">DNI</label>
            <input type="text" name="buyer_dni" id="buyer_dni" class="w-full border rounded p-2">
        </div>

        <h2 class="text-lg font-semibold mt-6 mb-2">Seleccioná tus entradas</h2>

        @foreach ($evento->entradas as $entrada)
            <div class="mb-3 border rounded p-3">
                <p><strong>{{ $entrada->titulo }}</strong> — ${{ number_format($entrada->precio, 2) }}</p>
                <label for="entrada-{{ $entrada->id }}" class="block mt-2 text-sm">Cantidad:</label>
                <input type="number" min="0" name="entradas[{{ $entrada->id }}][cantidad]" id="entrada-{{ $entrada->id }}" class="w-24 border rounded p-1" value="0">
                <input type="hidden" name="entradas[{{ $entrada->id }}][id]" value="{{ $entrada->id }}">
            </div>
        @endforeach

        <button type="submit" class="mt-6 w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Proceder al pago</button>
    </form>
</div>
@endsection

