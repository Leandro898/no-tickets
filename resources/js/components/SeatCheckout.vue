<template>
    <div class="seat-checkout-container">
        <div class="seat-canvas">
            <SeatSelector :key="mapKey" :evento-slug="props.eventoSlug" @selection-change="onSelectionChange"
                class="w-full h-full" />
        </div>

        <div class="reserve-button-container">
            <button v-if="selectedSeats.length && !showPurchase" @click="reserveSeats" class="reserve-btn"
                :disabled="isLoading">
                <span v-if="isLoading">Procesando...</span>
                <span v-else>
                    🛎 Reservar {{ selectedSeats.length }} asiento
                    <span v-if="selectedSeats.length > 1">s</span>
                </span>
            </button>
        </div>

        <PurchasePanel :visible="showPurchase" :seats="selectedSeats" :reserved-until="reservedUntil"
            :isLoading="isLoading" @close="closePanel" @confirm="submitPayment" />
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
const isLoading = ref(false)

function onSelectionChange(seats) {
    selectedSeats.value = seats
}

async function reserveSeats() {
    const ids = selectedSeats.value.map(s => s.id)
    isLoading.value = true
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
    } finally {
        isLoading.value = false
    }
}

async function submitPayment({ seats, buyer }) {
    // Activamos el estado de carga justo al iniciar la función.
    isLoading.value = true;
    try {
        const payload = {
            seats,
            buyer_full_name: buyer.name,
            buyer_email: buyer.email,
            buyer_dni: buyer.dni || '',
        }
        const { data } = await axios.post(props.purchaseRoute, payload)
        // Si la llamada es exitosa, nos redirigimos y no necesitamos deshabilitar el estado de carga.
        window.location.href = data.redirect_url
    } catch (err) {
        // Si hay un error, volvemos a habilitar el botón para que el usuario pueda intentarlo de nuevo.
        alert('❌ Error al procesar el pago')
        isLoading.value = false;
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
}

.seat-canvas {
    width: 100%;
    height: calc(100vh - 80px);
}

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

.reserve-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

@media (max-width: 640px) {
    .seat-canvas {
        overflow-x: auto;
        padding: 0 0.5rem;
    }

    .seat-canvas>* {
        min-width: 800px;
    }
}
</style>
