<!-- resources/js/components/SeatMap/SeatControls.vue -->
<template>
    <div class="p-4 border-l bg-white w-64 space-y-4">
        <!-- Título -->
        <h3 class="font-semibold mb-2">Editar labels</h3>

        <!-- 1 asiento seleccionado -->
        <div v-if="selected.length === 1" class="space-y-2">
            <label class="block text-sm">Nuevo Label:</label>
            <input v-model="single" class="w-full border rounded px-2 py-1" placeholder="Etiqueta" />
            <button @click="applySingle" class="mt-2 w-full bg-purple-600 text-white rounded py-1">
                Aplicar
            </button>
        </div>

        <!-- Varias asientos seleccionados -->
        <div v-else-if="selected.length > 1" class="space-y-3">
            <div class="flex items-center gap-2">
                <label class="block text-sm w-1/3">Letra:</label>
                <input v-model="letter" maxlength="1" class="w-2/3 border rounded px-2 py-1" placeholder="A" />
            </div>

            <div class="flex items-center gap-2">
                <label class="block text-sm w-1/3">Desde:</label>
                <input v-model.number="start" type="number" min="1" class="w-2/3 border rounded px-2 py-1"
                    placeholder="1" />
            </div>

            <div class="flex items-center gap-2">
                <label class="block text-sm w-1/3">Hasta:</label>
                <input :value="end" disabled class="w-2/3 border rounded bg-gray-100 px-2 py-1 text-gray-600" />
            </div>

            <button @click="applyRange" class="mt-2 w-full bg-purple-600 text-white rounded py-1">
                Asignar rango
            </button>
        </div>

        <!-- Ningún asiento -->
        <div v-else class="text-gray-500">
            Selecciona uno o más asientos
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    selected: { type: Array, required: true },
})
const emit = defineEmits(['rename'])

// Para un solo asiento
const single = ref('')

// Para rango de asientos
const letter = ref('A')
const start = ref(1)

// Computed para el valor "Hasta"
const end = computed(() => start.value + props.selected.length - 1)

// Cuando cambie el número de asientos seleccionados en rango, reseteamos el start a 1
watch(
    () => props.selected.length,
    (n) => {
        if (n > 1) {
            start.value = 1
        }
    }
)

// Emitir nuevo label individual
function applySingle() {
    emit('rename', { type: 'single', label: single.value })
}

// Emitir rango de labels
function applyRange() {
    emit('rename', { type: 'range', letter: letter.value, start: start.value })
}
</script>

<style scoped>
/* Estilos específicos para SeatControls si los necesitas */
</style>
