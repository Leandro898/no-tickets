<div>
    @auth
    <div x-data="{ open: false }" class="relative z-[99]">
        <style>
            .menu-enter {
                opacity: 0;
                transform: translateX(100%);
            }
            .menu-enter-active {
                transition: all 0.3s ease;
                opacity: 1;
                transform: translateX(0);
            }
            .menu-leave {
                opacity: 1;
                transform: translateX(0);
            }
            .menu-leave-active {
                transition: all 0.3s ease;
                opacity: 0;
                transform: translateX(100%);
            }
        </style>

        <!-- Overlay + Menú -->
        <template x-if="open">
            <div class="fixed inset-0 z-[90] flex items-end justify-end">
                <!-- Fondo oscuro -->
                <div @click="open = false"
                     class="absolute inset-0 bg-black/50 transition-opacity duration-300"></div>

                <!-- Menú -->
                <div x-ref="menu"
                     x-init="$nextTick(() => {
                         $refs.menu.classList.add('menu-enter');
                         requestAnimationFrame(() => {
                             $refs.menu.classList.add('menu-enter-active');
                         });
                     })"
                     @mouseleave="$refs.menu.classList.add('menu-leave-active'); setTimeout(() => open = false, 300)"
                     class="relative mt-auto mr-6 w-60 bg-white text-black rounded-xl shadow-2xl p-4 z-[99]"
                     style="margin-bottom: 120px"
                >
                    <nav class="space-y-2 text-sm">
                        <a href="https://prueba.cyberespacio.online/admin/eventos" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded">
                            🗓️ <span>Eventos</span>
                        </a>
                        <a href="https://prueba.cyberespacio.online/admin/ticket-scanner" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded">
                            🔍 <span>Scanner</span>
                        </a>
                        <a href="https://prueba.cyberespacio.online/admin/oauth-connect-page" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded">
                            💳 <span>Pagos</span>
                        </a>
                    </nav>
                </div>
            </div>
        </template>

        <!-- Botón flotante -->
        <button @click="open = !open"
            class="fixed bottom-6 right-6 z-[100] bg-white rounded-full p-3 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    @endauth
</div>
