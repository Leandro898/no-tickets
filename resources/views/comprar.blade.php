<style>
    body {
        font-family: sans-serif;
        background-color: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }

    .container {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        width: 400px;
        text-align: center;
    }

    h1 {
        color: #333;
        margin-bottom: 20px;
    }

    .success-message {
        color: green;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #e6ffe6;
        border: 1px solid #b3ffb3;
        border-radius: 4px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
        text-align: left;
    }

    input[type="text"],
    input[type="email"] {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 16px;
    }

    button[type="submit"] {
        background-color: #007bff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
        background-color: #0056b3;
    }
</style>

<div class="container">
    <h1>Comprar entrada para: {{ $evento->nombre }}</h1>

    @if(session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('comprar.entrada.store', $evento->id) }}" method="POST">
        @csrf

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required><br>

        <label for="entrada_id">Selecciona tu entrada:</label>
        <select name="entrada_id" id="entrada_id" required>
            @foreach($entradas as $entrada)
                <option value="{{ $entrada->id }}">
                    {{ $entrada->nombre }} - ${{ number_format($entrada->precio, 2) }}
                </option>
            @endforeach
        </select><br>

        <button type="submit">Comprar</button>
    </form>
</div>