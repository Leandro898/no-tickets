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
    <h1>Comprar entradas para: {{ $evento->nombre }}</h1>

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

        <div class="row">
            @foreach($entradas as $entrada)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $entrada->nombre }}</h5>
                            <p class="card-text">Precio: ${{ number_format($entrada->precio, 2) }}</p>
                            <p class="card-text">Stock disponible: {{ $entrada->stock_actual }}</p>

                            <div class="form-group">
                                <label for="cantidad_{{ $entrada->id }}">Cantidad:</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary minus-btn" data-entrada-id="{{ $entrada->id }}">-</button>
                                    <input type="number" class="form-control cantidad-input" id="cantidad_{{ $entrada->id }}" name="cantidades[{{ $entrada->id }}]" value="0" min="0" max="{{ $entrada->max_por_compra ?? $entrada->stock_actual }}">
                                    <button type="button" class="btn btn-outline-secondary plus-btn" data-entrada-id="{{ $entrada->id }}">+</button>
                                </div>
                                @if ($entrada->max_por_compra)
                                    <small class="form-text text-muted">Máximo {{ $entrada->max_por_compra }} por compra.</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit">Comprar</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.plus-btn').forEach(button => {
            button.addEventListener('click', function() {
                const entradaId = this.dataset.entradaId;
                const cantidadInput = document.getElementById(`cantidad_${entradaId}`);
                const max = parseInt(cantidadInput.getAttribute('max'));
                if (parseInt(cantidadInput.value) < max) {
                    cantidadInput.value = parseInt(cantidadInput.value) + 1;
                }
            });
        });

        document.querySelectorAll('.minus-btn').forEach(button => {
            button.addEventListener('click', function() {
                const entradaId = this.dataset.entradaId;
                const cantidadInput = document.getElementById(`cantidad_${entradaId}`);
                if (parseInt(cantidadInput.value) > 0) {
                    cantidadInput.value = parseInt(cantidadInput.value) - 1;
                }
            });
        });
    });
</script>