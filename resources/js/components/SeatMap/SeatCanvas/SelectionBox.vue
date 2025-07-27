<template>
    <v-rect v-if="selection.visible" :config="selection" />
</template>
<script setup>
import { watch } from 'vue'
defineProps({ modelValue: Object })
const selection = useVModel(props, 'modelValue')
// (los watchers para update:selection)
</script>
