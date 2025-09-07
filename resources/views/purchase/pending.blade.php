@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto mt-12 p-6 bg-white rounded shadow">
  <h1 class="text-2xl font-bold text-yellow-700">Pago Pendiente ⏳</h1>
  <p class="mt-4">
    Tu pago está pendiente de confirmación. Te notificaremos por correo cuando se apruebe.<br>
    <strong>Número de Orden: {{ $order->id }}</strong>
  </p>
  <button onclick="location.href='{{ route('home') }}'" class="mt-6 btn btn-primary">
    Volver al Evento
  </button>
</div>

<script>
  // Polling cada 5s para comprobar el estado
  const orderId = {{ $order->id }};
  const interval = setInterval(async () => {
    try {
      const res = await fetch(`{{ url('orders') }}/${orderId}/status`);
      const { status } = await res.json();
      console.log('Comprobando estado de orden', orderId, status);
      if (status === 'approved') {
        clearInterval(interval);
        window.location.href = `{{ url('orders') }}/${orderId}/gracias`;
      } else if (status === 'rejected') {
        clearInterval(interval);
        window.location.href = `{{ url('purchase/failure', $order->id) }}`;
      }
    } catch (e) {
      console.error('Error comprobando estado', e);
    }
  }, 5000);
</script>
@endsection
