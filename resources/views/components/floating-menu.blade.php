<div x-data="{ open: false }" class="filament-float-menu" style="z-index: 999;">
    <!-- Botón flotante, un poco más arriba del borde inferior -->
    <button @click="open = !open"
        style="
            position: fixed;
            bottom: 40px;      /* Ajuste: más arriba */
            right: 32px;       /* Ajuste: más a la izquierda */
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 8px 24px 0 #0004;  /* Sombra más marcada */
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            z-index: 1010;
        ">
        <!-- Ícono hamburguesa -->
        <svg width="27" height="27" fill="none" stroke="black" stroke-width="2"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Overlay y Menú -->
    <template x-if="open">
        <div>
            <!-- Fondo oscuro al abrir -->
            <div
                @click="open = false"
                style="
                    position: fixed;
                    inset: 0;
                    background: rgba(0,0,0,0.19);
                    z-index: 1005;
                    transition: background 0.3s;
                ">
            </div>

            <!-- Menú flotante -->
            <div
                style="
                    position: fixed;
                    bottom: 100px;    /* Ajuste para dejarlo alineado al botón */
                    right: 38px;
                    background: #fff;
                    color: #222;
                    border-radius: 22px;
                    box-shadow: 0 8px 32px 0 #0002;
                    min-width: 150px;
                    padding: 22px 20px 18px 20px;
                    z-index: 1011;
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    animation: fadeInMenu 0.22s;
                ">
                <a href="/admin/eventos" style="display: block; margin-bottom: 14px; font-size: 1.08rem; text-decoration: none; color: #222;">🗓️ Eventos</a>
                <a href="/admin/ticket-scanner" style="display: block; margin-bottom: 14px; font-size: 1.08rem; text-decoration: none; color: #222;">🔍 Scanner</a>
                <a href="/admin/oauth-connect-page" style="display: block; font-size: 1.08rem; text-decoration: none; color: #222;">💳 Pagos</a>
            </div>
        </div>
    </template>

    <!-- Animación del menú y solo mobile -->
    <style>
        @media (min-width: 768px) {
            .filament-float-menu {
                display: none !important;
            }
        }
        @keyframes fadeInMenu {
            from { opacity: 0; transform: translateY(20px);}
            to { opacity: 1; transform: translateY(0);}
        }
        body.menu-open {
            overflow: hidden !important;
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('menuState', () => ({
                open: false,
                init() {
                    this.$watch('open', value => {
                        document.body.classList.toggle('menu-open', value);
                    });
                }
            }));
        });
    </script>
</div>
