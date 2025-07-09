@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">🎟️ Mis Entradas</h2>

    @forelse($tickets as $ticket)
    <div class="border rounded p-4 mb-4 shadow-sm bg-white">
        <p><strong>🎫 Entrada #{{ $ticket->id }}</strong></p>

        {{-- Evento asociado --}}
        <p>📍 Evento:
            <strong>{{ $ticket->entrada->evento->titulo ?? 'Evento no disponible' }}</strong>
        </p>

        {{-- Tipo de entrada --}}
        <p>🎟️ Tipo: {{ $ticket->ticket_type }}</p>

        {{-- Código QR --}}
        <p>🧾 Código: {{ $ticket->short_code }}</p>

        {{-- Estado --}}
        <p>
            🟢 Estado:
            <span class="{{ $ticket->status === 'used' ? 'text-red-600' : 'text-green-600' }}">
                {{ $ticket->status === 'used' ? 'Usada' : 'Válida' }}
            </span>
        </p>

        {{-- Fecha de compra --}}
        <p>📅 Comprada el: {{ $ticket->created_at->format('d/m/Y H:i') }}</p>

        {{-- Link de descarga --}}
        <div class="mt-3">
            <a href="{{ route('tickets.download', $ticket->id) }}"
                class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                Descargar Entrada
            </a>
        </div>

        {{-- Imagen QR (opcional) --}}
        @if($ticket->qr_path)
        <div class="mt-3">
            <img src="{{ asset('storage/' . $ticket->qr_path) }}"
                alt="Código QR" class="w-40 h-auto border rounded" />
        </div>
        @endif
    </div>
    @empty
    <p class="text-gray-600">Todavía no compraste ninguna entrada.</p>
    @endforelse
</div>
@endsection