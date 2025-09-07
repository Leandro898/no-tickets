@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 shadow rounded text-center">
        <h1 class="text-2xl font-bold text-green-600 mb-4">✅ Mercado Pago vinculado con éxito</h1>

        <p class="text-gray-700 mb-2">¡Tu cuenta fue conectada correctamente!</p>

        <div class="mt-4 text-sm text-left">
            <p><strong>ID de Usuario MP:</strong> {{ $user->mp_user_id }}</p>
            <p><strong>Expira:</strong> {{ $user->mp_expires_in?->format('Y-m-d H:i') }}</p>
        </div>

        <a href="{{ route('dashboard') }}" class="mt-6 inline-block text-blue-600 hover:underline">Volver al panel</a>
    </div>
@endsection

