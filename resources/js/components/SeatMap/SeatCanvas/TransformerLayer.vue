<script setup>
const props = defineProps({
    selectedCircle: Object // Recibe el nodo real, no array
})
</script>

<template>
    <v-transformer v-if="selectedCircle" :node="selectedCircle" draggable="false"
        :enabledAnchors="['top-left', 'top-right', 'bottom-left', 'bottom-right']" />
</template>
