@extends('layouts.app')

@section('content')
    {{-- Mensaje informativo --}}
    <div class="max-w-4xl mx-auto mt-8 mb-6">
        <p class="text-gray-700 text-lg text-center">
            Tus entradas han sido enviadas por correo electrónico al confirmar el pago.<br>
            También podés verlas y descargarlas desde aquí.
        </p>
    </div>

    {{-- Tabla de tickets --}}
    <div class="max-w-6xl mx-auto px-2">
        <div class="overflow-x-auto bg-white shadow-md border border-gray-200 rounded-2xl">
            <table class="w-full divide-y divide-gray-200 text-[15px] text-center">
                <thead class="bg-gray-100 font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Banner</th>
                        <th class="px-4 py-3">Evento</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Lugar y Fecha</th>
                        <th class="px-4 py-3">Valor</th>
                        <th class="px-4 py-3">eTicket</th>
                        <th class="px-4 py-3">Opciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        @php
                            $evento = optional($ticket->entrada)->evento;
                            $cantidad = json_decode($ticket->order->items_data)[0]->cantidad ?? 1;
                            $mensaje = rawurlencode(
                                "¡Hola! Gracias por tu compra en *Tickets Pro*.

                        *Evento:* {$evento->nombre}
                        *Lugar:* {$evento->ubicacion}
                        *Fecha:* " .
                                    \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') .
                                    "

                        *Podés descargar tu entrada en PDF desde este enlace:*",
                            );
                            $link = route('ticket.view', ['ticket' => $ticket->id]);
                            $whatsappUrl = "https://wa.me/{$ticket->order->whatsapp_number}?text={$mensaje}%0A{$link}";
                        @endphp
                        <tr class="hover:bg-violet-50/70 transition rounded-xl shadow-sm">
                            <td class="px-4 py-3 align-middle">
                                <img src="{{ asset('storage/' . ($evento->imagen ?? 'placeholder.jpg')) }}" alt="Afiche"
                                    class="w-16 h-16 object-cover rounded-xl shadow-sm mx-auto border border-gray-300">
                            </td>
                            <td class="px-4 py-3 align-middle text-left">
                                <div class="font-extrabold text-violet-700 text-base leading-tight">{{ $evento->nombre ?? 'Evento no disponible' }}</div>
                                <div class="inline-block px-2 py-1 mt-1 text-xs bg-green-100 text-green-600 rounded font-semibold">Vigente</div>
                            </td>
                            <td class="px-4 py-3 align-middle font-medium">{{ $ticket->ticket_type }}</td>
                            <td class="px-4 py-3 align-middle">{{ $cantidad }}</td>
                            <td class="px-4 py-3 align-middle text-left">
                                <span class="block font-semibold text-gray-800">{{ $evento->ubicacion ?? '-' }}</span>
                                <span class="block text-xs text-gray-500">{{ $evento?->fecha_inicio ? \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') : '-' }}</span>
                            </td>
                            <td class="px-4 py-3 align-middle font-semibold text-gray-800">${{ number_format($ticket->order->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 align-middle font-mono font-bold text-base text-violet-700 tracking-wide">{{ $ticket->short_code }}</td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex flex-row gap-2 items-center justify-center">
                                    {{-- BOTÓN eTICKET --}}
                                    <a href="{{ route('ticket.view', $ticket->id) }}" target="_blank"
                                        class="flex items-center gap-2 bg-blue-500 px-3 py-1 text-white rounded-lg hover:bg-blue-600 shadow transition"
                                        title="Ver eTicket">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8v4l3 3"/><path d="M4 4h16v16H4V4z" /></svg>
                                        eTICKET
                                    </a>
                                    {{-- BOTÓN REENVIAR EMAIL (Livewire) --}}
                                    <livewire:reenviar-ticket :ticket-id="$ticket->id" />
                                    {{-- BOTÓN WHATSAPP --}}
                                    <a href="{{ $whatsappUrl }}" target="_blank"
                                        class="flex items-center gap-2 bg-green-500 px-3 py-1 text-white rounded-lg hover:bg-green-600 shadow transition"
                                        title="Enviar por WhatsApp">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.119.553 4.098 1.604 5.857L0 24l6.309-1.589A11.956 11.956 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm.002 22.026c-1.945 0-3.84-.523-5.474-1.507l-.391-.233-3.747.944.999-3.647-.255-.397A9.959 9.959 0 0 1 2.01 12c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10zm5.045-7.566c-.084-.14-.308-.223-.644-.391-.336-.167-1.985-.977-2.292-1.09-.307-.112-.53-.168-.754.168-.224.336-.866 1.089-1.062 1.312-.195.224-.391.252-.727.084-.336-.167-1.419-.522-2.703-1.663-.999-.887-1.675-1.979-1.872-2.315-.196-.336-.021-.517.147-.684.151-.15.336-.392.504-.588.168-.196.223-.336.336-.56.112-.224.056-.42-.028-.588-.084-.167-.754-1.82-1.03-2.488-.271-.651-.547-.563-.753-.573-.195-.008-.419-.01-.642-.01s-.589.084-.898.42c-.308.336-1.175 1.147-1.175 2.801s1.203 3.265 1.372 3.488c.168.223 2.374 3.634 5.742 4.949.804.33 1.429.527 1.917.674.804.254 1.537.219 2.119.134.646-.093 1.985-.808 2.267-1.587.28-.78.28-1.449.197-1.588z"/></svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500">No tenés entradas aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Toast Notificación (mejorado) -->
    <div x-cloak x-data="{ show: false, title: '', message: '', type: '' }"
        x-on:toast.window="
            title   = $event.detail.title;
            message = $event.detail.message;
            type    = $event.detail.type;
            show    = true;
            setTimeout(() => show = false, 3000);
        "
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-6 right-6 z-[9999] min-w-[240px] px-6 py-4 rounded-lg shadow-lg text-white font-semibold"
        :class="{
            'bg-green-600': type === 'success',
            'bg-red-600': type === 'error',
            'bg-blue-600': type !== 'success' && type !== 'error'
        }"
        style="pointer-events: auto;">
        <div class="font-bold mb-1 text-lg" x-text="title"></div>
        <div x-text="message"></div>
    </div>
@endsection
