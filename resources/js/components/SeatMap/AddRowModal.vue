<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Agregar fila de butacas</h3>

            <!-- Sector -->
            <label class="block mb-2 text-sm font-medium">Sector</label>
            <select v-model="sector" class="w-full border rounded px-2 py-1 mb-4">
                <option v-for="s in sectors" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>

            <!-- Prefijo -->
            <label class="block mb-2 text-sm font-medium">Prefijo</label>
            <input v-model="prefix" type="text" class="w-full border rounded px-2 py-1 mb-4" />

            <!-- Primer valor -->
            <label class="block mb-2 text-sm font-medium">Primer valor de la fila</label>
            <input v-model.number="start" type="number" class="w-full border rounded px-2 py-1 mb-4" />

            <!-- Cantidad -->
            <label class="block mb-2 text-sm font-medium">Cantidad de butacas</label>
            <input v-model.number="count" type="number" class="w-full border rounded px-2 py-1 mb-6" />

            <!-- Botones -->
            <div class="flex justify-end gap-2">
                <button @click="$emit('cancel')" class="px-4 py-2 rounded border text-gray-700 hover:bg-gray-100">
                    Cancelar
                </button>
                <button @click="onAdd" class="px-4 py-2 rounded bg-purple-600 text-white hover:bg-purple-700">
                    + Agregar fila
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    sectors: { type: Array, required: true }
})
const emit = defineEmits(['add', 'cancel'])

const sector = ref(props.sectors[0]?.id || null)
const prefix = ref('A')
const start = ref(1)
const count = ref(10)

function onAdd() {
    emit('add', {
        sectorId: sector.value,
        prefix: prefix.value,
        start: start.value,
        count: count.value,
    })
}
</script>
