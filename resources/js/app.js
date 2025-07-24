import './bootstrap';
import { createApp } from 'vue';
import SeatMap from './components/SeatMap.vue';

const el = document.getElementById('seat-map-app');
if (el) {
    createApp(SeatMap, {
        eventId: el.dataset.eventId,
    }).mount(el);
}
