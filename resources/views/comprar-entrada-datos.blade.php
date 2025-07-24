@extends('layouts.app')

{{-- Fondo degradado --}}
@section('body-class','bg-gradient-to-br from-purple-50 to-purple-100 min-h-screen flex flex-col')

@section('content')
  <div class="flex flex-1 items-center justify-center px-2">
    <div class="w-full max-w-4xl bg-white rounded-[95px] shadow-2xl px-6 sm:px-14 md:px-20 py-10 mx-auto mb-12">

      {{-- Resumen de selección previa --}}
      <div class="bg-violet-50 border-l-4 border-violet-500 rounded-2xl px-7 py-6 mb-10 shadow flex flex-col gap-3">
        <div class="flex items-center gap-3 mb-2">
          <svg class="w-6 h-6 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3M4 4h16v16H4V4z" />
          </svg>
          <span class="text-lg font-semibold text-violet-700">Resumen de tu compra</span>
        </div>
        <div class="flex justify-between items-center text-gray-700 text-base py-1">
          <span class="font-medium">Entrada:</span>
          <span>{{ $entrada->nombre }}</span>
        </div>
        <div class="flex justify-between items-center text-gray-700 text-base py-1">
          <span class="font-medium">Cantidad:</span>
          <span>{{ $cantidad }}</span>
        </div>
        <div class="flex justify-between items-center text-gray-900 text-xl font-extrabold pt-3">
          <span>Subtotal:</span>
          <span>${{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
      </div>

      {{-- Formulario --}}
      <form action="{{ route('eventos.comprar.split.storeDatos', $evento) }}"
            method="POST"
            class="space-y-8">
        @csrf
        <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">
        <input type="hidden" name="cantidad"    value="{{ $cantidad }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          {{-- Columna 1 --}}
          <div class="space-y-6">
            <div>
              <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                Nombre completo <span class="text-red-500">*</span>
              </label>
              <input id="nombre" name="nombre" type="text" required
                     class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
            </div>
            <div>
              <label for="buyer_dni" class="block text-sm font-medium text-gray-700 mb-1">
                DNI (opcional)
              </label>
              <input id="buyer_dni" name="buyer_dni" type="text"
                     class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
            </div>
          </div>

          {{-- Columna 2 --}}
          <div class="space-y-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
              </label>
              <input id="email" name="email" type="email" required
                     class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
            </div>
            <div>
              <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">
                WhatsApp (opcional)
              </label>
              <div class="flex">
                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-white text-gray-600">
                  +54
                </span>
                <input id="whatsapp" name="whatsapp" type="text" placeholder="2944 123456"
                       class="flex-1 rounded-r-lg border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:border-transparent" />
              </div>
            </div>
          </div>
        </div>

        {{-- Nota informativa --}}
        <p class="text-center text-sm text-gray-500 mb-2">
          Al finalizar, te enviaremos las entradas por <strong>Correo</strong>.
        </p>

        {{-- Botón de envío --}}
        <button type="submit"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl transition-shadow shadow-md hover:shadow-lg text-lg tracking-wide mt-2 mb-1">
          Proceder al pago
        </button>
      </form>
    </div>
  </div>
@endsection
