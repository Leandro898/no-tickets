{{-- resources/views/eventos/checkout-seats.blade.php --}}
@extends('layouts.app')

@section('content')
  <div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">
      Seleccioná tus asientos para: {{ $evento->nombre }}
    </h1>

    <div
      id="seat-checkout-app"
      data-evento-id="{{ $evento->id }}"
      data-purchase-route="{{ route('eventos.checkout-seats.store', $evento) }}"
      class="w-full h-[600px] bg-white border rounded-lg shadow-lg"
    ></div>
  </div>
@endsection

@push('scripts')
  @vite('resources/js/app.js')
@endpush
