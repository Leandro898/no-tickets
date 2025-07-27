<template>
    <v-stage ref="stageRef" :config="{ width, height }">
        <v-layer ref="layerRef">
            <BackgroundImage :bgImage="bgImage" :width="width" :height="height" />
            <SelectionBox v-model="selection" />
            <SeatsLayer :seats="seats" :defaultRadius="defaultRadius" />
            <LabelsLayer :seats="seats" />
            <TransformerLayer ref="transformerRef" :selectedSeats="seats.filter(s => s.selected)" />
        </v-layer>
    </v-stage>
</template>
<script setup>
import { ref } from 'vue'
import BackgroundImage from './BackgroundImage.vue'
import SelectionBox from './SelectionBox.vue'
import SeatsLayer from './SeatsLayer.vue'
import LabelsLayer from './LabelsLayer.vue'
import TransformerLayer from './TransformerLayer.vue'
import { useCanvasInteractions } from '@/composables/useCanvasInteractions'

defineProps(['width', 'height', 'bgImage', 'seats', 'panMode'])
const emit = defineEmits(['update:seats', 'update:mapJSON', 'update:selection'])

const { stageRef, layerRef, transformerRef,
    selection, defaultRadius } =
    useCanvasInteractions({ props, emit })
</script>
