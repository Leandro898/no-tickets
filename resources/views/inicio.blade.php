@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        html, body { overflow-x: hidden !important; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
    <!-- Hero Slider Full Width con Swiper (RESPONSIVE) -->
    <section class="relative w-full max-w-5xl mx-auto overflow-hidden mb-6 mt-2 rounded-2xl">
        <div class="swiper heroSwiper hide-scrollbar h-48 sm:h-72 md:h-96 lg:h-[500px]">
            <div class="swiper-wrapper">
                <!-- Slide 1 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ej1.png') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-cover object-center" />
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle Concierto Chewelche"></a>
                    <div class="absolute inset-0 z-20 bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none"></div>
                </div>
                <!-- Slide 2 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ej2.jpg') }}" alt=""
                        class="w-full h-full object-cover object-center" />
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle"></a>
                    <div class="absolute inset-0 z-20 bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none"></div>
                </div>
                <!-- Slide 3-->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/x1.jpg') }}" alt="Concierto Chewelche"
                        class="w-full h-full object-cover object-center" />
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle"></a>
                    <div class="absolute inset-0 z-20 bg-opacity-20 flex flex-col justify-center p-6 pointer-events-none"></div>
                </div>
            </div>
            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-button-next text-white"></div>
        </div>
    </section>

    {{-- Título de separación --}}
    <section class="container mx-auto px-4 mt-8 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Próximos eventos</h2>
    </section>

    <!-- Grid de Eventos -->
    <section class="container mx-auto px-2 py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($eventos as $evento)
                <a 
                    href="{{ route('eventos.show', $evento) }}"
                    class="block rounded-2xl overflow-hidden bg-white
                           shadow-md transition-transform duration-300 ease-in-out
                           hover:-translate-y-2 hover:shadow-xl"
                >
                    <div class="w-full h-40 sm:h-56 md:h-64 overflow-hidden rounded-2xl">
                        <img
                            src="{{ asset('storage/'.$evento->imagen) }}"
                            alt="{{ $evento->nombre }}"
                            class="w-full h-full object-cover object-center"
                        />
                    </div>
                    <div class="p-3">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 leading-snug mb-1">
                            {{ $evento->nombre }}
                        </h3>
                    </div>
                    <div class="flex items-center justify-between px-3 pb-3 border-t border-gray-100">
                        <div class="flex items-baseline space-x-1">
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d') }}
                            </span>
                            <span class="text-xs text-gray-600 uppercase">
                                {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('M') }}
                            </span>
                        </div>
                        <div class="flex items-baseline space-x-1">
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">
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
        });
    </script>
@endpush
