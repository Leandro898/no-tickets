<template>
    <div class="seat-checkout-container">
        <!-- El lienzo de asientos -->
        <div class="seat-canvas">
            <SeatSelector :key="mapKey" :evento-slug="props.eventoSlug" @selection-change="onSelectionChange"
                class="w-full h-full" />
        </div>

        <!-- Botón RESERVAR siempre visible -->
        <div class="reserve-button-container">
            <button v-if="selectedSeats.length && !showPurchase" @click="reserveSeats" class="reserve-btn">
                🛎 Reservar {{ selectedSeats.length }} asiento<span v-if="selectedSeats.length > 1">s</span>
            </button>
        </div>

        <!-- Drawer de compra -->
        <PurchasePanel :visible="showPurchase" :seats="selectedSeats" :reserved-until="reservedUntil"
            @close="closePanel" @confirm="submitPayment" @remove="removeSeat" />
    </div>
</template>

<script setup>
import { defineProps, ref } from 'vue'
import axios from 'axios'
import SeatSelector from './SeatSelector.vue'
import PurchasePanel from './PurchasePanel.vue'

const props = defineProps({
    eventoSlug: String,
    purchaseRoute: String,
})

const mapKey = ref(0)
const selectedSeats = ref([])
const showPurchase = ref(false)
const reservedUntil = ref(null)

function onSelectionChange(seats) {
    selectedSeats.value = seats
}

async function reserveSeats() {
    const ids = selectedSeats.value.map(s => s.id)
    try {
        const { data } = await axios.post('/api/asientos/reservar', { seats: ids })
        reservedUntil.value = new Date(data.reserved_until)
        showPurchase.value = true
    } catch (err) {
        if (err.response?.status === 409) {
            alert(`❌ Ya no están disponibles: ${err.response.data.ocupados.join(', ')}`)
            mapKey.value++
        } else {
            alert('❌ Error al reservar')
        }
    }
}

async function submitPayment({ seats, buyer }) {
    try {
        const payload = {
            seats,
            buyer_full_name: buyer.name,
            buyer_email: buyer.email,
            buyer_dni: buyer.dni || '',
        }
        const { data } = await axios.post(props.purchaseRoute, payload)
        window.location.href = data.redirect_url
    } catch (err) {
        // manejo simplificado…
        alert('❌ Error al procesar el pago')
    }
}

function closePanel() {
    showPurchase.value = false
    selectedSeats.value = []
    reservedUntil.value = null
    mapKey.value++
}

async function removeSeat(id) {
    await axios.post('/api/asientos/liberar', { seats: [id] })
    selectedSeats.value = selectedSeats.value.filter(s => s.id !== id)
    if (!selectedSeats.value.length) showPurchase.value = false
}
</script>

<style scoped>
.seat-checkout-container {
    position: relative;
    min-height: calc(100vh - 80px);
    /* ajustá según tu header/footer */
}

.seat-canvas {
    width: 100%;
    height: calc(100vh - 80px);
}

/* — botón RESERVAR — */
.reserve-button-container {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 1000;
}

.reserve-btn {
    background: #16a34a;
    color: white;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    cursor: pointer;
}

.reserve-btn:hover {
    background: #15803d;
}

/* En pantallas muy chicas, que ocupe todo el ancho al pie */
@media (max-width: 640px) {
    .seat-canvas {
        /* habilita scroll si el mapa sigue siendo más ancho que la pantalla */
        overflow-x: auto;
        /* opcional: un poco de “padding” para que no quede pegado al borde */
        padding: 0 0.5rem;
    }

    /* si quieres que el contenedor interno mantenga su ancho original */
    .seat-canvas>* {
        min-width: 800px;
        /* pon aquí el ancho base (BASE_CANVAS_WIDTH) */
    }
}
</style>
