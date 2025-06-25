{{-- resources/views/inicio.blade.php --}}
<x-guest-layout>
    <div class="max-w-xl mx-auto mt-24 bg-white p-10 rounded-lg shadow text-center">
      <h1 class="text-4xl font-bold text-purple-700 mb-4">Bienvenido a Innova Ticket</h1>
      <p class="text-gray-700 text-lg mb-6">
        Tu plataforma para vender entradas fácil y rápido.
      </p>
      <a href="{{ route('eventos.index') }}"
         class="inline-block bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-6 rounded">
        Ver eventos
      </a>
    </div>
  </x-guest-layout>
  