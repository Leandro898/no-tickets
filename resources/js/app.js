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
    const eventoSlug = adminEl.dataset.eventoSlug // CORRECTO, queda como string
    const initialBgImageUrl = adminEl.dataset.bgImageUrl || ''
    createApp(SeatMap, { eventoSlug, initialBgImageUrl })
        .use(VueKonva)
        .mount(adminEl)
}

// ——— 2) Selector / Checkout para el front ———
const el = document.getElementById('seat-checkout')
if (el) {
    const eventoSlug = el.dataset.slug
    const purchaseRoute = el.dataset.purchaseRoute
    //console.log('[app.js] montando SeatCheckout con:', { eventoSlug, purchaseRoute })

    createApp(SeatCheckout, { eventoSlug, purchaseRoute })
        .use(VueKonva)
        .mount(el)
}

