@component('mail::message')
# ¡Gracias por tu compra! 🎉

Hola **{{ $order->user->name }}**,

¡Tu compra ha sido aprobada y tus entradas están listas!

Has adquirido entradas para el evento: **{{ $order->event->nombre }}**
Fecha del evento: **{{ \Carbon\Carbon::parse($order->event->fecha)->format('d/m/Y H:i') }}**
Total pagado: **{{ number_format($order->total_amount, 2, ',', '.') }} ARS**

---

### Tus entradas:

Hemos adjuntado **{{ $purchasedTickets->count() }}** archivos PNG a este correo. Cada archivo contiene el código QR de una de tus entradas.

**Para acceder a tus entradas:**
1. Abre los archivos adjuntos en este correo.
2. Guarda los códigos QR en tu dispositivo.
3. Presenta el QR correspondiente en la entrada del evento.

---

Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.

¡Esperamos verte allí!

Saludos,
El equipo de {{ config('app.name') }}

@component('mail::footer')
Este es un correo automático. Por favor, no respondas a esta dirección.
@endcomponent
@endcomponent