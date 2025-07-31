<template>
    <form :action="purchaseRoute" method="POST" class="h-full flex flex-col">
        <input type="hidden" name="_token" :value="csrfToken" />
        <seat-selector :evento-id="eventoId" @selection-change="onSelectionChange" class="flex-1" />
        <div class="p-4 border-t flex justify-between items-center">
            <div>
                <strong>Asientos:</strong>
                <span v-if="selectedSeats.length">{{ selectedSeats.join(', ') }}</span>
                <span v-else class="text-gray-500">ninguno</span>
            </div>
            <button type="submit" :disabled="!selectedSeats.length"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                Comprar ({{ selectedSeats.length }})
            </button>
        </div>

        <!-- inputs ocultos con los IDs -->
        <template v-for="id in selectedSeats" :key="id">
            <input type="hidden" name="seats[]" :value="id" />
        </template>
    </form>
</template>

<script setup>
import { ref } from 'vue'
import SeatSelector from './SeatSelector.vue'

const props = defineProps({
    eventoId: { type: Number, required: true },
    purchaseRoute: { type: String, required: true }
})

const selectedSeats = ref([])
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

function onSelectionChange(ids) {
    selectedSeats.value = ids
}
</script>
