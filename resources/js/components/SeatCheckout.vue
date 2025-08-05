<!-- resources/js/components/SeatCheckout.vue -->
<script setup>
import { defineProps, ref } from 'vue'
import axios from 'axios'
import SeatSelector from './SeatSelector.vue'
import PurchasePanel from './PurchasePanel.vue'

const props = defineProps({
    eventoSlug: { type: String, required: true },
    purchaseRoute: { type: String, required: true },
})

// 1) Estado local
const mapKey = ref(0);
const selectedSeats = ref([])     // aquí guardamos los objetos asiento
const showPurchase = ref(false) // controla visibilidad del drawer

// Contdor de expiración de reserva
const reservedUntil = ref(null)

// Este método recibirá el array de IDs desde SeatSelector
// 2) Cuando SeatSelector emite los asientos seleccionados:
function onSelectionChange(seats) {
    selectedSeats.value = seats   // ahora son {id,label,price}
    // showPurchase.value = seats.length > 0
}

async function reserveSeats() {
    try {
        // 1) Reserva y obtenemos reserved_until
        const ids = selectedSeats.value.map(s => s.id)
        const res = await axios.post('/api/asientos/reservar', { seats: ids })
        reservedUntil.value = new Date(res.data.reserved_until)
        showPurchase.value = true

        // 2) Ahora abrimos el panel de compra
        // (el PurchasePanel recibirá reservedUntil como prop)
    } catch (err) {
        if (err.response?.status === 409) {
            alert(`❌ Ya no están disponibles: ${err.response.data.ocupados.join(', ')}`)
            mapKey.value++
        } else {
            alert('❌ Error al reservar')
        }
        return
    }
}

// Cuando el usuario confirma dentro del timer:
async function submitPayment({ seats, buyer }) {
    try {
        const payload = {
            seats,
            buyer_full_name: buyer.name,
            buyer_email: buyer.email,
            buyer_dni: buyer.dni || '',
        };
        const res = await axios.post(props.purchaseRoute, payload);
        window.location.href = res.data.redirect_url;
    } catch (err) {
        // Si viene 422, muestro el mensaje concreto
        if (err.response?.status === 422) {
            const data = err.response.data;
            // Validaciones de FormRequest:
            if (data.errors) {
                // Ejemplo: { errors: { seats: [...], buyer_full_name: [...] } }
                const msgs = Object.values(data.errors)
                    .flat()
                    .join('\n');
                alert(`❌ Errores de validación:\n${msgs}`);
            }
            // Excepciones lanzadas manualmente en el controller:
            else if (data.error) {
                alert(`❌ ${data.error}`);
            }
        } else {
            console.error(err);
            alert('❌ Error inesperado. Mira la consola.');
        }
    }
}


function closePanel() {
    showPurchase.value = false
    selectedSeats.value = []
    reservedUntil.value = null
    mapKey.value++
}

// 4) Recarga el mapa (por ejemplo recargando la página o volviendo a fetch)
function loadMap() {
    window.location.reload()
}

// 5) Al terminar la compra simulada
function onPurchased(order) {
    // order viene del backend (purchase-simulated)
    alert(`¡Compra simulada OK! Orden ID: ${order.id}`)
    // aquí podrías redirigir a "Mis Entradas" o similar
}


// Al pulsar “Comprar”, redirigimos a tu ruta de checkout pasando los IDs
function goToCheckout() {
    if (selectedSeats.value.length === 0) {
        alert('Por favor, seleccioná al menos un asiento.')
        return
    }
    const params = new URLSearchParams()
    selectedSeats.value.forEach(id => params.append('seats[]', id))
    window.location.href = `${props.purchaseRoute}?${params.toString()}`
}

// 6) Liberar un asiento
// Esta función se llamará desde el componente PurchasePanel
// cuando el usuario pulse el botón de "Quitar" en un asiento.
// También se llamará al cerrar el panel de compra si hay asientos seleccionados.
// Liberamos el asiento del backend y lo quitamos de la selección.
// Si no hay asientos seleccionados, ocultamos el panel de compra.
async function removeSeat(id) {
    try {
        await axios.post('/api/asientos/liberar', { seats: [id] })
    } catch (e) {
        console.error('No se pudo liberar asiento', id, e)
    }
    selectedSeats.value = selectedSeats.value.filter(s => s.id !== id)
    if (!selectedSeats.value.length) showPurchase.value = false
}

/*
 * Al cerrar el panel de compra, liberamos los asientos seleccionados
 * y limpiamos la selección.
 */
// Esta función se llamará desde el componente PurchasePanel
// cuando el usuario cierre el panel de compra.
// También se llamará al cerrar el panel desde el botón de "Cerrar".
// Si hay asientos seleccionados, los liberamos.

async function onClosePanel() {
    const ids = selectedSeats.value.map(s => s.id)
    if (ids.length) {
        try {
            await axios.post('/api/asientos/liberar', { seats: ids })
        } catch (e) {
            console.error('Error liberando al cerrar:', e)
        }
    }
    selectedSeats.value = []
    showPurchase.value = false
}
</script>

<template>
    <div class="flex-1 w-full flex items-center justify-center px-2 overflow-hidden">
        <!-- 1) El lienzo con los asientos -->
        <div class="w-full h-[calc(100vh-80px)] flex items-center justify-center">
            <SeatSelector :key="mapKey" :evento-slug=" props.eventoSlug" @selection-change="onSelectionChange"
                class="w-full h-full" />
        </div>
        <!-- 2) Drawer de compra -->
        <PurchasePanel :visible="showPurchase" :seats="selectedSeats" :reserved-until="reservedUntil"
            @close="closePanel" @confirm="submitPayment" />

        <!-- 2) Botón “Reservar” -->
        <button v-if="selectedSeats.length && !showPurchase" @click="reserveSeats"
            class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            🛎 Reservar {{ selectedSeats.length }} asiento<span v-if="selectedSeats.length > 1">s</span>
        </button>

        <!-- 2) Indicador de cuántos asientos seleccionó
        <p class="text-lg">
            Asientos seleccionados: <strong>{{ selectedSeats.length }}</strong>
        </p> -->

        <!-- 3) Botón para proceder al checkout -->
        <!-- <button @click="goToCheckout"
            class="px-6 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 disabled:opacity-50"
            :disabled="selectedSeats.length === 0">
            Comprar {{ selectedSeats.length }} asiento{{ selectedSeats.length > 1 ? 's' : '' }}
        </button> -->
    </div>
</template>
