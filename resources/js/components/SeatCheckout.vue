<!-- resources/js/components/SeatCheckout.vue -->
<script setup>
import { defineProps, ref } from 'vue'
import SeatSelector from './SeatSelector.vue'

const props = defineProps({
    eventoSlug: { type: String, required: true },
    purchaseRoute: { type: String, required: true },
})

const selectedSeats = ref([])

// Este método recibirá el array de IDs desde SeatSelector
function onSelectionChange(ids) {
    selectedSeats.value = ids
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
</script>

<template>
    <div class="flex flex-col items-center gap-4">
        <!-- 1) El lienzo con los asientos -->
        <div class="w-full h-[600px]">
            <SeatSelector :evento-slug="props.eventoSlug" :purchase-route="props.purchaseRoute"
                @selection-change="onSelectionChange" class="w-full h-full" />
        </div>

        <!-- 2) Indicador de cuántos asientos seleccionó -->
        <p class="text-lg">
            Asientos seleccionados: <strong>{{ selectedSeats.length }}</strong>
        </p>

        <!-- 3) Botón para proceder al checkout -->
        <button @click="goToCheckout"
            class="px-6 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 disabled:opacity-50"
            :disabled="selectedSeats.length === 0">
            Comprar {{ selectedSeats.length }} asiento{{ selectedSeats.length > 1 ? 's' : '' }}
        </button>
    </div>
</template>
