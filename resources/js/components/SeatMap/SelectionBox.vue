<!-- resources/js/components/SeatMap/SeatCanvas/SelectionBox.vue -->
<template>
    <v-rect v-if="modelValue.visible" :config="{
        x: modelValue.width < 0 ? modelValue.x + modelValue.width : modelValue.x,
        y: modelValue.height < 0 ? modelValue.y + modelValue.height : modelValue.y,
        width: Math.abs(modelValue.width) || 1,
        height: Math.abs(modelValue.height) || 1,
        fill: 'rgba(60,120,255,0.2)',
        stroke: 'rgba(60,120,255,0.7)',
        dash: [4, 4],
        listening: false
    }" />
</template>


<script setup>
const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
})
// No hace falta defineEmits si no vas a emitir cambios desde aquí
</script>

<style scoped>
/* si quisieras estilos específicos */
</style>
