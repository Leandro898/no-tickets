<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        {{-- LOGO --}}
        <a href="{{ url('/') }}" class="text-purple-700 text-2xl font-bold">
          Innova Ticket
        </a>
  
        {{-- LINKS DESKTOP --}}
        <div class="hidden sm:flex sm:items-center sm:space-x-12">
          <x-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">
            Eventos
          </x-nav-link>
  
          @guest
            <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
              Ingresar
            </x-nav-link>
            <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
              Registrar
            </x-nav-link>
          @endguest
  
          @auth
            <x-nav-link :href="route('mis-entradas')" :active="request()->routeIs('mis-entradas')">
              Mis Entradas
            </x-nav-link>
  
            {{-- DROPDOWN PERFIL --}}
            <x-dropdown align="right" width="48">
              <x-slot name="trigger">
                <button
                  class="inline-flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                  {{ Auth::user()->name }}
                  <svg class="ml-1 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 
                                1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 
                                1 0 010-1.414z"
                          clip-rule="evenodd"/>
                  </svg>
                </button>
              </x-slot>
              <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                  Perfil
                </x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <x-dropdown-link
                    :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    Cerrar sesión
                  </x-dropdown-link>
                </form>
              </x-slot>
            </x-dropdown>
          @endauth
        </div>
  
        {{-- HAMBURGUESA MOBILE --}}
        <div class="-mr-2 flex sm:hidden">
          <button @click="open = ! open"
                  class="inline-flex items-center justify-center p-2 rounded-md text-gray-400
                         hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
              <path :class="{ 'inline-flex': !open, 'hidden': open }"
                    class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
              <path :class="{ 'inline-flex': open, 'hidden': !open }"
                    class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  
    {{-- MENÚ MOBILE --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link
          :href="route('eventos.index')"
          :active="request()->routeIs('eventos.*')">
          Eventos
        </x-responsive-nav-link>
  
        @guest
          <x-responsive-nav-link
            :href="route('login')"
            :active="request()->routeIs('login')">
            Ingresar
          </x-responsive-nav-link>
          <x-responsive-nav-link
            :href="route('register')"
            :active="request()->routeIs('register')">
            Registrar
          </x-responsive-nav-link>
        @endguest
  
        @auth
          <x-responsive-nav-link
            :href="route('mis-entradas')"
            :active="request()->routeIs('mis-entradas')">
            Mis Entradas
          </x-responsive-nav-link>
          <x-responsive-nav-link :href="route('profile.edit')">
            Perfil
          </x-responsive-nav-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link
              :href="route('logout')"
              onclick="event.preventDefault(); this.closest('form').submit();">
              Cerrar sesión
            </x-responsive-nav-link>
          </form>
        @endauth
      </div>
    </div>
  </nav>
  