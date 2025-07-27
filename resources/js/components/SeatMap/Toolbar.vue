<template>
    <div class="toolbar flex gap-2 bg-white p-2 border-b">
        <!-- Seleccionar / Deseleccionar todo -->
        <button @click="$emit('toggle-select-all')" class="px-2">
            <input type="checkbox" :checked="allSelected" />
        </button>

        <!-- Undo / Redo -->
        <button @click="$emit('undo')" :disabled="!canUndo">↶</button>
        <button @click="$emit('redo')" :disabled="!canRedo">↷</button>

        <!-- Zoom In / Zoom Out -->
        <button @click="$emit('zoom-in')">＋</button>
        <button @click="$emit('zoom-out')">－</button>

        <!-- Reset View -->
        <button @click="$emit('reset-view')">⟳</button>

        <!-- Eliminar seleccionados -->
        <button @click="$emit('delete-selected')" :disabled="!anySelected">🗑️</button>

        <!-- Ayuda -->
        <button @click="$emit('show-help')">❓</button>
    </div>
</template>

<script setup>
import { computed } from 'vue'

// Recibimos los props que le pasas desde index.vue
const props = defineProps({
    seats: { type: Array, required: true },
    history: { type: Array, required: true },
    future: { type: Array, required: true },
})

// ¿Están TODOS seleccionados?
const allSelected = computed(
    () => props.seats.length > 0 && props.seats.every(s => s.selected)
)
// ¿Hay al menos uno seleccionado?
const anySelected = computed(
    () => props.seats.some(s => s.selected)
)
// ¿Podemos deshacer?
const canUndo = computed(
    () => props.history.length > 1
)
// ¿Podemos rehacer?
const canRedo = computed(
    () => props.future.length > 0
)
</script>

<style scoped>
.toolbar button {
    user-select: none;
    padding: 4px 8px;
    border: none;
    background: none;
    cursor: pointer;
}

.toolbar button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
