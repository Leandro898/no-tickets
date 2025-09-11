@extends('layouts.app')

@section('content')
<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg mx-auto text-center">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">¡Registro Exitoso! 🎉</h1>
    <p class="text-gray-700 mb-6">Tu invitación ha sido registrada correctamente y se ha enviado un correo con tu código QR.</p>

    {{-- Aquí se muestra el QR --}}
    <div class="my-6">
        <img src="{{ asset('storage/' . $invitacion->qr_path) }}" alt="Código QR de la invitación" style="display:block; margin: 0 auto; max-width: 250px;">
    </div>

    <div class="mt-8 mb-6 border-t border-gray-200 pt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Detalles de tu invitación</h2>
        <p class="text-gray-700"><strong>Nombre:</strong> {{ $invitacion->buyer_name }}</p>
        <p class="text-gray-700"><strong>Email:</strong> {{ $invitacion->email }}</p>
        <p class="text-gray-700"><strong>Evento:</strong> {{ $invitacion->evento->nombre }}</p>
    </div>

    <p class="text-gray-700">
        <strong class="font-semibold text-gray-900">¡Te esperamos!</strong>
    </p>

    <div class="mt-6">
        <a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Volver al inicio</a>
    </div>
</div>
@endsection