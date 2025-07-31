<template>
    <v-stage :config="{ width: canvasW, height: canvasH }">
        <v-layer>
            <v-circle v-for="seat in seats" :key="seat.id" :config="{
                id: 'seat-' + seat.id,
                x: seat.x,
                y: seat.y,
                radius: seat.radius,
                fill: seat.selected ? selectedFill : defaultFill,
                stroke: seat.selected ? selectedStroke : defaultStroke,
                strokeWidth: 2,
                draggable: false
            }" @click="toggle(seat.id)" />
        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Ahora recibimos el slug del evento, no el ID
const props = defineProps({
    eventoSlug: { type: String, required: true }
})
const emit = defineEmits(['selection-change'])

// Dimensiones de tu canvas
const canvasW = 800
const canvasH = 600

// Colores
const defaultFill = '#e5e7eb'
const defaultStroke = '#a1a1aa'
const selectedFill = '#a78bfa'
const selectedStroke = '#7c3aed'

// Estado local
const seats = ref([])
const selected = ref([])

/**
 * Trae los asientos en base al SLUG del evento
 * (usa la ruta API: GET /api/eventos/{slug}/asientos)
 */
function loadSeats() {
    axios.get(`/api/eventos/${props.eventoSlug}/asientos`)
        .then(resp => {
            seats.value = resp.data.map(s => ({
                id: s.id,
                x: s.x,
                y: s.y,
                radius: s.radius ?? 22,
                selected: false
            }))
        })
        .catch(err => {
            console.error('No pude cargar los asientos:', err)
        })
}

/**
 * Alterna selección de un asiento, emitiendo el array de ids seleccionados
 */
function toggle(id) {
    const seat = seats.value.find(s => s.id === id)
    seat.selected = !seat.selected

    selected.value = seats.value
        .filter(s => s.selected)
        .map(s => s.id)

    emit('selection-change', selected.value)
}

onMounted(loadSeats)
</script>
