{{-- resources/views/orders/thankyou.blade.php --}}
@extends('layouts.app')

@section('content')
  <div class="max-w-2xl mx-auto py-8 space-y-6">
    <h1 class="text-3xl font-bold">¡Gracias por tu compra!</h1>

    <p>Tu orden <strong>#{{ $order->id }}</strong> se ha generado con éxito.</p>

    <p>
      <strong>Total pagado:</strong>
      ${{ number_format($order->total_amount, 2) }}
    </p>

    <p>
      Hemos enviado todos los detalles de tu compra (entradas, códigos QR y acceso) al
      correo <strong>{{ $order->buyer_email }}</strong>. 
      Si no lo ves, revisá la bandeja de spam o correo no deseado.
    </p>

    <a
      href="{{ route('mis-entradas') }}"
      class="inline-block mt-4 px-6 py-2 bg-violet-600 text-white rounded hover:bg-violet-700"
    >
      Ver mis entradas
    </a>
  </div>
@endsection
