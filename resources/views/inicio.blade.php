@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
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

        /* SWIPER: Mantener proporción real de la imagen */
        .heroSwiper {
            aspect-ratio: 3.5/1;
            /* Si tu imagen es 1400x400 */
            width: 100vw;
            max-width: 100vw;
            background: #181818;
            max-height: 420px;
        }

        .heroSwiper .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain !important;
            object-position: center;
            background: #181818;
            transition: object-fit 0.3s;
        }

        @media (max-width: 640px) {
            .heroSwiper {
                aspect-ratio: 2.5/1;
                max-height: 160px;
            }
        }

        /* --- Tarjeta de Evento Moderna --- */
        .evento-card {
            transition: box-shadow 0.3s, transform 0.2s;
        }

        .evento-card:hover {
            box-shadow: 0 10px 30px 0 rgba(75, 0, 130, .10), 0 2px 6px 0 rgba(0, 0, 0, .05);
            transform: translateY(-7px) scale(1.025);
            border-color: #8b5cf6;
        }

        .evento-card .evento-img {
            transition: transform 0.35s cubic-bezier(.34, 1.56, .64, 1);
        }

        .evento-card:hover .evento-img {
            transform: scale(1.05) rotate(-1deg);
        }
    </style>
@endpush

@section('slider')
    <section class="relative w-screen left-1/2 transform -translate-x-1/2 overflow-hidden mb-0 shadow-lg"
        style="max-width: 100vw;">
        <div class="swiper heroSwiper hide-scrollbar">
            <div class="swiper-wrapper">
                <!-- Slide 1 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ej1.png') }}" alt="Evento 1" />
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle"></a>
                </div>
                <!-- Slide 2 -->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/ej2.jpg') }}" alt="Evento 2" />
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle"></a>
                </div>
                <!-- Slide 3-->
                <div class="swiper-slide bg-black relative">
                    <img src="{{ asset('storage/eventos/x1.jpg') }}" alt="Evento 3" />
                    <a href="#" class="absolute inset-0 z-30" aria-label="Ver detalle"></a>
                </div>
            </div>
            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-button-next text-white"></div>
        </div>
    </section>
@endsection

@section('content')
    {{-- Título de separación --}}
    <section class="container mx-auto px-4 mt-8 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Próximos eventos</h2>
    </section>

    <!-- Grid de Eventos -->
    <section class="container mx-auto px-2 py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($eventos as $evento)
                <a href="{{ route('eventos.show', $evento) }}"
                    class="block bg-white rounded-[2rem] overflow-hidden shadow-md border border-gray-100 max-w-xs w-full mx-auto transition hover:shadow-xl hover:-translate-y-2">
                    <!-- Imagen cuadrada con bordes solo arriba -->
                    <div class="w-full aspect-square overflow-hidden rounded-t-[2rem]">
                        <img src="{{ asset('storage/' . $evento->imagen) }}" alt="{{ $evento->nombre }}"
                            class="w-full h-full object-cover object-center" />
                    </div>
                    <!-- Info principal -->
                    <div class="p-6 flex flex-col gap-2">
                        <!-- Lugar opcional -->
                        @if ($evento->ubicacion)
                            <div class="flex items-center gap-1 text-xs text-gray-500 uppercase font-bold mb-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 22s8-4.438 8-12a8 8 0 10-16 0c0 7.562 8 12 8 12z" />
                                </svg>
                                {{ $evento->ubicacion }}
                            </div>
                        @endif
                        <!-- Nombre del evento -->
                        <h3 class="font-normal text-2xl text-gray-900 mb-3 leading-tight break-words">
                            {{ $evento->nombre }}
                        </h3>
                        <!-- Footer con fecha y hora -->
                        <div class="flex gap-8 pt-2 border-t border-gray-200 mt-4">
                            <div class="flex flex-col items-center">
                                <span
                                    class="text-3xl font-black">{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d') }}</span>
                                <span class="uppercase text-sm text-gray-600 -mt-1">
                                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('M') }}
                                </span>
                                <span class="text-xs text-gray-400 font-bold -mt-1">
                                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('Y') }}
                                </span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-3xl font-black">
                                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('H') }}
                                    <span
                                        class="text-xl font-black">:{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('i') }}</span>
                                </span>
                                <span class="uppercase text-xs text-gray-600 -mt-1 font-bold">hrs</span>
                            </div>
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
