{{-- resources/views/eventos/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Eventos Disponibles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($eventos as $evento)
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $evento->nombre }}</h3>
                        <p class="text-gray-700 mb-4">
                            {{ \Illuminate\Support\Str::limit($evento->descripcion, 100) }}
                        </p>
                        <p class="text-sm text-gray-600 mb-1">
                            <strong>Ubicación:</strong> {{ $evento->ubicacion }}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') }}
                        </p>
                        <a href="{{ route('eventos.show', $evento) }}"
                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Ver Evento
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
