<template>
    <v-transformer v-if="nodes.length" ref="transRef" :config="config" @transformend="onTransformEnd"
        @dragmove="onTransformerDrag" @dragend="onTransformerDragEnd" />
</template>
<script setup>
import { watch, nextTick } from 'vue'
defineProps(['selectedSeats'])
defineEmits(['update:seats'])
// ref a transformer, watcher para nodes (buscar en layer)
// funciones onTransformEnd, onTransformerDrag, ...
</script>
