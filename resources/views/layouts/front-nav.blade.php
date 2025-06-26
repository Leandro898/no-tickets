<header class="bg-white shadow w-full sticky top-0 z-50">
    <div class="pl-4 sm:pl-8 lg:pl-12 pr-4 py-4 flex justify-between items-center">

        <a href="{{ url('/') }}" class="text-2xl font-bold text-purple-700">Innova Ticket</a>
        <nav class="flex items-center text-base space-x-8">
            <a href="{{ route('eventos.index') }}" class="text-gray-700 hover:text-purple-700 font-medium">Eventos</a>
            @auth
                <a href="{{ route('mis-entradas') }}" class="text-gray-700 hover:text-purple-700 font-medium">Mis Entradas</a>
                <!-- Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center text-gray-700 hover:text-purple-700 font-medium focus:outline-none">
                        <span class="mr-2">{{ Auth::user()->name }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <!-- Dropdown Menu -->
                    <div
                        x-show="open"
                        @click.away="open = false"
                        class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg py-2 z-50"
                        style="display: none;"
                    >
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
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-purple-700 font-medium">Ingresar</a>
                <a href="{{ route('register') }}" class="text-gray-700 hover:text-purple-700 font-medium">Registrar</a>
            @endauth
        </nav>
    </div>
</header>
