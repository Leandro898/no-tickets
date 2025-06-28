@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Slider principal -->
    <div class="mb-12">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @foreach($featuredEvents as $event)
                    <div class="swiper-slide relative rounded-lg overflow-hidden">
                        <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="w-full h-64 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center items-start p-6">
                            <h2 class="text-white text-2xl font-bold mb-2">{{ $event->title }}</h2>
                            <p class="text-gray-200">{{ $event->date->format('d M, Y') }}</p>
                            <a href="{{ route('events.show', $event) }}" class="mt-4 bg-violet-600 hover:bg-violet-700 text-white font-semibold py-2 px-4 rounded">
                                Comprar
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Navegación -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <!-- Grid de eventos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach($events as $event)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-4">
                <img src="{{ $event->thumbnail_url }}" alt="{{ $event->title }}" class="w-full h-40 object-cover rounded">
                <h3 class="mt-4 text-xl font-semibold text-gray-800">{{ $event->title }}</h3>
                <p class="text-gray-600">{{ $event->date->format('d M, Y') }}</p>
                <a href="{{ route('events.show', $event) }}" class="mt-3 inline-block bg-violet-600 hover:bg-violet-700 text-white font-semibold py-2 px-4 rounded">
                    Ver más
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.swiper-container', {
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 5000,
            },
        });
    });
</script>
@endpush
