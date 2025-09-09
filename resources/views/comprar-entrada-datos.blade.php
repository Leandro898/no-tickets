@extends('layouts.app')

@section('body-class','bg-gradient-to-br from-purple-50 to-purple-100 min-h-screen flex flex-col')

@section('content')
<div class="flex flex-1 items-center justify-center px-4 py-8">
  {{-- Contenedor principal con más espacio lateral --}}
  <div class="w-full max-w-4xl mx-4 sm:mx-6 md:mx-8 bg-white rounded-3xl shadow-xl px-6 sm:px-12 md:px-16 py-10 mb-8">

    {{-- Resumen de selección previa --}}
    <div class="bg-violet-50 border-l-4 border-violet-500 rounded-2xl px-6 py-5 mb-10 shadow-sm flex flex-col gap-3">
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
      <div class="flex justify-between items-center text-gray-900 text-xl font-extrabold pt-3 border-t border-violet-100">
        <span>Subtotal:</span>
        <span>${{ number_format($subtotal, 0, ',', '.') }}</span>
      </div>
    </div>

    {{-- Formulario --}}
    <form id="paymentForm" action="{{ route('eventos.comprar.split.storeDatos', $evento) }}" method="POST" class="space-y-8">
      @csrf
      <input type="hidden" name="entrada_id" value="{{ $entrada->id }}">
      <input type="hidden" name="cantidad" value="{{ $cantidad }}">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
        {{-- Columna 1 --}}
        <div class="space-y-6">
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
              <span class="bg-violet-100 text-violet-700 rounded-full p-1 mr-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </span>
              Nombre completo <span class="text-red-500 ml-1">*</span>
            </label>
            <input id="nombre" name="nombre" type="text" required
              class="w-full rounded-xl border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-200"
              placeholder="Ingresa tu nombre completo" />
          </div>
          <div>
            <label for="buyer_dni" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
              <span class="bg-violet-100 text-violet-700 rounded-full p-1 mr-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
              </span>
              DNI (opcional)
            </label>
            <input id="buyer_dni" name="buyer_dni" type="text"
              class="w-full rounded-xl border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-200"
              placeholder="Número de documento" />
          </div>
        </div>

        {{-- Columna 2 --}}
        <div class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
              <span class="bg-violet-100 text-violet-700 rounded-full p-1 mr-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </span>
              Email <span class="text-red-500 ml-1">*</span>
            </label>
            <input id="email" name="email" type="email" required
              class="w-full rounded-xl border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-200"
              placeholder="tu@email.com" />
          </div>
          <div>
            <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
              <span class="bg-violet-100 text-violet-700 rounded-full p-1 mr-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </span>
              WhatsApp (opcional)
            </label>
            <div class="flex">
              <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-gray-300 bg-gray-100 text-gray-600 text-sm font-medium">
                +54
              </span>
              <input id="whatsapp" name="whatsapp" type="text" placeholder="2944 123456"
                class="flex-1 rounded-r-xl border border-gray-300 px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-200" />
            </div>
          </div>
        </div>
      </div>

      {{-- Nota informativa --}}
      <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200">
        <p class="text-center text-sm text-blue-700 flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          Al finalizar, te enviaremos las entradas por <strong class="ml-1">Correo</strong>.
        </p>
      </div>

      {{-- Botón de envío mejorado --}}
      <button id="payButton" type="submit"
        style="background-color: #7E22CE;"
        class="w-full text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-lg tracking-wide mt-2 mb-1 flex items-center justify-center transform hover:scale-[1.01] hover:bg-[#6B1BB8]">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Proceder al pago
      </button>
    </form>
  </div>
</div>

<style>
  .shadow-xl {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  }

  .shadow-sm {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }

  input:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
  }

  /* Estilos para el estado deshabilitado del botón */
  .button-disabled {
    background-color: #A78BFA !important;
    cursor: not-allowed;
    transform: scale(1) !important;
    pointer-events: none;
  }
</style>

<script>
  // Obtiene el formulario y el botón por su ID
  const form = document.getElementById('paymentForm');
  const payButton = document.getElementById('payButton');
  const originalButtonText = payButton.innerHTML;

  // Escucha el evento de envío del formulario
  form.addEventListener('submit', () => {
    // Deshabilita el botón y cambia el texto y el estilo
    payButton.disabled = true;
    payButton.innerHTML = `<svg class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Procesando...`;
    payButton.classList.add('button-disabled');
  });
</script>

@endsection