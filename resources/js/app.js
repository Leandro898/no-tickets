import './bootstrap'
import { createApp } from 'vue'
import VueKonva from 'vue-konva'
import SeatMap from './components/SeatMap/Page.vue'

// IMPORTAR TOAST
import toastr from 'toastr'
import 'toastr/build/toastr.min.css'
window.toastr = toastr

const el = document.getElementById('seat-map-app')

if (el) {
    // 1️⃣ Leemos data-evento-id en lugar de data-event-id
    const eventoId = Number(el.dataset.eventoId)
    const initialBgImageUrl = el.dataset.bgImageUrl || ''

    if (!eventoId) {
        console.error('❌ No se encontró o es inválido el atributo data-evento-id en #seat-map-app')
    }

    // 2️⃣ Creamos la app pasándole los props correctos
    const app = createApp(SeatMap, {
        eventoId,
        initialBgImageUrl
    })

    app.use(VueKonva)
    app.mount(el)
}
