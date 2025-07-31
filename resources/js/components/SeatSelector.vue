<script setup>
import { defineProps, defineEmits, ref, onMounted } from 'vue'
import axios from 'axios'

// 1️⃣ Define los props que recibes del padre
const props = defineProps({
    eventoSlug: { type: String, required: true },
    purchaseRoute: { type: String, required: true }
})

// 2️⃣ Crea el emitter para comunicarte con el padre
const emit = defineEmits(['selection-change'])

const seats = ref([])
const selected = ref([])

function loadSeats() {
    // 3️⃣ Usa props.eventoSlug, ya no eventoId
    axios.get(`/api/eventos/${props.eventoSlug}/asientos`)
        .then(res => {
            seats.value = res.data.map(s => ({
                id: s.id,
                x: s.x,
                y: s.y,
                radius: s.radius ?? 22,
                selected: false
            }))
        })
        .catch(err => console.error('No pude cargar los asientos:', err))
}

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

<template>
    <v-stage :config="{ width: 800, height: 600 }">
        <v-layer>
            <v-circle v-for="seat in seats" :key="seat.id" :config="{
                x: seat.x, y: seat.y,
                radius: seat.radius,
                fill: seat.selected ? '#a78bfa' : '#e5e7eb',
                stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
                strokeWidth: 2
            }" @click="toggle(seat.id)" />
        </v-layer>
    </v-stage>
</template>
