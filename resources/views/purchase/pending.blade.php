<<<<<<< HEAD
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Pendiente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <h1 class="text-3xl font-bold text-yellow-600 mb-4">Pago Pendiente ⏳</h1>
        <p class="text-gray-700 mb-4">
            Tu pago está pendiente de confirmación. Te notificaremos por correo electrónico cuando se apruebe.
        </p>
        <p class="text-gray-700 mb-2">
            Número de Orden: <span class="font-bold">{{ $order->id }}</span>
        </p>
        <a href="{{ route('eventos.show', $order->event_id) }}"
           class="mt-8 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Volver al Evento
        </a>
    </div>

    <script>
        const orderId      = {{ $order->id }};
        const checkInterval= 5000;

        async function checkStatus() {
            console.log('🔄 Comprobando estado de orden', orderId);
            try {
                const res  = await fetch(`/api/orders/${orderId}/status`);
                console.log('Respuesta HTTP:', res.status);
                if (!res.ok) throw new Error(res.statusText);
                const json = await res.json();
                console.log('JSON recibido:', json);
                if (json.status && json.status !== 'pending') {
                    window.location.href = `/purchase/${json.status}/${orderId}`;
                }
            } catch (err) {
                console.error('Error en checkStatus:', err);
            }
        }

        setInterval(checkStatus, checkInterval);
        </script>

</body>
</html>
=======
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
>>>>>>> ajustes-seats
