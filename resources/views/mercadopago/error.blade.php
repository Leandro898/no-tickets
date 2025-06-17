@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto bg-white p-6 mt-10 shadow text-center text-red-600 font-semibold">
        ❌ Ocurrió un error al intentar vincular tu cuenta de Mercado Pago.
        @if(session('error'))
            <p class="text-sm text-gray-600 mt-2">{{ session('error') }}</p>
        @endif
        <a href="{{ route('dashboard') }}" class="block mt-6 text-blue-500 hover:underline">Volver al panel</a>
    </div>
@endsection

