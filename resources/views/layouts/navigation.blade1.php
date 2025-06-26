{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- IZQUIERDA: logo + enlaces -->
            <div class="flex">
                <!-- LOGO -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        {{-- Reemplaza por tu componente de logo o por texto --}}
                        <x-application-logo class="block h-9 w-auto fill-current text-purple-700" />
                    </a>
                </div>

                <!-- NAV LINKS (desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">
                        {{ __('Eventos') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- DERECHA: Auth Links / Profile dropdown -->
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                @guest
                    <div class="space-x-4">
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            {{ __('Ingresar') }}
                        </x-nav-link>
                        <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
                            {{ __('Registrar') }}
                        </x-nav-link>
                    </div>
                @else
                    <div class="space-x-8">
                        <x-nav-link :href="route('mis-entradas')" :active="request()->routeIs('mis-entradas')">
                            {{ __('Mis Entradas') }}
                        </x-nav-link>
                    </div>

                    <!-- Perfil dropdown -->
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                        {{ Auth::user()->name }}
                                        <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 
                                                     1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 
                                                     0 010-1.414z"
                                                  clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <!-- Perfil -->
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Perfil') }}
                                </x-dropdown-link>
                                <!-- Logout -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Cerrar sesión') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endguest
            </div>

            <!-- BOTÓN HAMBURGUESA (mobile) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400
                               hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }"
                              class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }"
                              class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- MENU MOBILE -->
    <div :class="{ 'block': open, 'hidden': ! open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">
                {{ __('Eventos') }}
            </x-responsive-nav-link>
            @guest
                <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">
                    {{ __('Ingresar') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">
                    {{ __('Registrar') }}
                </x-responsive-nav-link>
            @endguest
            @auth
                <x-responsive-nav-link :href="route('mis-entradas')" :active="request()->routeIs('mis-entradas')">
                    {{ __('Mis Entradas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            @endauth
        </div>
    </div>
</nav>
