<div x-data="{ open: false }" class="front-float-menu">
    <!-- Botón flotante -->
    <button @click="open = !open"
        style="
            position: fixed;
            bottom: 40px;      /* Más arriba del borde */
            right: 32px;       /* Más a la izquierda */
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 8px 24px 0 #0004;  /* Sombra más fuerte */
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            z-index: 1010;
        ">
        <svg width="26" height="26" fill="none" stroke="black" stroke-width="2"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Overlay y Menú -->
    <template x-if="open">
        <div>
            <!-- Fondo oscuro -->
            <div @click="open = false"
                style="
                    position: fixed;
                    inset: 0;
                    background: rgba(0,0,0,0.19);
                    z-index: 1005;
                    transition: background 0.3s;
                ">
            </div>

            <!-- Menú -->
            <div
                style="
                    position: fixed;
                    bottom: 98px;     /* Alineado al botón */
                    right: 38px;
                    background: #fff;
                    color: #222;
                    border-radius: 22px;
                    box-shadow: 0 8px 32px 0 #0002;
                    min-width: 144px;
                    padding: 18px 16px 15px 16px;
                    z-index: 1011;
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    animation: fadeInMenu 0.22s;
                ">
                <a href="/eventos" style="display: block; margin-bottom: 13px; font-size: 1.08rem; text-decoration: none; color: #222;">🗓️ Eventos</a>
                <a href="/mis-entradas" style="display: block; margin-bottom: 13px; font-size: 1.08rem; text-decoration: none; color: #222;">🎟️ Mis Entradas</a>
                <a href="/perfil" style="display: block; font-size: 1.08rem; text-decoration: none; color: #222;">👤 Mi Perfil</a>
            </div>
        </div>
    </template>

    <!-- Animación -->
    <style>
        @media (min-width: 768px) {
            .front-float-menu {
                display: none !important;
            }
        }
        @keyframes fadeInMenu {
            from { opacity: 0; transform: translateY(20px);}
            to { opacity: 1; transform: translateY(0);}
        }
    </style>
</div>
