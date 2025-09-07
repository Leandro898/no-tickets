{{-- resources/views/eventos/checkout-form.blade.php --}}

@extends('layouts.app')

@section('content')
  <div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Confirmar compra: {{ $evento->nombre }}</h1>

    <section>
      <h2 class="text-xl font-semibold">Asientos seleccionados</h2>
      <ul class="list-disc list-inside">
        @foreach($seats as $seat)
          <li>Asiento #{{ $seat->id }} (fila {{ $seat->row }}, número {{ $seat->number }})</li>
        @endforeach
      </ul>
    </section>

    <p class="text-lg">
      <strong>Total:</strong> ${{ number_format($total, 2) }}
    </p>

    <form action="{{ route('orders.create') }}" method="POST" class="space-y-4">
      @csrf

      {{-- Datos ocultos --}}
      <input type="hidden" name="event_id" value="{{ $evento->id }}">
      @foreach($seats as $seat)
        <input type="hidden" name="seats[]" value="{{ $seat->id }}">
      @endforeach

      {{-- Campos de comprador --}}
      <div>
        <label for="buyer_full_name" class="block font-medium">Nombre completo:</label>
        <input
          id="buyer_full_name"
          name="buyer_full_name"
          type="text"
          required
          class="w-full border px-3 py-2 rounded"
        >
      </div>

      <div>
        <label for="buyer_email" class="block font-medium">Email:</label>
        <input
          id="buyer_email"
          name="buyer_email"
          type="email"
          required
          class="w-full border px-3 py-2 rounded"
        >
      </div>

      <div>
        <label for="buyer_phone" class="block font-medium">Teléfono (opcional):</label>
        <input
          id="buyer_phone"
          name="buyer_phone"
          type="text"
          class="w-full border px-3 py-2 rounded"
        >
      </div>

      <div>
        <label for="buyer_dni" class="block font-medium">DNI (opcional):</label>
        <input
          id="buyer_dni"
          name="buyer_dni"
          type="text"
          class="w-full border px-3 py-2 rounded"
        >
      </div>

      <button
        type="submit"
        class="w-full bg-violet-600 text-white py-2 rounded hover:bg-violet-700"
      >
        Pagar ${{ number_format($total, 2) }}
      </button>
    </form>
  </div>
@endsection
