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
                    <img src="{{ asset('storage/eventos/ejemplo-4.png') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-contain" />

                    <!-- Enlace que cubre TODO el slide -->
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>

                    <!-- Capa de texto superpuesta (ahora sin bloquear clics) -->
                    <div
                        class="absolute inset-0 z-20 bg-black bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none">
                        {{-- aquí podrás añadir <h2>, <p>, etc. sin que bloqueen el enlace --}}
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ejemplo-3.png') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-contain" />

                    <!-- Enlace que cubre TODO el slide -->
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>

                    <!-- Capa de texto superpuesta (ahora sin bloquear clics) -->
                    <div
                        class="absolute inset-0 z-20 bg-black bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none">
                        {{-- aquí podrás añadir <h2>, <p>, etc. sin que bloqueen el enlace --}}
                    </div>
                </div>

                <!-- Slide 3-->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ejemplo-2.jpg') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-contain" />

                    <!-- Enlace que cubre TODO el slide -->
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>

                    <!-- Capa de texto superpuesta (ahora sin bloquear clics) -->
                    <div
                        class="absolute inset-0 z-20 bg-black bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none">
                        {{-- aquí podrás añadir <h2>, <p>, etc. sin que bloqueen el enlace --}}
                    </div>
                </div>
            </div>

            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-button-next text-white"></div>
        </div>
    </section>

    {{-- Título de separación --}}
    <section class="container mx-auto px-4 mt-12 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Próximos eventos</h2>
    </section>
    <!-- Próximos Eventos Carousel Full Width -->
    {{-- <section class="relative w-screen left-1/2 transform -translate-x-1/2 overflow-hidden mb-6">
        <div class="swiper cardsSwiper hide-scrollbar">
            <div class="swiper-wrapper">

                <!-- Empieza el bucle de las Tarjetas de eventos -->

                @foreach (range(1, 12) as $i)
                    <div class="swiper-slide flex-shrink-0 w-40 bg-white rounded-lg shadow overflow-hidden">
                        <img src="https://source.unsplash.com/240x160/?event,concert,band,{{ $i }}"
                            alt="Evento {{ $i }}" class="w-full h-32 object-cover">
                        <div class="p-2">
                            <h4 class="font-semibold text-gray-800 text-sm">Evento {{ $i }}</h4>
                            <p class="text-gray-600 text-xs">{{ now()->addDays($i * 5)->format('d M, Y') }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
            <!-- Botones de navegación -->
            <div class="swiper-button-prev text-gray-600"></div>
            <div class="swiper-button-next text-gray-600"></div>
        </div>
    </section> --}}

    <!-- Search Section -->
    {{-- <section class="bg-gray-900 text-white py-6 mt-6">
        <div class="container mx-auto px-4">
            <form class="flex flex-wrap items-center gap-4">
                <input type="text" placeholder="Buscar en Innova Ticket"
                    class="flex-grow min-w-[200px] bg-gray-800 placeholder-gray-400 text-white rounded px-4 py-2 focus:outline-none" />
                <select class="bg-gray-800 text-white rounded px-4 py-2 focus:outline-none">
                    <option>Provincia</option>
                    <option>Buenos Aires</option>
                    <option>Córdoba</option>
                    <option>Santa Fe</option>
                </select>
                <select class="bg-gray-800 text-white rounded px-4 py-2 focus:outline-none">
                    <option>Localidad</option>
                    <option>Ciudad</option>
                    <option>Villa</option>
                </select>
                <input type="date" class="bg-gray-800 text-white rounded px-4 py-2 focus:outline-none" />
                <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold px-6 py-2 rounded">Buscar</button>
            </form>
        </div>
    </section> --}}

    <!-- Grid de Eventos -->
    <section class="container mx-auto px-4 py-8">
      
        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
          @forelse($eventos as $evento)
            <a 
              href="{{ route('eventos.show', $evento) }}"
              class="block bg-white rounded-2xl overflow-hidden
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
      </section>
      
@endsection

@push('scripts')
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hero Slider
            new Swiper('.heroSwiper', {
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
            });
            // Cards Carousel - movimiento constante
            const cardsSwiper = new Swiper('.cardsSwiper', {
                loop: true,
                slidesPerView: 'auto',
                spaceBetween: 12,
                freeMode: true,
                freeModeMomentum: false,
                speed: 3000,
                autoplay: {
                    delay: 0,
                    disableOnInteraction: false
                },
                navigation: {
                    nextEl: '.cardsSwiper .swiper-button-next',
                    prevEl: '.cardsSwiper .swiper-button-prev'
                }
            });
            // Pausar/retomar autoplay al pasar hover
            cardsSwiper.el.addEventListener('mouseenter', () => cardsSwiper.autoplay.stop());
            cardsSwiper.el.addEventListener('mouseleave', () => cardsSwiper.autoplay.start());
        });
    </script>
@endpush
