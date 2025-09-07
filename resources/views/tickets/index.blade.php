@extends('layouts.app')

@section('content')
    <h1>Entradas Vendidas</h1>

    <table>
        <thead>
            <tr>
                <th>ID Ticket</th>
                <th>Evento</th>
                <th>Entrada</th>
                <th>Nombre comprador</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Fecha de compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->id }}</td>
                    <td>{{ $ticket->entrada->evento->nombre ?? 'N/A' }}</td>
                    <td>{{ $ticket->entrada->nombre ?? 'N/A' }}</td>
                    <td>{{ $ticket->nombre }}</td>
                    <td>{{ $ticket->email }}</td>
                    <td>{{ $ticket->estado }}</td>
                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $tickets->links() }} {{-- Paginación --}}
@endsection
