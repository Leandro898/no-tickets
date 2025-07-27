<template>
    <v-text v-for="(s, i) in seats" :key="i" :config="{
        x: s.x,
        y: s.y - (s.radius || defaultRadius) - 6,
        text: s.label, align: 'center', fontSize: 14
    }" />
</template>
<script setup>
defineProps(['seats', 'defaultRadius'])
</script>
