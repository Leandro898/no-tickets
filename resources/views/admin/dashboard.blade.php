@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">👨‍💼 Panel de Administración</h1>
    <p>Bienvenido, {{ auth()->user()->name }}</p>
</div>
@endsection