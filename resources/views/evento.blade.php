<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $evento->titulo }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">{{ $evento->titulo }}</h1>

        <form action="{{ route('pago.comprar', $evento->id) }}" method="POST" class="space-y-4">
            @csrf

            @foreach ($evento->entradas as $entrada)
                <div class="border p-4 rounded bg-gray-50">
                    <p class="text-lg font-semibold">{{ $entrada->titulo }}</p>
                    <p class="text-gray-600 mb-2">$ {{ number_format($entrada->precio, 2) }}</p>
                    <label class="block">
                        Cantidad:
                        <input type="number" name="entradas[{{ $entrada->id }}][cantidad]" min="0" value="0" class="mt-1 block w-24 border rounded px-2 py-1">
                    </label>
                </div>
            @endforeach

            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                Continuar al Pago
            </button>
        </form>
    </div>
</body>
</html>

