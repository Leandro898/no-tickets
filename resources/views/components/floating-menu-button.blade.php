<div x-data="{ open: false }" class="z-[9999]">
    {{-- Botón flotante --}}
    <button @click="open = !open"
        class="fixed bottom-6 right-6 w-16 h-16 bg-primary-600 text-white rounded-full shadow-2xl flex items-center justify-center border-4 border-white"
        aria-label="Abrir menú">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Menú flotante visible solo cuando open == true --}}
    <div
        x-show="open"
        x-transition
        @click.away="open = false"
        class="fixed bottom-24 right-6 w-60 bg-white rounded-xl shadow-2xl flex flex-col divide-y divide-gray-200 z-[9998]"
    >
        <button class="text-left px-4 py-3 hover:bg-gray-100 w-full">Tienda</button>
        <button class="text-left px-4 py-3 hover:bg-gray-100 w-full">Por Tipo</button>
        <button class="text-left px-4 py-3 hover:bg-gray-100 w-full">Por Lugar</button>
        <button class="text-left px-4 py-3 hover:bg-gray-100 w-full">Por Color</button>
        <button class="text-left px-4 py-3 hover:bg-gray-100 w-full">Reseñas</button>
    </div>
</div>
