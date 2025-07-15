{{-- resources/views/eventos/show.blade.php --}}
@extends('layouts.app')

{{-- Meta título --}}
@section('title', $evento->nombre.' – Detalles del Evento')

{{-- Forzamos scroll vertical y degradado de fondo --}}
@section('body-class', 'bg-gradient-to-br from-purple-50 min-h-screen overflow-y-scroll')

@push('styles')
  <style>
    /* Quitar flechas de inputs numéricos */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
  </style>
@endpush

@section('content')
  <div class="max-w-7xl mx-auto px-4 pt-6 pb-8">
    {{-- Botón "Volver a Eventos" --}}
    <div class="flex justify-end mb-4">
      <a href="/"
         class="inline-flex items-center gap-2 bg-white hover:bg-purple-50 border border-purple-200
                text-purple-700 font-semibold px-4 py-2 rounded-lg shadow transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 19l-7-7 7-7" />
        </svg>
        Volver a eventos
      </a>
    </div>

    {{-- Título del evento --}}
    <h1 class="text-3xl sm:text-4xl font-extrabold text-purple-700 text-center mb-6">
      {{ $evento->nombre }}
    </h1>

    <div class="flex flex-col lg:flex-row items-start gap-8">
      {{-- Columna izquierda: imagen + descripción --}}
      <div class="w-full lg:w-1/2 space-y-6">
        {{-- Banner --}}
        <div class="flex justify-center">
          @if($evento->imagen)
            <img
              src="{{ asset('storage/'.$evento->imagen) }}"
              alt="Banner de {{ $evento->nombre }}"
              class="w-full max-w-xs md:max-w-sm object-contain rounded-lg shadow-lg"
            />
          @else
            <div class="w-64 h-40 bg-gray-100 flex items-center justify-center rounded-lg shadow-lg">
              <span class="text-gray-400">Sin imagen disponible</span>
            </div>
          @endif
        </div>

        {{-- Acerca del evento --}}
        <div class="bg-white rounded-2xl p-6 shadow-lg">
          <h2 class="text-2xl font-bold text-purple-700 mb-3">Acerca del evento</h2>
          <p class="text-gray-800">{{ $evento->descripcion }}</p>
        </div>
      </div>

      {{-- Columna derecha: contador + entradas --}}
      <div class="w-full lg:w-1/2 space-y-6" x-data>
        {{-- Contador dinámico --}}
        <div class="text-center">
          <span id="countdown"
                class="inline-block bg-purple-600 text-white font-semibold px-5 py-3 rounded-lg shadow-lg">
            Cargando…
          </span>
        </div>

        {{-- Entradas desde (compacto y centrado horizontal) --}}
<div class="flex justify-center">
  <div class="bg-purple-600 text-white rounded-xl p-2 text-center shadow max-w-xs w-full">
    <div class="text-xs sm:text-sm font-medium">Entradas desde</div>
    <div class="text-lg sm:text-xl font-extrabold my-0.5">
      ${{ number_format($evento->entradas->min('precio'), 0, ',', '.') }}
    </div>
  </div>
</div>




        {{-- Formulario de compra split --}}
        @foreach($evento->entradas as $entrada)
          <form action="{{ route('eventos.comprar.split.store', $evento) }}"
                method="POST" x-data="{ qty: 1 }"
                class="bg-white rounded-xl p-5 shadow border border-purple-100">
            @csrf
            <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">

            {{-- Fecha y hora --}}
            <div class="text-sm font-bold text-gray-700 mb-2">
              {{ \Carbon\Carbon::parse($evento->fecha_inicio)
                   ->locale('es')
                   ->translatedFormat('l d \\d\\e F, H:i') }} hs
            </div>

            {{-- Nombre y precio --}}
            <div class="flex justify-between items-center mb-4">
              <div class="text-lg font-semibold text-gray-800">{{ $entrada->nombre }}</div>
              <div class="text-lg font-bold text-gray-900">
                ${{ number_format($entrada->precio, 0, ',', '.') }}
              </div>
            </div>

            {{-- Stepper --}}
            <div class="flex justify-center items-center space-x-4 mb-4">
              <button type="button" @click="qty = Math.max(1, qty - 1)"
                      class="w-10 h-10 bg-purple-100 hover:bg-purple-200 rounded-lg text-purple-700 font-bold text-xl transition-shadow shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                −
              </button>
              <input type="number" name="cantidad" x-model.number="qty"
                     min="1" max="{{ $entrada->stock_actual }}"
                     class="w-16 h-10 text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" />
              <button type="button"
                      @click="qty = Math.min({{ $entrada->stock_actual }}, qty + 1)"
                      class="w-10 h-10 bg-purple-100 hover:bg-purple-200 rounded-lg text-purple-700 font-bold text-xl transition-shadow shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                +
              </button>
            </div>

            {{-- Total y botón Comprar (destacado) --}}
            <div class="flex justify-between items-center">
              <div class="text-gray-700 font-medium">
                Total:
                <span class="font-bold"
                      x-text="`$${(qty * {{ $entrada->precio }}).toLocaleString('de-DE')}`">
                </span>
              </div>
              <button type="submit"
                      class="bg-green-500 hover:bg-green-600 text-white font-extrabold text-lg px-6 py-3 rounded-full transition-shadow shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-400">
                Comprar
              </button>
            </div>
          </form>
        @endforeach
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  {{-- Alpine.js para stepper --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  {{-- Contador dinámico --}}
  <script>
    (function(){
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
