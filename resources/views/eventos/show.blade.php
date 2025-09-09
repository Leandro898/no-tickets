{{-- resources/views/eventos/show.blade.php --}}
@extends('layouts.app')

@section('title', $evento->nombre.' – Detalles del Evento')
@section('body-class', 'bg-gray-50 min-h-screen') {{-- sin scroll forzado --}}

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">

  {{-- Botón volver y Título --}}
  <div class="mb-4 lg:mb-12">
    <div class="flex justify-end mb-8">
      <a href="/" class="bg-white border border-purple-200 text-purple-700 px-4 py-2 rounded-lg shadow hover:bg-purple-50 transition">
        ← Volver a eventos
      </a>
    </div>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-purple-700 text-center pb-16">
      {{ $evento->nombre }}
    </h1>
  </div>

  {{-- Contenedor principal con 2 columnas y separación --}}
  <div class="flex flex-col lg:flex-row lg:space-x-10">

    {{-- Columna izquierda (solo imagen) --}}
    <div class="lg:w-1/2 flex flex-col gap-8">
      {{-- Imagen --}}
      <div class="w-full flex justify-center lg:justify-start">
        @if($evento->imagen)
        <img src="{{ asset('storage/'.$evento->imagen) }}" alt="{{ $evento->nombre }}"
          class="rounded-lg shadow-lg max-w-full object-cover" />
        @else
        <div class="w-64 h-40 bg-gray-200 flex items-center justify-center rounded-lg shadow">
          Sin imagen disponible
        </div>
        @endif
      </div>
    </div>

    {{-- Separación al medio --}}
    <div class="hidden lg:block w-10"></div>

    {{-- Columna derecha (contador, entradas y descripción) --}}
    <div class="lg:w-1/2 flex flex-col gap-6 mt-8 lg:mt-0">

      {{-- Contador dinámico --}}
      <div class="text-center mb-4">
        <span id="countdown" class="inline-block bg-purple-600 text-white px-5 py-3 rounded-lg font-semibold shadow">
          Cargando…
        </span>
      </div>

      {{-- Entradas --}}
      @foreach($evento->entradas as $entrada)
      @if($entrada->stock_actual > 0)
      <form action="{{ route('eventos.comprar.split.store', $evento) }}" method="POST" x-data="{ qty: 1 }" class="bg-white p-5 rounded-xl shadow border border-purple-100">
        @csrf
        <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">

        {{-- Nombre y precio --}} {{-- Tipo de entrada --}}
        <div class="flex justify-between mb-1">
          <div class="mb-4">
            <span class="font-bold text-gray-800">Tipo de entrada:</span> <span class="font-medium text-gray-600">{{ $entrada->nombre }}</span>
          </div>
          <span class="font-bold">${{ number_format($entrada->precio,0,',','.') }}</span>
        </div>

        {{-- Stepper cantidad sin modificadores --}}
        <div class="flex justify-center items-center space-x-4 my-6">
          <button type="button" @click="qty = Math.max(1, qty - 1)"
            class="w-10 h-10 bg-purple-100 hover:bg-purple-200 rounded-lg text-purple-700 font-bold text-xl">
            −
          </button>
          <input type="number" name="cantidad" x-model.number="qty"
            min="1" max="{{ $entrada->stock_actual }}"
            class="w-16 h-10 text-center border border-gray-300 rounded-lg appearance-none" readonly />
          <button type="button" @click="qty = Math.min({{ $entrada->stock_actual }}, qty + 1)"
            class="w-10 h-10 bg-purple-100 hover:bg-purple-200 rounded-lg text-purple-700 font-bold text-xl">
            +
          </button>
        </div>

        {{-- Total y botón Comprar --}}
        <div class="flex justify-between items-center">
          <div class="text-gray-700 font-medium">
            Total:
            <span class="font-bold" x-text="`$${(qty * {{ $entrada->precio }}).toLocaleString('de-DE')}`"></span>
          </div>
          <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold">
            Comprar
          </button>
        </div>
      </form>
      @else
      <div class="bg-red-50 text-red-600 rounded-xl p-5 shadow border border-red-100 text-center mb-4">
        Entradas agotadas.
      </div>
      @endif
      @endforeach

      {{-- Descripción --}}
      <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold text-purple-700 mb-3">Acerca del evento</h2>
        <p class="text-gray-800">{{ $evento->descripcion }}</p>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- Alpine.js para stepper --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

{{-- Contador dinámico --}}
<script>
  (function() {
    const target = new Date("{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('Y/m/d H:i:s') }}").getTime();
    const el = document.getElementById('countdown');

    function update() {
      const now = Date.now();
      const diff = target - now;
      if (diff <= 0) {
        el.textContent = '¡El evento ha comenzado!';
        clearInterval(timer);
        return;
      }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      el.textContent = `Faltan ${d} d | ${h} h | ${m} m | ${s} s`;
    }

    update();
    const timer = setInterval(update, 1000);
  })();
</script>
@endpush