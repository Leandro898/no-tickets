<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm">
            <h3 class="text-xl font-bold mb-4">Generar s</h3>

            <!-- Tipo de Entrada -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Tipo de Entrada</label>
                <select v-model="localSelected" class="w-full border p-2 rounded-lg">
                    <option v-for="t in tickets" :key="t.id" :value="t.id">
                        {{ t.nombre }} ({{ t.remaining }} disponibles)
                    </option>
                </select>
            </div>

            <!-- Cantidad -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Cantidad a Generar</label>
                <input type="number" v-model.number="localCount" class="w-full border p-2 rounded-lg" :min="1"
                    :max="remainingFor(localSelected)" />
                <p class="mt-1 text-xs text-gray-500">
                    Máx: {{ remainingFor(localSelected) }}
                </p>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <button @click="$emit('cancel')"
                    class="px-4 py-2 font-medium border border-red-400 text-red-600 rounded-lg hover:bg-red-50 transition">
                    Cancelar
                </button>
                <button @click="doGenerate" :disabled="!canGenerate"
                    class="px-5 py-2 font-semibold bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Generar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    tickets: { type: Array, required: true },
    count: { type: Number, required: true },
    selectedTicket: { type: [Number, String], default: null },
})
const emit = defineEmits(['update:count', 'selectTicket', 'generate', 'cancel'])

// Local copy para no mutar directo
const localCount = ref(props.count)
const localSelected = ref(props.selectedTicket)

// Computed para saber cuántos quedan del tipo seleccionado
function remainingFor(id) {
    const t = props.tickets.find(x => x.id === id)
    return t ? t.remaining : 0
}

// Podrás generar si hay ticket seleccionado y cantidad válida
const canGenerate = computed(() => {
    return (
        localSelected.value !== null &&
        localCount.value >= 1 &&
        localCount.value <= remainingFor(localSelected.value)
    )
})

function doGenerate() {
    // 1️⃣ Busca el objeto ticket en el array props.tickets
    const ticketObj = props.tickets.find(t => t.id === localSelected.value)
    // 2️⃣ Emite ese objeto
    emit('selectTicket', ticketObj)
    // 3️⃣ Mantén la lógica de count y generate
    emit('update:count', localCount.value)
    emit('generate')
}
</script>
