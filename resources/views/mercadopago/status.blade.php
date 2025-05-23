{{-- Asume que tienes un layout principal, como 'layouts.app' --}}
@extends('layouts.app')

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
                        <p>Tu cuenta de Mercado Pago está conectada.</p>
                        <p>ID de usuario de MP: <strong>{{ auth()->user()->mp_user_id }}</strong></p>
                        <p>Última actualización de token: {{ auth()->user()->mp_expires_in ? auth()->user()->mp_expires_in->diffForHumans() : 'N/A' }}</p>
                    @else
                        <p>Tu cuenta de Mercado Pago no está conectada.</p>
                        <a href="{{ route('mercadopago.connect') }}" class="btn btn-primary">Conectar con Mercado Pago</a>
                    @endif
                    <hr>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Volver al Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection