{{-- resources/views/mercadopago/status.blade.php --}}

@extends('layouts.app') {{-- O tu layout principal de Filament si lo prefieres para la UI --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Estado de Conexión con Mercado Pago</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif

                    @if(auth()->check() && auth()->user()->hasMercadoPagoAccount())
                        <p class="alert alert-success">¡Tu cuenta de Mercado Pago está conectada!</p>
                        <p>ID de usuario de MP: <strong>{{ auth()->user()->mp_user_id }}</strong></p>
                        <p>Última actualización de token: {{ auth()->user()->mp_expires_in ? auth()->user()->mp_expires_in->diffForHumans() : 'N/A' }}</p>

                        <form action="{{ route('mercadopago.unlink') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-warning">Desvincular Cuenta</button>
                        </form>
                    @else
                        <p class="alert alert-warning">Tu cuenta de Mercado Pago no está conectada.</p>
                        <a href="{{ route('mercadopago.connect') }}" class="btn btn-primary mt-3">Conectar con Mercado Pago</a>
                    @endif
                    <hr class="my-4">
                    {{-- Ajusta esta URL para que te lleve al dashboard de Filament o donde quieras que regrese el usuario --}}
                    <a href="{{ url('/admin') }}" class="btn btn-secondary">Volver al Panel de Administración</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
