<template>
    <v-stage :config="{ width, height }" @mousedown="onStageMouseDown" @mousemove="onStageMouseMove"
        @mouseup="onStageMouseUp">
        <v-layer ref="layerRef">
            <!-- Fondo -->
            <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />

            <!-- SelectionBox -->
            <SelectionBox v-model="selectionBox" />

            <!-- Shapes -->
            <template v-for="(s, i) in shapes" :key="'shape-' + i">
                <component :is="s.type === 'rect' ? 'v-rect' : s.type === 'circle' ? 'v-circle' : 'v-text'"
                    :config="getShapeConfig(s, i)" :id="'shape-' + i" :draggable="true" @mousedown="selectShape(i)"
                    @dragend="onShapeDragEnd(i, $event)" />
            </template>

            <!-- Asientos -->
            <template v-for="(seat, i) in seats" :key="'seat-' + i">
                <v-circle :config="getSeatConfig(seat, i)" :id="'seat-' + i" :draggable="true"
                    @mousedown="selectSeat(i)" @dragend="onSeatDragEnd(i, $event)" />
                <v-text :config="getLabelConfig(seat)" />
            </template>

            <!-- Transformer -->
            <v-transformer ref="transformerRef" />
        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import SelectionBox from './SeatCanvas/SelectionBox.vue' // Ajusta la ruta si hace falta

const props = defineProps({
    width: { type: Number, default: 1000 },
    height: { type: Number, default: 800 },
    bgImage: { type: Object, default: null },
    shapes: { type: Array, default: () => [] },
    seats: { type: Array, default: () => [] },
})

// Selección individual
const selected = ref([])
const layerRef = ref(null)
const transformerRef = ref(null)

// ---- SELECTION BOX MULTIPLE ----
const selectionBox = ref({
    visible: false,
    x: 0,
    y: 0,
    width: 0,
    height: 0,
})
let isSelecting = false
let startPos = { x: 0, y: 0 }

// --- Eventos para el SelectionBox ---
function onStageMouseDown(e) {
    // Solo activar selection box si se clickea el fondo (no sobre asiento/shape)
    if (e.target === e.target.getStage()) {
        selected.value = []
        isSelecting = true
        const pos = e.target.getStage().getPointerPosition()
        startPos = { ...pos }
        selectionBox.value = {
            visible: true,
            x: pos.x,
            y: pos.y,
            width: 0,
            height: 0,
        }
    }
}
function onStageMouseMove(e) {
    if (!isSelecting) return
    const pos = e.target.getStage().getPointerPosition()
    selectionBox.value.width = pos.x - startPos.x
    selectionBox.value.height = pos.y - startPos.y
}
function onStageMouseUp(e) {
    if (!isSelecting) return
    isSelecting = false
    selectionBox.value.visible = false

    selectElementsInRect(selectionBox.value) // <- Ahora selecciona ambos
}

// ---- Selección múltiple tanto de asientos como shapes ----
function selectElementsInRect(rect) {
    const minX = Math.min(rect.x, rect.x + rect.width)
    const maxX = Math.max(rect.x, rect.x + rect.width)
    const minY = Math.min(rect.y, rect.y + rect.height)
    const maxY = Math.max(rect.y, rect.y + rect.height)

    // Limpiá antes de seleccionar
    selected.value = []

    // Seats
    props.seats.forEach((seat, i) => {
        seat.selected = (
            seat.x >= minX && seat.x <= maxX &&
            seat.y >= minY && seat.y <= maxY
        )
        if (seat.selected) selected.value.push({ type: 'seat', idx: i })
    })

    // Shapes
    props.shapes.forEach((shape, i) => {
        let isSel = false
        if (shape.type === 'circle') {
            isSel = (
                shape.x >= minX && shape.x <= maxX &&
                shape.y >= minY && shape.y <= maxY
            )
        } else if (shape.type === 'rect') {
            isSel = (
                shape.x + shape.width >= minX && shape.x <= maxX &&
                shape.y + shape.height >= minY && shape.y <= maxY
            )
        } else if (shape.type === 'text') {
            isSel = (
                shape.x >= minX && shape.x <= maxX &&
                shape.y >= minY && shape.y <= maxY
            )
        }
        shape.selected = isSel
        if (isSel) selected.value.push({ type: 'shape', idx: i })
    })
}

// ---- Configuración visual de shapes/asientos/labels - cambia de color si se selecciona shape ----
function getShapeConfig(s, i) {
    const isSelected = selected.value.some(sel => sel.type === 'shape' && sel.idx === i)
    if (s.type === 'rect')
        return {
            x: s.x, y: s.y, width: s.width, height: s.height,
            stroke: isSelected ? 'blue' : 'gray',
            strokeWidth: 2,
            draggable: true,
        }
    if (s.type === 'circle')
        return {
            x: s.x, y: s.y, radius: s.width / 2 || 30,
            stroke: isSelected ? 'blue' : 'gray',
            strokeWidth: 2,
            draggable: true,
        }
    // text
    return {
        x: s.x, y: s.y, text: s.label, fontSize: s.fontSize,
        fill: isSelected ? 'blue' : 'black',
        draggable: true,
    }
}

function getSeatConfig(seat, i) {
    const isSelected = selected.value.some(sel => sel.type === 'seat' && sel.idx === i)
    return {
        x: seat.x, y: seat.y, radius: seat.radius || 22,
        fill: isSelected ? '#fac' : '#72d759',
        stroke: '#555', strokeWidth: 2,
        draggable: true,
    }
}

function getLabelConfig(seat, i) {
    const isSelected = selected.value.some(sel => sel.type === 'seat' && sel.idx === i)
    return {
        x: seat.x,
        y: seat.y + (seat.radius || 22) + 14,
        text: seat.label,
        fontSize: seat.fontSize || 16,
        fill: isSelected ? '#1976d2' : '#222', // azul si está seleccionado, negro si no
        align: 'center'
    }
}


// --- Selección individual (sigue funcionando)
function selectShape(i) {
    selected.value = [{ type: 'shape', idx: i }]
}
function selectSeat(i) {
    selected.value = [{ type: 'seat', idx: i }]
}

// --- Drag update (puedes emitir eventos si lo deseas)
function onShapeDragEnd(i, e) {
    props.shapes[i].x = e.target.x()
    props.shapes[i].y = e.target.y()
}
function onSeatDragEnd(i, e) {
    props.seats[i].x = e.target.x()
    props.seats[i].y = e.target.y()
}

// --- Transformer (para shape/seat seleccionado)
watch(selected, async () => {
    await nextTick()
    const layer = layerRef.value.getNode()
    const transformer = transformerRef.value.getNode()
    const nodes = []

    // Agregá todos los nodos seleccionados (seats y shapes)
    selected.value.forEach(sel => {
        if (sel.type === 'seat') {
            const node = layer.findOne('#seat-' + sel.idx)
            if (node) nodes.push(node)
        }
        if (sel.type === 'shape') {
            const node = layer.findOne('#shape-' + sel.idx)
            if (node) nodes.push(node)
        }
    })
    transformer.nodes(nodes)
    layer.batchDraw()
})
</script>
