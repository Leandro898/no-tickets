<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm">
            <h3 class="text-xl font-bold mb-4">Generar asientos</h3>

            <!-- Tipo de Entrada -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Tipo de Entrada</label>
                <select v-model="localSelected" class="w-full border p-2 rounded-lg">
                    <option v-for="t in tickets" :key="t.id" :value="t.id">
                        {{ t.nombre }} ({{ remainingFor(t.id) }} disponibles)
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
import { ref, computed, watch, onMounted } from 'vue'

// FUNCION PARA VER LOGS DE STOCK
// onMounted(() => {
//     console.log('🔍 [Modal] props.tickets =', props.tickets)
//     console.log('🔍 [Modal] props.seats   =', props.seats)
//     console.log('🔍 [Modal] selectedTicket=', props.selectedTicket)
// })

const props = defineProps({
    tickets: { type: Array, required: true },      // [{ id, nombre, total }]
    seats: { type: Array, required: true },      // [{ entrada_id, … }]
    count: { type: Number, required: true },     // v-model:count
    selectedTicket: { type: [Number, String], default: null }, // v-model:selectedTicket
    remaining: Number    // ← la recibes para usarla directamente
})
const emit = defineEmits(['update:count', 'selectTicket', 'generate', 'cancel'])

// --- Copias locales para no mutar props directos
const localCount = ref(props.count)
const localSelected = ref(props.selectedTicket)

// --- Calcula dinámico: total - creados
function remainingFor(id) {
    if (!id) return 0

    const t = props.tickets.find(x => x.id === id)
    const total = t?.stock_inicial || 0    // ← aquí usamos la propiedad correcta

    const used = props.seats.filter(s => s.entrada_id === id).length
    

    return Math.max(total - used, 0)
}


// --- Habilita botón sólo si la cantidad cabe en el stock
const canGenerate = computed(() =>
    localSelected.value !== null &&
    localCount.value >= 1 &&
    localCount.value <= remainingFor(localSelected.value)
)

// --- Emitimos hacia el padre al cambiar
watch(localCount, v => emit('update:count', v))
watch(localSelected, v => {
    emit('selectTicket', v)
    // Si nos pasamos, reajustamos
    if (localCount.value > remainingFor(v)) {
        localCount.value = remainingFor(v) || 1
    }
})

// --- Dispara la generación definitiva
function doGenerate() {
    emit('generate')
}
</script>
