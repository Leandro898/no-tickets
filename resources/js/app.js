import './bootstrap'
import { createApp } from 'vue'
import VueKonva from 'vue-konva'
import SeatMap from './components/SeatMap.vue'

const el = document.getElementById('seat-map-app')
if (el) {
    const app = createApp(SeatMap, {
        eventId: el.dataset.eventId,
    })
    app.use(VueKonva)
    app.mount(el)
}
