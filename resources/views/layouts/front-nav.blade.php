<header class="bg-white shadow w-full sticky top-0 z-50">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 py-3 flex justify-between items-center">
        <a href="{{ url('/') }}"
           class="text-xl font-extrabold text-purple-700 sm:text-2xl whitespace-nowrap">
            Innova Ticket
        </a>
        <nav class="flex items-center space-x-3 sm:space-x-8">
            <a href="/" class="text-gray-700 hover:text-purple-700 font-medium nav-desktop text-sm sm:text-base">Eventos</a>
            @auth
                <a href="{{ route('mis-entradas') }}" class="text-gray-700 hover:text-purple-700 font-medium nav-desktop text-sm sm:text-base">Mis Entradas</a>
                <!-- Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center text-gray-700 hover:text-purple-700 font-medium focus:outline-none text-sm sm:text-base">
                        <span class="mr-1 truncate max-w-[90px] sm:max-w-none">{{ Auth::user()->name }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-44 bg-white border rounded shadow-lg py-2 z-50"
                        x-cloak>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-purple-700 font-medium text-sm sm:text-base">Ingresar</a>
                <a href="{{ route('register') }}" class="text-gray-700 hover:text-purple-700 font-medium text-sm sm:text-base">Registrar</a>
            @endauth
        </nav>
    </div>
    <style>
        @media (max-width: 767px) {
            .nav-desktop { display: none !important; }
        }
    </style>
</header>
