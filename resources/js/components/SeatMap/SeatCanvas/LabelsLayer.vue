<template>
    <v-text v-for="(s, i) in seats.filter(seat =>
        seat &&
        typeof seat.x === 'number' && !isNaN(seat.x) &&
        typeof seat.y === 'number' && !isNaN(seat.y) &&
        Object.keys(seat).length > 0 &&
        seat.x !== 0 && seat.y !== 0 // Agregado para ocultar labels en 0,0
    )" :key="i" :config="{
    x: s.x,
    y: s.y - (s.radius || defaultRadius) - 6,
    text: s.label, align: 'center', fontSize: 14
}" />

</template>

<script setup>
const { seats, defaultRadius } = defineProps(['seats', 'defaultRadius'])
//console.log("LABELS SEATS:", seats)
</script>

