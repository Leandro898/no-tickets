@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        /* Ocultar scrollbars en los sliders y evitar scroll horizontal global */
        html,
        body {
            overflow-x: hidden !important;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('content')
    <!-- Hero Slider Full Width con Swiper -->
    {{-- Hero Slider Full Width con Swiper (hardcodeado) --}}
    <section class="relative w-screen left-1/2 transform -translate-x-1/2 overflow-hidden -mt-16 mb-4">
        <div class="swiper heroSwiper hide-scrollbar h-[500px]">
            <div class="swiper-wrapper">

                <!-- Slide 1 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ej1.png') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-contain" />

                    <!-- Enlace que cubre TODO el slide -->
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>

                    <!-- Capa de texto superpuesta (ahora sin bloquear clics) -->
                    <div
                        class="absolute inset-0 z-20 bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none">
                        {{-- aquí podrás añadir <h2>, <p>, etc. sin que bloqueen el enlace --}}
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ej2.jpg') }}" alt=""
                        class="w-full h-full object-contain" />

                    <!-- Enlace que cubre TODO el slide -->
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>

                    <!-- Capa de texto superpuesta (ahora sin bloquear clics) -->
                    <div
                        class="absolute inset-0 z-20 bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none">
                        {{-- aquí podrás añadir <h2>, <p>, etc. sin que bloqueen el enlace --}}
                    </div>
                </div>

                <!-- Slide 3-->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/x1.jpg') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-contain" />

                    <!-- Enlace que cubre TODO el slide -->
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>

                    <!-- Capa de texto superpuesta (ahora sin bloquear clics) -->
                    <div
                        class="absolute inset-0 z-20 bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none">
                        {{-- aquí podrás añadir <h2>, <p>, etc. sin que bloqueen el enlace --}}
                    </div>
                </div>
            </div>

            {{-- BOTONES DE ACCIÓN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-filament::button
                    tag="a"
                    :href="route('filament.admin.resources.eventos.edit',['record'=>$record->id])"
                    icon="heroicon-o-pencil-square"
                    class="btn-detalles"
                >Editar evento</x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="route('filament.admin.resources.eventos.gestionar-entradas',['record'=>$record->id])"
                    icon="heroicon-o-pencil"
                    class="btn-detalles"
                >Editar Entradas</x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="\App\Filament\Resources\EventoResource\Pages\ReportesEvento::getUrl(['record'=>$record->id])"
                    icon="heroicon-o-chart-bar"
                    class="btn-detalles"
                >Reportes</x-filament::button>

                <x-filament::button
                    type="button"
                    icon="heroicon-o-link"
                    class="btn-detalles"
                    x-on:click="mostrarModal = true"
                >Copiar link</x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="\App\Filament\Resources\EventoResource\Pages\ListaDigital::getUrl(['record'=>$record->id])"
                    icon="heroicon-o-list-bullet"
                    class="btn-detalles"
                >Lista digital</x-filament::button>

                <x-filament::button
                    type="button"
                    icon="heroicon-o-x-circle"
                    class="btn-detalles danger"
                    wire:click="abrirPrimerModal"
                >Suspender evento</x-filament::button>
            </div>
        </div>
    </section> --}}

    <!-- Grid de Eventos -->
    <section class="container mx-auto px-4 py-8">
      
        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
          @forelse($eventos as $evento)
            <a 
              href="{{ route('eventos.show', $evento) }}"
              class="block rounded-2xl overflow-hidden
                     shadow-md transition-transform duration-300 ease-in-out
                     hover:-translate-y-2 hover:shadow-xl"
            >
              {{-- Imagen con ratio fijo --}}
              <div class="w-full h-64 overflow-hidden rounded-t-2xl">
                <img
                  src="{{ asset('storage/'.$evento->imagen) }}"
                  alt="{{ $evento->nombre }}"
                  class="w-full h-full object-cover object-center"
                />
              </div>              
      
              {{-- Nombre --}}
              <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 leading-snug">
                  {{ $evento->nombre }}
                </h3>
              </div>
      
              {{-- Footer: día/mes y hora --}}
              <div class="flex items-center justify-between px-4 pb-4 border-t border-gray-100">
                <div class="flex items-baseline space-x-1">
                  <span class="text-2xl font-bold text-gray-900">
                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d') }}
                  </span>
                  <span class="text-xs text-gray-600 uppercase">
                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('M') }}
                  </span>
                </div>
                <div class="flex items-baseline space-x-1">
                  <span class="text-2xl font-bold text-gray-900">
                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('H') }}
                  </span>
                  <span class="text-xs text-gray-600">
                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('i') }} hrs
                  </span>
                </div>
              </div>
            </a>
          @empty
            <p class="col-span-full text-center text-gray-500">No hay próximos eventos.</p>
          @endforelse
        </div>

    </div>
</x-filament::page>
