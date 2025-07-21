<header class="bg-white shadow w-full sticky top-0 z-50">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 py-3 flex justify-between items-center">
        <a href="{{ url('/') }}"
            class="text-2xl font-extrabold text-purple-700 sm:text-3xl whitespace-nowrap tracking-tight leading-none">
            Tickets Pro
        </a>

        {{-- NAV DESKTOP --}}
        <nav class="hidden md:flex items-center space-x-3 sm:space-x-7 lg:space-x-10 nav-desktop">
            <a href="/"
                class="text-gray-700 hover:text-purple-700 font-medium text-lg px-2 py-1 rounded transition-all duration-150 hover:underline hover:underline-offset-8 hover:decoration-2">
                Eventos
            </a>
            @auth
            <a href="{{ route('mis-entradas') }}"
                class="text-gray-700 hover:text-purple-700 font-medium text-lg px-2 py-1 rounded transition-all duration-150 hover:underline hover:underline-offset-8 hover:decoration-2">
                Mis Entradas
            </a>
            @endauth
        </nav>

        {{-- DROPDOWN DEL USUARIO (VISIBLE EN TODOS LOS TAMAÑOS) --}}
        @auth
        <div class="relative ml-4" x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center text-gray-700 hover:text-purple-700 font-medium text-lg px-2 py-1 rounded transition-all duration-150 focus:outline-none">
                <span class="mr-1 truncate max-w-[120px] sm:max-w-none">{{ Auth::user()->name }}</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
        @endauth

        {{-- NAV MOBILE (solo guest) --}}
        @guest
        <nav class="flex md:hidden items-center space-x-4 nav-mobile">
            <a href="{{ route('login') }}"
                class="text-gray-700 hover:text-purple-700 font-medium text-base px-1 py-1 rounded transition-all duration-150">
                Ingresar
            </a>
            <a href="{{ route('register') }}"
                class="text-gray-700 hover:text-purple-700 font-medium text-base px-1 py-1 rounded transition-all duration-150">
                Registrar
            </a>
        </nav>
        @endguest
    </div>

    <style>
        .nav-desktop {
            display: none;
        }

        @media (min-width: 768px) {
            .nav-desktop {
                display: flex !important;
            }

            .nav-mobile {
                display: none !important;
            }
        }
    </style>
</header>