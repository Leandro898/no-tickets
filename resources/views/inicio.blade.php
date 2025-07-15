{{-- resources/views/inicio.blade.php --}}
@extends('layouts.app')

@push('styles')
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
@endpush

@section('slider')
    <section
        class="relative w-screen left-1/2 transform -translate-x-1/2 overflow-hidden mb-0 shadow-lg"
        style="max-width: 100vw;"
    >
        <div class="swiper heroSwiper hide-scrollbar">
            <div class="swiper-wrapper">
                @foreach($eventos->take(3) as $slide)
                    <div class="swiper-slide bg-black relative">
                        <img
                            src="{{ asset('storage/'.$slide->imagen) }}"
                            alt="{{ $slide->nombre }}"
                            class="w-full h-full object-contain object-center"
                        />
                        <a
                            href="{{ route('eventos.show', $slide) }}"
                            class="absolute inset-0 z-30"
                            aria-label="Ver detalle"
                        ></a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-button-next text-white"></div>
        </div>
    </section>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-10">
        {{-- Título de sección --}}
        <section class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Próximos eventos</h2>
        </section>

        {{-- Grid de tarjetas --}}
        <section>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($eventos as $evento)
                    <a
                        href="{{ route('eventos.show', $evento) }}"
                        class="evento-card bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <div class="evento-img w-full aspect-square overflow-hidden">
                            <img
                                src="{{ asset('storage/'.$evento->imagen) }}"
                                alt="{{ $evento->nombre }}"
                                class="w-full h-full object-cover object-center"
                            />
                        </div>
                        <div class="p-4 flex flex-col h-full">
                            <h3 class="font-semibold text-lg text-gray-900 mb-2">
                                {{ $evento->nombre }}
                            </h3>
                            <div class="mt-auto flex justify-between items-center pt-2 border-t border-gray-200">
                                <span class="text-gray-700 font-medium">
                                    {{ \Carbon\Carbon::parse($evento->fecha_inicio)
                                         ->locale('es')
                                         ->translatedFormat('d M Y') }}
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
