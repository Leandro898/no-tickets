{{-- resources/views/comprar-entrada-datos.blade.php --}}
@extends('layouts.app')

{{-- Pintamos el fondo con tu degradado púrpura claro --}}
@section('body-class','bg-gradient-to-br from-purple-50 to-purple-100')

@section('content')
  {{-- Este wrapper se desplazará al centro superior de la ventana al cargar --}}
  <div
    id="comprador"
    x-data
    x-init="$nextTick(() => {
      document
        .getElementById('comprador')
        .scrollIntoView({ behavior: 'smooth', block: 'start' });
    })"
    class="min-h-screen flex items-start justify-center px-4 py-6"
  >
    <div class="w-full max-w-3xl bg-white rounded-3xl shadow-lg p-6">
      {{-- Título --}}
      <h2 class="text-4xl font-extrabold text-purple-700 text-center mb-6">
        Datos del comprador
      </h2>

      {{-- Resumen de selección previa --}}
      <div class="bg-gray-50 rounded-xl p-5 mb-6">
        <div class="flex justify-between mb-2 text-gray-700">
          <span class="font-medium">Entrada:</span>
          <span>{{ $entrada->nombre }}</span>
        </div>
        <div class="flex justify-between mb-2 text-gray-700">
          <span class="font-medium">Cantidad:</span>
          <span>{{ $cantidad }}</span>
        </div>
        <div class="flex justify-between text-gray-900 font-semibold">
          <span>Subtotal:</span>
          <span>${{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
      </div>

      {{-- Formulario en grid responsivo --}}
      <form action="{{ route('eventos.comprar.split.storeDatos', $evento) }}"
            method="POST"
            class="space-y-6">
        @csrf
        <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">
        <input type="hidden" name="cantidad"    value="{{ $cantidad }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Columna 1 --}}
          <div class="space-y-4">
            <div>
              <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                Nombre completo <span class="text-red-500">*</span>
              </label>
              <input id="nombre" name="nombre" type="text" required
                     class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
            </div>
            <div>
              <label for="buyer_dni" class="block text-sm font-medium text-gray-700 mb-1">
                DNI (opcional)
              </label>
              <input id="buyer_dni" name="buyer_dni" type="text"
                     class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
            </div>
          </div>

          {{-- Columna 2 --}}
          <div class="space-y-4">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
              </label>
              <input id="email" name="email" type="email" required
                     class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
            </div>
            <div>
              <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">
                WhatsApp (opcional)
              </label>
              <div class="flex">
                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-white text-gray-600">
                  +54
                </span>
                <input id="whatsapp" name="whatsapp" type="text" placeholder="11 1234-5678"
                       class="flex-1 rounded-r-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
              </div>
            </div>
          </div>
        </div>

        {{-- Nota informativa --}}
        <p class="text-center text-sm text-gray-500">
          Al finalizar, te enviaremos las entradas por <strong>Correo</strong> y <strong>WhatsApp</strong>.
        </p>

        {{-- Botón de envío full-width --}}
        <button type="submit"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg transition-shadow shadow-md hover:shadow-lg">
          Proceder al pago
        </button>
      </form>
    </div>
  </div>
@endsection
