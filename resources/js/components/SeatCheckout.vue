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
const selectedSeats = ref([])     // aquí guardamos los objetos asiento
const showPurchase = ref(false) // controla visibilidad del drawer

// Este método recibirá el array de IDs desde SeatSelector
// 2) Cuando SeatSelector emite los asientos seleccionados:
function onSelectionChange(seats) {
    selectedSeats.value = seats   // ahora son {id,label,price}
    showPurchase.value = seats.length > 0
}


// 3) Quitar asiento desde el drawer
// function removeSeat(id) {
//     selectedSeats.value = selectedSeats.value.filter(s => s.id !== id)
//     if (!selectedSeats.value.length) showPurchase.value = false
// }


async function handleConfirm({ seats, buyer }) {
    try {
        // 1) Reserva
        //await axios.post('/api/asientos/reservar', { seats })

        // 2) Compra simulada
        const { data } = await axios.post(
            props.purchaseRoute,
            {
                seats,
                buyer_full_name: buyer.name,
                buyer_email: buyer.email
            }
        )

        alert(`✅ Compra simulada exitosa. Orden ID: ${data.order.id}`)
        showPurchase.value = false
        // Opcional: redirigir a “Mis Entradas” o recargar
    }
    catch (err) {
        if (err.response?.status === 409) {
            const ocupados = err.response.data.ocupados
            alert(`❌ Los asientos ${ocupados.join(', ')} ya no están disponibles.`)
            // Forzar recarga del mapa
            window.location.reload()
        } else {
            alert(err.response?.data?.error || '❌ Error al procesar la compra.')
        }
    }
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
            <SeatSelector :evento-slug=" props.eventoSlug" :purchase-route="props.purchaseRoute"
                @selection-change="onSelectionChange" class="w-full h-full" />
        </div>
        <!-- 2) Drawer de compra -->
        <PurchasePanel :visible="showPurchase" :seats="selectedSeats" @remove="removeSeat" @close="onClosePanel"
            @confirm="handleConfirm" />

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
