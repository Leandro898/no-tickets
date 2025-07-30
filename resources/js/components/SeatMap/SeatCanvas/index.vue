<!-- C:\xampp\htdocs\no-tickets\resources\js\components\SeatMap\SeatCanvas\index.vue -->
<template>
    <div v-bind="$attrs">
        <v-stage ref="stageRef" :config="{ width, height }" @mousedown="onMouseDown" @mousemove="onMouseMove"
            @mouseup="onMouseUp" @click="onStageClick">
            <v-layer ref="layerRef">
                <!-- 1) Fondo -->
                <BackgroundImage :bgImage="bgImage" :width="width" :height="height" />

                <!-- 2) Selector por recuadro de asientos -->
                <SelectionBox v-model="selection" />

                <!-- 3) Shapes: rect, circle, text -->
                <template v-for="(shape, i) in shapes" :key="i">
                    <v-rect v-if="shape.type === 'rect'" :ref="el => shapeRefs[i] = el" :config="{
                        x: shape.x,
                        y: shape.y,
                        width: shape.width,
                        height: shape.height,
                        stroke: shape.stroke || 'gray',
                        strokeWidth: shape.strokeWidth || 2,
                        rotation: shape.rotation || 0
                    }" draggable @dragend="onShapeDragEnd(i, $event)" @click="onShapeClick(i, $event)" />
                    <v-circle v-else-if="shape.type === 'circle'" :ref="el => shapeRefs[i] = el" :config="{
                        x: shape.x,
                        y: shape.y,
                        radius: shape.radius,
                        stroke: shape.stroke || 'gray',
                        strokeWidth: shape.strokeWidth || 2,
                        rotation: shape.rotation || 0
                    }" draggable @dragend="onShapeDragEnd(i, $event)" @click="onShapeClick(i, $event)" />
                    <v-text v-else :ref="el => shapeRefs[i] = el" :config="{
                        x: shape.x,
                        y: shape.y,
                        text: shape.label,
                        fontSize: shape.fontSize || 18,
                        rotation: shape.rotation || 0
                    }" draggable @dragend="onShapeDragEnd(i, $event)" @dblclick="onShapeTextEdit(i)"
                        @click="onShapeClick(i, $event)" />
                </template>

                <!-- 4) Transformer para shapes -->
                <v-transformer v-if="shapeTransformerNodes.length" ref="shapeTransformerRef"
                    :nodes="shapeTransformerNodes" :config="{
                        enabledAnchors: [
                            'top-left', 'top-right',
                            'bottom-left', 'bottom-right',
                            'middle-left', 'middle-right',
                            'top-center', 'bottom-center'
                        ]
                    }" @transformend="onShapeTransformEnd" />

                <!-- 5) Tus asientos “reales” -->
                <SeatsLayer ref="seatsLayerRef" :seats="seatItems" :defaultRadius="defaultRadius"
                    @update:seats="onSeatsLayerUpdate" />
                <LabelsLayer :seats="seatItems" :defaultRadius="defaultRadius" />

                <!-- 6) Transformer de grupo de asientos -->
                <template v-if="transformerNodes.length">
                    <!-- 6a) Recuadro casi invisible que captura el drag -->
                    <v-rect :x="bbox.x" :y="bbox.y" :width="bbox.width" :height="bbox.height" fill="#ffffff"
                        :opacity="0.001" listening draggable @dragstart="handleGroupDragStart"
                        @dragmove="onGroupDragMove" @dragend="handleGroupDragEnd" />
                    <!-- 6b) Transformer “pegado” a los círculos seleccionados -->
                    <v-transformer ref="transformerRef" :nodes="transformerNodes" :config="{
                        enabledAnchors: [
                            'top-left', 'top-right',
                            'bottom-left', 'bottom-right'
                        ]
                    }" />
                </template>
            </v-layer>
        </v-stage>
    </div>
</template>

<script setup>
console.log('✅ SeatCanvas/index.vue cargado')


import { ref, computed, watch, nextTick } from 'vue'
import { useCanvasInteractions } from '@/composables/useCanvasInteractions'
import BackgroundImage from './BackgroundImage.vue'
import SelectionBox from './SelectionBox.vue'
import SeatsLayer from './SeatsLayer.vue'
import LabelsLayer from './LabelsLayer.vue'

// — Props y Emits —
const props = defineProps({
    width: { type: Number, required: true },
    height: { type: Number, required: true },
    bgImage: { type: Object, default: null },
    seats: { type: Array, required: true },
    panMode: { type: Boolean, default: false },
})
const emit = defineEmits(['update:seats', 'update:mapJSON', 'update:selection'])

// — Refs de Konva y Vue —
const stageRef = ref(null)
const layerRef = ref(null)
const transformerRef = ref(null)
const seatsLayerRef = ref(null)
const shapeRefs = ref([])

// — Canvas interactions (pan + selección) —
const {
    selection,
    defaultRadius,
    didRectSelect,
    onMouseDown,
    onMouseMove,
    onMouseUp
} = useCanvasInteractions({ props, emit })

// Para exponer getStage() al padre
defineExpose({ getStage: () => stageRef.value?.getStage() })

// — Separar asientos (“seat”) de shapes (“rect”|“circle”|“text”) —
const seatItems = computed(() =>
    props.seats.filter(s => !s.type || s.type === 'seat')
)
const shapes = computed(() =>
    props.seats.filter(s => ['rect', 'circle', 'text'].includes(s.type))
)

// — Handlers de shapes —
function onShapeClick(idx, e) {
    e.cancelBubble = true
    const updated = shapes.value.map((sh, i) =>
        i === idx
            ? { ...sh, selected: !sh.selected }
            : { ...sh, selected: false }
    )
    emit('update:seats', [...seatItems.value, ...updated])
}
function onShapeDragEnd(idx, e) {
    const { x, y } = e.target.getAttrs()
    const updated = shapes.value.map((sh, i) =>
        i === idx ? { ...sh, x, y } : sh
    )
    emit('update:seats', [...seatItems.value, ...updated])
}
function onShapeTextEdit(idx) {
    const txt = window.prompt('Editar texto:', shapes.value[idx].label)
    if (txt !== null) {
        const updated = shapes.value.map((sh, i) =>
            i === idx ? { ...sh, label: txt } : sh
        )
        emit('update:seats', [...seatItems.value, ...updated])
    }
}

// — Transformer para shapes —
const shapeTransformerNodes = computed(() =>
    shapeRefs.value
        .map((c, i) => c && shapes.value[i].selected ? c.getNode() : null)
        .filter(Boolean)
)
watch(shapeTransformerNodes, async nodes => {
    if (!nodes.length) return
    await nextTick()
    const tr = shapeTransformerRef.value.getNode()
    tr.nodes(nodes)
    tr.moveToTop()
    layerRef.value.getNode().batchDraw()
}, { immediate: true, flush: 'post' })
function onShapeTransformEnd() {
    const tr = shapeTransformerRef.value.getNode()
    const nodes = tr.nodes()
    if (nodes.length !== 1) return
    const node = nodes[0]
    const idx = shapeRefs.value.findIndex(c => c.getNode() === node)
    const shape = { ...shapes.value[idx] }
    if (shape.type === 'rect') {
        shape.width = node.width() * node.scaleX()
        shape.height = node.height() * node.scaleY()
    } else if (shape.type === 'circle') {
        shape.radius = node.radius() * node.scaleX()
    } else {
        shape.fontSize = shape.fontSize * node.scaleX()
    }
    shape.rotation = node.rotation()
    node.scale({ x: 1, y: 1 })
    const updated = shapes.value.map((sh, i) => i === idx ? shape : sh)
    emit('update:seats', [...seatItems.value, ...updated])
}

// — Transformer + drag grupal para seats —
const transformerNodes = computed(
    () => seatsLayerRef.value?.selectedCircleRefs.value || []
)
const bbox = computed(() => {
    const sel = seatItems.value.filter(s => s.selected)
    if (!sel.length) return { x: 0, y: 0, width: 0, height: 0 }
    const rs = sel.map(s => s.radius ?? defaultRadius)
    const xs = sel.map((s, i) => [s.x - rs[i], s.x + rs[i]]).flat()
    const ys = sel.map((s, i) => [s.y - rs[i], s.y + rs[i]]).flat()
    return {
        x: Math.min(...xs),
        y: Math.min(...ys),
        width: Math.max(...xs) - Math.min(...xs),
        height: Math.max(...ys) - Math.min(...ys),
    }
})
watch(transformerNodes, async nodes => {
    if (!nodes.length) return
    await nextTick()
    const t = transformerRef.value.getNode()
    t.nodes(nodes)
    t.moveToTop()
    layerRef.value.getNode().batchDraw()
}, { immediate: true, flush: 'post' })

let groupStartPos = null
let groupStartSeats = []
function handleGroupDragStart(e) {
    groupStartPos = { x: e.target.x(), y: e.target.y() }
    groupStartSeats = seatItems.value.map(s =>
        s ? { x: s.x, y: s.y, selected: s.selected } : { x: 0, y: 0, selected: false }
    )
}
function onGroupDragMove(e) {
    const { x, y } = e.target.getAttrs()
    const dx = x - groupStartPos.x
    const dy = y - groupStartPos.y
    const moved = seatItems.value.map((s, i) =>
        s.selected
            ? { ...s, x: groupStartSeats[i].x + dx, y: groupStartSeats[i].y + dy, selected: true }
            : s
    )
    emit('update:seats', [...moved, ...shapes.value])
}
function handleGroupDragEnd() {
    groupStartPos = null
    groupStartSeats = []
}

// — Cuando SeatsLayer emite update (drag individual) —
function onSeatsLayerUpdate(newSeats) {
    emit('update:seats', [...newSeats, ...shapes.value])
}

// — Click fuera del stage: deseleccionar todo —
function onStageClick(e) {
    if (didRectSelect.value) {
        didRectSelect.value = false
        return
    }
    const cls = e.target.getClassName?.()
    if (['Stage', 'Layer', 'Image'].includes(cls)) {
        emit('update:seats', props.seats.map(s => ({ ...s, selected: false })))
    }
}
</script>

<style scoped>
/* Aquí pon tus estilos extra si los necesitas */
</style>
