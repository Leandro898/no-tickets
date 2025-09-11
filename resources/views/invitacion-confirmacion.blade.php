@extends('layouts.app')

@section('content')
<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg mx-auto text-center">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">¡Registro Exitoso!</h1>
    <p class="text-gray-700 mb-6">Tu invitación ha sido registrada correctamente.</p>
    <p class="text-gray-700">Puedes cerrar esta ventana.</p>
    {{-- Aquí podrías mostrar el ID de la invitación si lo pasas desde la ruta --}}
    @if (isset($invitacion_id))
    <p class="text-gray-500 text-sm mt-4">ID de Invitación: {{ $invitacion_id }}</p>
    @endif
</div>
@endsection