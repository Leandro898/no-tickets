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
        <section class="mt-16 mb-20 text-center px-4">
            <div class="bg-purple-100 text-purple-800 px-6 py-10 rounded-2xl max-w-3xl mx-auto shadow-lg">
                <h3 class="text-3xl font-extrabold mb-4">¿Querés publicar tu próximo evento?</h3>
                <p class="mb-6 text-gray-700 text-lg">Escribinos por WhatsApp y te ayudamos a difundirlo en TicketsPro.</p>
                <a href="https://wa.me/5492944900107" target="_blank"
                    class="inline-flex items-center gap-2 bg-green-500 text-white font-semibold px-6 py-3 rounded-lg hover:bg-green-600 transition shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 0c-6.63 0-12 5.37-12 12 0 2.11.55 4.11 1.61 5.9l-1.66 6.07 6.23-1.63c1.73.94 3.7 1.43 5.82 1.43 6.63 0 12-5.37 12-12s-5.37-12-12-12zm0 22.09c-1.78 0-3.52-.48-5.04-1.39l-.36-.21-3.69.97.98-3.58-.23-.37c-1-1.56-1.52-3.36-1.52-5.21 0-5.51 4.49-10 10-10s10 4.49 10 10-4.49 10-10 10zm5.41-7.55c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.66.15s-.76.97-.93 1.17c-.17.2-.34.22-.64.07-.3-.15-1.27-.47-2.42-1.51-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.34.45-.51.15-.17.2-.29.3-.49.1-.2.05-.37-.03-.52-.07-.15-.66-1.6-.91-2.19-.24-.57-.49-.5-.66-.5h-.56c-.2 0-.52.07-.79.37s-1.04 1.01-1.04 2.46 1.07 2.85 1.22 3.05c.15.2 2.11 3.22 5.11 4.51.71.31 1.27.5 1.7.64.71.22 1.35.19 1.86.12.57-.08 1.77-.72 2.02-1.42.25-.69.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>
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
