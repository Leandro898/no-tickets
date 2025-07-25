import './bootstrap'
import { createApp } from 'vue'
import VueKonva from 'vue-konva'
import SeatMap from './components/SeatMap.vue'

const el = document.getElementById('seat-map-app')
if (el) {
    const app = createApp(SeatMap, {
        eventoId: el.dataset.eventId, // ⬅️ Notá el nombre: eventoId
        initialBgImageUrl: el.dataset.bgImageUrl
    })
    app.use(VueKonva)
    app.mount(el)
}
