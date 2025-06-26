@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[75vh]">
        <div class="max-w-xl w-full mx-auto bg-white p-10 rounded-xl shadow text-center">
            <h1 class="text-4xl font-bold text-purple-700 mb-4">Bienvenido a Innova Ticket</h1>
            <p class="text-gray-700 text-lg mb-6">
                Tu plataforma para vender entradas fácil y rápido.
            </p>
            <a href="{{ route('eventos.index') }}"
                class="inline-block bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-6 rounded transition">
                Ver eventos
            </a>
        </div>
    </div>
@endsection
