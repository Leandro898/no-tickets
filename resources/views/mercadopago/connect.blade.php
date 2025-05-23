{{-- Asume que tienes un layout principal, como 'layouts.app' --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Conectar Cuenta de Mercado Pago</div>
                <div class="card-body">
                    @if(auth()->check() && auth()->user()->hasMercadoPagoAccount())
                        <p>Tu cuenta de Mercado Pago ya está conectada.</p>
                        <form action="{{ route('mercadopago.unlink') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">Desvincular Cuenta</button>
                        </form>
                    @else
                        <p>Conecta tu cuenta de Mercado Pago para recibir pagos por tus entradas.</p>
                        <a href="{{ route('mercadopago.connect') }}" class="btn btn-primary">Conectar con Mercado Pago</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection