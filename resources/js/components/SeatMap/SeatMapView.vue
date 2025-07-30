<template>
    <v-stage :config="{ width, height }" @mousedown="onStageMouseDown" @mousemove="onStageMouseMove"
        @mouseup="onStageMouseUp">
        <v-layer ref="layerRef">
            <!-- 1) Fondo -->
            <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />

            <!-- 2) SelectionBox para drag‑select -->
            <SelectionBox v-model="selectionBox" />

            <!-- 3) Shapes -->
            <template v-for="(s, i) in shapes" :key="i">
                <component :is="shapeTag(s)" :config="shapeConfig(s, i)" :id="'shape-' + i"
                    :ref="el => shapeRefs[i] = el" @mousedown="onShapeMouseDown(i, $event)"
                    @dragend="onShapeDragEnd(i, $event)" @transformend.native="onShapeTransformEnd(i, $event)" />
            </template>

            <!-- 4) Capa de asientos -->
            <SeatsLayer ref="seatsLayerRef" :seats="seats" :defaultRadius="22" @update:seats="onSeatsUpdate"
                @update:selection="onSeatSelection" />

            <!-- 5) Transformer único -->
            <v-transformer ref="transformerRef" />
        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import SelectionBox from './SeatCanvas/SelectionBox.vue'
import SeatsLayer from './SeatCanvas/SeatsLayer.vue'

const props = defineProps({
    width: Number,
    height: Number,
    bgImage: Object,
    seats: Array,
    shapes: Array
})
const emit = defineEmits(['update:seats', 'update:shapes'])

// refs a Stage/Layer/Transformer y SeatsLayer
const layerRef = ref(null)
const transformerRef = ref(null)
const seatsLayerRef = ref(null)

// refs para cada shape
const shapeRefs = ref([])

// selección por drag
const selectionBox = ref({ visible: false, x: 0, y: 0, width: 0, height: 0 })
let startPos = { x: 0, y: 0 }
const isDragging = ref(false)

// ——————————————
// 1) Helpers Stage: selección en recuadro
// ——————————————
function onStageMouseDown(e) {
    if (e.target === e.target.getStage()) {
        isDragging.value = true
        clearTransformer()
        const p = e.target.getStage().getPointerPosition()
        startPos = { ...p }
        selectionBox.value = { visible: true, x: p.x, y: p.y, width: 0, height: 0 }
    }
}
function onStageMouseMove(e) {
    if (!isDragging.value) return
    const p = e.target.getStage().getPointerPosition()
    selectionBox.value.width = p.x - startPos.x
    selectionBox.value.height = p.y - startPos.y
}
function onStageMouseUp(e) {
    if (!isDragging.value) return
    isDragging.value = false
    selectionBox.value.visible = false
    selectInBox(selectionBox.value)
}

function selectInBox({ x, y, width, height }) {
    const minX = Math.min(x, x + width)
    const maxX = Math.max(x, x + width)
    const minY = Math.min(y, y + height)
    const maxY = Math.max(y, y + height)

    // seats
    props.seats.forEach(seat => {
        seat.selected =
            seat.x >= minX && seat.x <= maxX &&
            seat.y >= minY && seat.y <= maxY
    })
    emit('update:seats', props.seats)

    // shapes
    props.shapes.forEach(sh => {
        let sel = false
        if (sh.type === 'rect') {
            sel = sh.x + sh.width >= minX && sh.x <= maxX && sh.y + sh.height >= minY && sh.y <= maxY
        }
        else if (sh.type === 'circle') {
            sel = sh.x >= minX && sh.x <= maxX && sh.y >= minY && sh.y <= maxY
        }
        else /* text */ {
            sel = sh.x >= minX && sh.x <= maxX && sh.y >= minY && sh.y <= maxY
        }
        sh.selected = sel
    })
    emit('update:shapes', props.shapes)
}

function clearTransformer() {
    const tr = transformerRef.value.getNode()
    tr.nodes([])
    layerRef.value.getNode().batchDraw()
}

// ——————————————
// 2) Configuración de tags y props de shapes
// ——————————————
function shapeTag(s) {
    return s.type === 'rect' ? 'v-rect'
        : s.type === 'circle' ? 'v-circle'
            : 'v-text'
}
function shapeConfig(s, i) {
    const sel = !!s.selected
    return {
        x: s.x, y: s.y,
        ...(s.type === 'rect'
            ? { width: s.width, height: s.height }
            : s.type === 'circle'
                ? { radius: s.radius ?? (s.width / 2) }
                : { text: s.label, fontSize: s.fontSize }
        ),
        stroke: sel ? 'blue' : 'gray',
        strokeWidth: 2,
        draggable: true
    }
}

// ——————————————
// 3) Handlers de shapes
// ——————————————
async function onShapeMouseDown(i, e) {
    e.cancelBubble = true
    // toggle o selección simple
    const updated = props.shapes.map((sh, idx) => ({
        ...sh,
        selected: e.shiftKey
            ? (idx === i ? !sh.selected : sh.selected)
            : (idx === i)
    }))
    // limpio seats
    props.seats.forEach(s => s.selected = false)
    emit('update:seats', props.seats)
    emit('update:shapes', updated)

    // engancho transformer sólo a este nodo
    await nextTick()
    const node = shapeRefs.value[i]?.getNode?.()
    if (node) {
        const tr = transformerRef.value.getNode()
        tr.nodes([node])
        tr.moveToTop()
        layerRef.value.getNode().batchDraw()
    }
}
function onShapeDragEnd(i, e) {
    const { x, y } = e.target.position()
    const updated = props.shapes.map((sh, idx) =>
        idx === i ? { ...sh, x, y, selected: sh.selected } : sh
    )
    emit('update:shapes', updated)
}
function onShapeTransformEnd(i, evt) {
    const node = evt.target
    const orig = props.shapes[i]
    const copy = { ...orig }
    if (orig.type === 'rect') {
        copy.width = node.width() * node.scaleX()
        copy.height = node.height() * node.scaleY()
    }
    if (orig.type === 'circle') {
        copy.radius = node.radius() * node.scaleX()
    }
    if (orig.type === 'text') {
        copy.fontSize = orig.fontSize * node.scaleX()
    }
    copy.rotation = node.rotation()
    node.scale({ x: 1, y: 1 })

    const updated = props.shapes.map((sh, idx) =>
        idx === i
            ? { ...copy, selected: true }
            : { ...sh, selected: false }
    )
    props.seats.forEach(s => s.selected = false)
    emit('update:seats', props.seats)
    emit('update:shapes', updated)
}

// ——————————————
// 4) Handlers de SeatsLayer
// ——————————————
function onSeatsUpdate(ns) {
    emit('update:seats', ns)
}
async function onSeatSelection() {
    await nextTick()
    const layer = layerRef.value.getNode()
    const tr = transformerRef.value.getNode()

    const shapeNodes = props.shapes
        .map((sh, i) => sh.selected ? layer.findOne('#shape-' + i) : null)
        .filter(Boolean)

    // Aquí también **sin** .value
    const seatNodes = seatsLayerRef.value.selectedCircleRefs || []



    tr.nodes([...shapeNodes, ...seatNodes])
    tr.moveToTop()
    layer.batchDraw()
}


// ——————————————
// 5) Watch global para shapes.selected O seats.selected
// ——————————————
watch(
    [
        () => props.shapes.map(s => s.selected),
        () => props.seats.map(s => s.selected)
    ],
    async () => {
        await nextTick()
        const layer = layerRef.value.getNode()
        const tr = transformerRef.value.getNode()

        // Shape nodes
        const shapeNodes = props.shapes
            .map((_, i) =>
                props.shapes[i].selected
                    ? layer.findOne('#shape-' + i)
                    : null)
            .filter(Boolean)

        // Seat nodes: **sin**.value
        const seatNodes = seatsLayerRef.value.selectedCircleRefs || []

        tr.nodes([...shapeNodes, ...seatNodes])
        tr.moveToTop()
        layer.batchDraw()
    },
    { immediate: true, flush: 'post' }
)

</script>
