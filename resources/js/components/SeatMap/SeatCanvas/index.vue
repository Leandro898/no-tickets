<template>
    <v-stage ref="stageRef" :config="{ width, height }" @mousedown="onMouseDown" @mousemove="onMouseMove"
        @mouseup="onMouseUp" @click="onStageClick">
        <v-layer ref="layerRef">

            <!-- Fondo -->
            <BackgroundImage :bgImage="bgImage" :width="width" :height="height" />

            <!-- Selector por recuadro -->
            <SelectionBox v-model="selection" />

            <!-- Asientos -->
            <SeatsLayer ref="seatsLayerRef" :seats="seats" :defaultRadius="defaultRadius"
                @update:seats="ns => emit('update:seats', ns)" />

            <!-- Etiquetas encima de los asientos -->
            <LabelsLayer :seats="seats" :defaultRadius="defaultRadius" />

            <!-- Rectángulo invisible para mover el grupo -->
            <v-rect v-if="transformerNodes.length" :x="bbox.x" :y="bbox.y" :width="bbox.width" :height="bbox.height"
                fill="#ffffff" :opacity="0.001" :draggable="true" :listening="true" :strokeWidth="0"
                @mouseover="onGroupMouseOver" @mouseout="onGroupMouseOut" @dragstart="handleGroupDragStart"
                @dragmove="onGroupDragMove" @dragend="handleGroupDragEnd" />

            <!-- Transformer para resize/move de nodos -->
            <v-transformer ref="transformerRef" v-if="transformerNodes.length" :nodes="transformerNodes"
                :config="{ enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'] }" />
        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue'
import { useCanvasInteractions } from '@/composables/useCanvasInteractions'
import BackgroundImage from './BackgroundImage.vue'
import SelectionBox from './SelectionBox.vue'
import SeatsLayer from './SeatsLayer.vue'
import LabelsLayer from './LabelsLayer.vue'

// — Props y emits —
const props = defineProps({
    width: { type: Number, required: true },
    height: { type: Number, required: true },
    bgImage: { type: Object, default: null },
    seats: { type: Array, required: true },
    panMode: { type: Boolean, default: false },
})
const emit = defineEmits(['update:seats', 'update:mapJSON', 'update:selection'])

// — Refs a Stage/Layer/Transformer y SeatsLayer —
const stageRef = ref(null)
const layerRef = ref(null)
const transformerRef = ref(null)
const seatsLayerRef = ref(null)

// — Composable de panning y selección por recuadro —
const {
    selection,
    defaultRadius,
    didRectSelect,
    onMouseDown,
    onMouseMove,
    onMouseUp
} = useCanvasInteractions({ props, emit })

// — Exponer stage hacia el padre si hiciera falta —
defineExpose({ getStage: () => stageRef.value?.getStage() })

// — Nodos seleccionados para el transformer —
const transformerNodes = computed(() =>
    seatsLayerRef.value?.selectedCircleRefs || []
)

// — Bounding box de los asientos seleccionados —
const bbox = computed(() => {
    const sel = props.seats.filter(s => s.selected)
    if (!sel.length) return { x: 0, y: 0, width: 0, height: 0 }
    const rs = sel.map(s => s.radius ?? defaultRadius)
    const xs = sel.map((s, i) => [s.x - rs[i], s.x + rs[i]]).flat()
    const ys = sel.map((s, i) => [s.y - rs[i], s.y + rs[i]]).flat()
    return {
        x: Math.min(...xs),
        y: Math.min(...ys),
        width: Math.max(...xs) - Math.min(...xs),
        height: Math.max(...ys) - Math.min(...ys)
    }
})

// — Enganchar el transformer cada vez que cambian los nodos —
watch(
    transformerNodes,
    async nodes => {
        if (!nodes.length) return
        await nextTick()
        const tr = transformerRef.value.getNode()
        tr.nodes(nodes)
        tr.moveToTop()
        layerRef.value.getNode().batchDraw()
    },
    { immediate: true, flush: 'post' }
)

// — Movimiento grupal desde el rectángulo invisible —
const groupDragStartPos = ref(null)
const groupStartSeats = ref([])

function onGroupDragStart(e) {
    groupDragStartPos.value = { x: e.target.x(), y: e.target.y() }
    groupStartSeats.value = props.seats.map(s => ({ x: s.x, y: s.y, selected: s.selected }))
}

function onGroupDragMove(e) {
    const pos = { x: e.target.x(), y: e.target.y() }
    const dx = pos.x - groupDragStartPos.value.x
    const dy = pos.y - groupDragStartPos.value.y
    const updated = props.seats.map((s, i) =>
        s.selected
            ? { ...s, x: groupStartSeats.value[i].x + dx, y: groupStartSeats.value[i].y + dy, selected: true }
            : s
    )
    emit('update:seats', updated)
}

function onGroupDragEnd(e) {
    // resetear estado de arrastre
    groupDragStartPos.value = null
    groupStartSeats.value = []
}

// — Handler combinado para dragstart: cursor + lógica —
function handleGroupDragStart(e) {
    onGroupMouseOver()
    onGroupDragStart(e)
}

// — Handler combinado para dragend: lógica + reset cursor —
function handleGroupDragEnd(e) {
    onGroupDragEnd(e)
    onGroupMouseOut()
}

// — Cambiar cursor al entrar/salir del rectángulo grupal —
function onGroupMouseOver() {
    const stage = stageRef.value?.getStage()
    if (stage) stage.container().style.cursor = 'move'
}
function onGroupMouseOut() {
    const stage = stageRef.value?.getStage()
    if (stage) stage.container().style.cursor = 'default'
}

// — Click en fondo: deselecciona todo salvo justo tras rectSelect —
function onStageClick(e) {
    const cls = e.target.getClassName?.()
    if (didRectSelect.value) {
        didRectSelect.value = false
        return
    }
    if (cls !== 'Circle') {
        emit('update:seats', props.seats.map(s => ({ ...s, selected: false })))
    }
}
</script>

<style scoped>
/* estilos opcionales */
</style>
