<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
    <!-- Fondo oscurecido -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40" @click="open = false"></div>

    <!-- Contenedor del menú -->
    <div
        x-show="open"
        x-transition
        class="absolute bottom-16 right-0 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-56 py-2 z-50"
    >
        <!-- Items del menú -->
        <a href="/admin/eventos" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
            Eventos
        </a>
        <a href="/admin/scanner-test" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
            Scanner
        </a>
        <a href="/admin/cobros" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
            Cobros
        </a>
    </div>

    <!-- Botón flotante -->
    <button @click="open = !open"
        class="w-16 h-16 bg-primary-600 text-white rounded-full shadow-2xl flex items-center justify-center border-4 border-white"
        aria-label="Abrir menú">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
</div>
