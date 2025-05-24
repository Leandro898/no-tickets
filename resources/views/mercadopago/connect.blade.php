{{-- resources/views/mercadopago/connect.blade.php --}}

@extends('layouts.app') {{-- O tu layout principal de Filament si lo prefieres para la UI --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Conectar Cuenta de Mercado Pago</div>
                <div class="card-body">
                    @if(auth()->check() && auth()->user()->hasMercadoPagoAccount())
                        <p class="alert alert-success">¡Tu cuenta de Mercado Pago ya está conectada!</p>
                        <p>ID de usuario de MP: <strong>{{ auth()->user()->mp_user_id }}</strong></p>
                        <p>Última actualización de token: {{ auth()->user()->mp_expires_in ? auth()->user()->mp_expires_in->diffForHumans() : 'N/A' }}</p>

                        <form action="{{ route('mercadopago.unlink') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">Desvincular Cuenta</button>
                        </form>
                    @else
                        <p>Conecta tu cuenta de Mercado Pago para recibir pagos por tus servicios/productos.</p>
                        <a href="{{ route('mercadopago.connect') }}" class="btn btn-primary">Conectar con Mercado Pago</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
