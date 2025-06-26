@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-purple-700 text-center mb-8">Eventos Disponibles</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 w-full min-h-[500px]">
        @forelse ($eventos as $evento)
            <a href="{{ route('eventos.show', $evento) }}"
               class="bg-white rounded-2xl shadow-md flex flex-col overflow-hidden w-full hover:shadow-lg transition group">
                <img src="{{ asset('storage/' . ($evento->imagen ?? 'placeholder.jpg')) }}"
                     alt="Banner del evento"
                     class="w-full h-44 object-cover group-hover:scale-105 transition">
                <div class="flex-1 flex flex-col justify-between p-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-2 truncate group-hover:text-purple-700 transition">
                        {{ $evento->nombre }}
                    </h2>
                    <div class="text-sm text-gray-500 mb-2 truncate">
                        {{ $evento->ubicacion }}
                    </div>
                    <div class="mt-auto flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d M Y') }}
                        </span>
                        <span class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('H:i') }} hs
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center text-gray-400 text-lg py-12">
                No hay eventos disponibles.
            </div>
        @endforelse
    </div>
@endsection
