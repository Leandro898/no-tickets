<template>
    <v-circle v-for="(s, i) in seats" :key="i" :id="'seat-' + i" :config="config(s)" @dragstart="onSeatDragStart(i, $event)"
        @dragend="onSeatDragEnd(i, $event)" @click="onToggleSeat(i)" />
</template>
<script setup>
defineProps(['seats', 'defaultRadius'])
const emit = defineEmits(['update:seats'])
// funciones onSeatDragStart, onSeatDragEnd, onToggleSeat
</script>
