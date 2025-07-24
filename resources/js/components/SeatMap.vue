<template>
    <div>
        <p class="mb-4">Entradas a mapear: {{ tickets.length }}</p>
        <div ref="canvasContainer" class="border rounded p-4 h-96 bg-gray-50">
            <!-- Aquí operará tu librería de dibujo (p.ej. Konva, Fabric.js o SVG puro) -->
        </div>
        <button @click="saveSeats" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded">
            Guardar Mapa
        </button>
    </div>
</template>

<script>
export default {
    props: {
        eventId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            tickets: [],      // las entradas/tickets para este evento
            seatsData: [],    // los datos que vayamos armando
        }
    },
    async mounted() {
        // 1) Traer las entradas vía API
        const res = await fetch(`/api/eventos/${this.eventId}/entradas`)
        this.tickets = await res.json()

        // 2) Inicializar lienzo (SVG, Konva, etc.) aquí
        //    y, si ya hubiera asientos guardados, cargarlos.
    },
    methods: {
        async saveSeats() {
            // 3) Mandar POST con this.seatsData a tu endpoint:
            await fetch(`/api/eventos/${this.eventId}/asientos`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ seats: this.seatsData }),
            })
            alert('Se guardó el mapa de butacas!')
        },
    },
}
</script>
<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    console.log('SeatMap cargado correctamente.');
});
</script>
