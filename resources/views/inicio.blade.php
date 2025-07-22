@extends('layouts.app')

@push('styles')
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        /* Forzar que el slider no tenga scroll horizontal nunca */
        .heroSwiper,
        .heroSwiper .swiper-slide {
            min-width: 0 !important;
        }
    </style>
@endpush

@section('slider')
    <section class="w-full overflow-hidden bg-black m-0 p-0" style="margin:0;padding:0;">
        <div class="swiper heroSwiper bg-black h-[250px] lg:h-[250px]">
            <div class="swiper-wrapper" style="margin:0;padding:0;">
                <div class="swiper-slide" style="margin:0;padding:0;">
                    <img src="{{ asset('storage/eventos/slider/ej1.png') }}" alt="Ejemplo 1"
                        class="w-full h-full object-contain object-center m-0 p-0" />
                </div>
                <div class="swiper-slide" style="margin:0;padding:0;">
                    <img src="{{ asset('storage/eventos/slider/ej2.jpg') }}" alt="Ejemplo 2"
                        class="w-full h-full object-contain object-center m-0 p-0" />
                </div>
                <div class="swiper-slide" style="margin:0;padding:0;">
                    <img src="{{ asset('storage/eventos/slider/ej3.png') }}" alt="Ejemplo 3"
                        class="w-full h-full object-contain object-center m-0 p-0" />
                </div>
            </div>
            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-button-next text-white"></div>
        </div>

    </section>
@endsection

@push('styles')
    <style>
        .heroSwiper,
        .heroSwiper .swiper-wrapper,
        .heroSwiper .swiper-slide {
            height: 200px !important;
            min-height: 0 !important;
            max-height: 200px !important;
        }

        @media (min-width: 1024px) {

            /* lg */
            .heroSwiper,
            .heroSwiper .swiper-wrapper,
            .heroSwiper .swiper-slide {
                height: 300px !important;
                max-height: 300px !important;
            }
        }

        @media (min-width: 1280px) {

            /* xl */
            .heroSwiper,
            .heroSwiper .swiper-wrapper,
            .heroSwiper .swiper-slide {
                height: 360px !important;
                max-height: 360px !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-10">
        {{-- Título de sección --}}
        <section class="mb-12">
            <h2 class="text-4xl text-center font-bold text-gray-800 pb-4">Próximos eventos</h2>
        </section>


        {{-- Grid de tarjetas --}}
        <section>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($eventos as $evento)
                    <a href="{{ route('eventos.show', $evento) }}"
                        class="evento-card bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 
                        ver:-translate-y-2">
                        <div
                            class="evento-img w-full h-45 sm:h-52 md:aspect-video overflow-hidden bg-black flex items-center justify-center">
                            <img src="{{ asset('storage/' . $evento->imagen) }}" alt="{{ $evento->nombre }}"
                                class="w-full h-full object-contain object-center" />
                        </div>

                        <div class="p-4 flex flex-col h-full">
                            <h3 class="font-semibold text-lg text-gray-900 mb-2">
                                {{ $evento->nombre }}
                            </h3>
                            <div class="mt-auto flex justify-between items-center pt-2 border-t border-gray-200">
                                <span class="text-gray-700 font-medium">
                                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)->locale('es')->translatedFormat('d M Y') }}
                                </span>
                                <span class="text-purple-600 font-bold">
                                    ${{ number_format($evento->entradas->min('precio'), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-center text-gray-500">
                        No hay próximos eventos.
                    </p>
                @endforelse
            </div>
        </section>

                {{-- CTA Publicar Evento --}}
        <section class="mt-16 mb-20 text-center">
            <div class="bg-purple-100 text-purple-800 px-6 py-10 rounded-2xl max-w-3xl mx-auto">
                <h3 class="text-2xl font-bold mb-4">¿Querés publicar tu próximo evento?</h3>
                <p class="mb-6 text-gray-700">Contactanos por WhatsApp y te ayudamos a promocionarlo en nuestra plataforma.</p>
                <a href="https://wa.me/549XXXXXXXXXX" target="_blank"
                    class="inline-block bg-purple-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                    Escribinos por WhatsApp
                </a>
            </div>
        </section>

    </div>
@endsection

@push('scripts')
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('.heroSwiper', {
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    </script>
@endpush
