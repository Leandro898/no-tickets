<template>
    <div>
        <v-stage :config="{ width, height }">
            <v-layer>
                <!-- Imagen de fondo -->
                <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />
                <!-- Renderizar asientos -->
                <v-circle v-for="(seat, i) in seats" :key="seat.id" :config="{
                    x: seat.x,
                    y: seat.y,
                    radius: seat.radius,
                    fill: seat.occupied ? '#ddd' : '#72d759', // Cambia según esté ocupado
                    stroke: '#555',
                    strokeWidth: 2
                }" />
                <!-- Etiquetas -->
                <v-text v-for="(seat, i) in seats" :key="'label-' + seat.id" :config="{
                    x: seat.x,
                    y: seat.y + seat.radius + 14,
                    text: seat.label,
                    fontSize: 16,
                    fill: '#222',
                    align: 'center'
                }" />
            </v-layer>
        </v-stage>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useImage from 'vue-konva/lib/useImage'

const props = defineProps({
    eventoId: { type: [Number, String], required: true },
})

// Datos reactivos
const seats = ref([])
const width = ref(1000)  // Valor por defecto, reemplazar con lo que venga de la API
const height = ref(800)
const bgImageUrl = ref('')
const bgImage = ref(null)

// Traer datos del backend
onMounted(async () => {
    const res = await fetch(`/api/eventos/${props.eventoId}/map`)
    const data = await res.json()
    seats.value = data.seats
    width.value = data.width || 1000
    height.value = data.height || 800
    bgImageUrl.value = data.bg_image_url

    if (bgImageUrl.value) {
        const img = new window.Image()
        img.src = bgImageUrl.value
        img.onload = () => {
            bgImage.value = img
        }
    }
})
</script>
