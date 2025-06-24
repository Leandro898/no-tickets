<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-purple-700">Mis Entradas</h2>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto">
        <p class="text-gray-700 mb-6">
            Tus entradas han sido enviadas por correo electrónico al confirmar el pago. También podés verlas y descargarlas desde aquí.
        </p>

        <div class="overflow-x-auto bg-white shadow border border-gray-200 rounded-lg">
            <table class="w-full divide-y divide-gray-200 text-sm text-center">
                <thead class="bg-gray-100 font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Afiche</th>
                        <th class="px-4 py-3">Evento</th>
                        <th class="px-4 py-3">Tipo</th>
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
                    @endphp
                    <tr>
                        <td class="px-4 py-3 align-middle">
                            <img src="{{ asset('storage/' . ($evento->afiche ?? 'placeholder.jpg')) }}"
                                alt="Afiche" class="w-16 h-16 object-cover rounded mx-auto">
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div class="font-semibold text-gray-800">{{ $evento->titulo ?? 'Evento no disponible' }}</div>
                            <div class="text-gray-500 text-xs">Vigente</div>
                        </td>
                        <td class="px-4 py-3 align-middle">{{ $ticket->ticket_type }}</td>
                        <td class="px-4 py-3 align-middle">
                            {{ $evento->lugar ?? '-' }}<br>
                            {{ $evento?->fecha ? \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3 align-middle">${{ number_format($ticket->order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 align-middle">{{ $ticket->ticket_code }}</td>
                        <td class="px-4 py-3 align-middle">
                            <div class="flex flex-col gap-1 items-center">
                                <a href="#" class="bg-yellow-500 hover:bg-yellow-600 text-gray px-3 py-1 rounded text-xs">Detalle</a>
                                <a href="{{ route('qr.descargar', ['filename' => basename($ticket->qr_path)]) }}"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-gray px-3 py-1 rounded text-xs">eTICKET</a>
                                <a href="#" class="bg-yellow-500 hover:bg-yellow-600 text-gray px-3 py-1 rounded text-xs">REENVIAR</a>
                                <a href="#" class="bg-yellow-500 hover:bg-yellow-600 text-gray px-3 py-1 rounded text-xs">WHATSAPP</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No tenés entradas aún.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>