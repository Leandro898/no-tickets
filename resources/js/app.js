import './bootstrap'
import { createApp } from 'vue'
import VueKonva from 'vue-konva'


// Toastr
import toastr from 'toastr'
import 'toastr/build/toastr.min.css'
window.toastr = toastr

// Componentes
import SeatMap from './components/SeatMap/Page.vue'     // editor en admin
import SeatCheckout from './components/SeatCheckout.vue'     // selector en front

// ——— 1) Editor interno Filament (admin) ———
const adminEl = document.getElementById('seat-map-app')
if (adminEl) {
    const eventoId = Number(adminEl.dataset.eventoId)
    const initialBgImageUrl = adminEl.dataset.bgImageUrl || ''
    createApp(SeatMap, { eventoId, initialBgImageUrl })
        .use(VueKonva)
        .mount(adminEl)
}

// ——— 2) Selector / Checkout para el front ———
const checkoutEl = document.getElementById('seat-checkout-app')
if (checkoutEl) {
    const eventoId = Number(checkoutEl.dataset.eventoId)
    const purchaseRoute = checkoutEl.dataset.purchaseRoute  // ← aquí
    console.log('[app.js] montando SeatCheckout con:', { eventoId, purchaseRoute })
    createApp(SeatCheckout, { eventoId, purchaseRoute })
        .use(VueKonva)
        .mount(checkoutEl)
}

