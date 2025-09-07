{{-- resources/views/auth/check-email.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg text-center">
    <h1 class="text-2xl font-bold mb-4">¡Casi listo!</h1>
    <p class="mb-4">
        Acabamos de enviarte un enlace mágico a <strong>{{ $email }}</strong>.
    </p>
    <p class="mb-4">
        Por favor revisa tu bandeja de entrada y haz clic en el enlace para finalizar tu registro.
    </p>
    <a href="{{ url('/') }}" class="underline text-sm text-gray-600 hover:text-gray-900">
        Volver al inicio
    </a>
</div>
@endsection
