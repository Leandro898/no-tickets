@extends('layouts.app')

@section('content')
    <p class="text-gray-700 mb-6">
        Tus entradas han sido enviadas por correo electrónico al confirmar el pago. También podés verlas y descargarlas desde aquí.
    </p>
    <div class="overflow-x-auto bg-white shadow border border-gray-200 rounded-lg">
        <table class="w-full divide-y divide-gray-200 text-sm text-center">
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
                        "¡Hola! Gracias por tu compra en *Innova Ticket*.

                    *Evento:* {$evento->nombre}
                    *Lugar:* {$evento->ubicacion}
                    *Fecha:* " . \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') . "

                    *Podés descargar tu entrada en PDF desde este enlace:*"
                    );
                    $link = route('ticket.descargar', ['ticket' => $ticket->id]);
                    $whatsappUrl = "https://wa.me/{$ticket->order->whatsapp_number}?text={$mensaje}%0A{$link}";
                @endphp
                <tr>
                    <td class="px-4 py-3 align-middle">
                        <img src="{{ asset('storage/' . ($evento->imagen ?? 'placeholder.jpg')) }}" alt="Afiche" class="w-16 h-16 object-cover rounded mx-auto">
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-semibold text-gray-800">{{ $evento->nombre ?? 'Evento no disponible' }}</div>
                        <div class="text-gray-500 text-xs">Vigente</div>
                    </td>
                    <td class="px-4 py-3 align-middle">{{ $ticket->ticket_type }}</td>
                    <td class="px-4 py-3 align-middle">{{ $cantidad }}</td>
                    <td class="px-4 py-3 align-middle">
                        {{ $evento->ubicacion ?? '-' }}<br>
                        {{ $evento?->fecha_inicio ? \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3 align-middle">${{ number_format($ticket->order->total_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 align-middle">{{ $ticket->ticket_code }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex flex-col gap-1 items-center">

                            {{-- BOTÓN eTICKET --}}
                            <a href="{{ route('ticket.mostrar', $ticket->id) }}" target="_blank"
                                class="bg-blue-500 w-40 py-1 text-white rounded hover:bg-blue-600 transition">
                                eTICKET
                            </a>

                            {{-- BOTÓN REENVIAR EMAIL (Livewire) --}}
                            <livewire:reenviar-ticket :ticket-id="$ticket->id" />

                            {{-- BOTÓN WHATSAPP --}}
                            <a href="{{ $whatsappUrl }}" target="_blank"
                                class="w-40 py-1 text-white block bg-green-500 hover:bg-green-600 rounded transition">
                                WHATSAPP
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

    <!-- Toast Notificación (solo en esta vista) -->
    <div
        x-data="{ show: false, title: '', message: '', type: '' }"
        x-on:toast.window="
            title = $event.detail.title;
            message = $event.detail.message;
            type = $event.detail.type;
            show = true;
            setTimeout(() => show = false, 3000);
        "
        x-show="show"
        class="fixed top-6 right-6 z-50 min-w-[220px] px-6 py-4 rounded shadow-lg text-white"
        :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
        x-transition
        style="display: none;"
    >
        <div class="font-bold mb-1" x-text="title"></div>
        <div x-text="message"></div>
    </div>


@endsection
