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
                    <!-- Rectángulo -->
                    <v-rect v-if="shape.type === 'rect'" :key="'rect-' + i" :ref="el => shapeRefs[i] = el" :config="{
                        x: shape.x,
                        y: shape.y,
                        width: shape.width,
                        height: shape.height,
                        stroke: shape.stroke || 'gray',
                        strokeWidth: shape.strokeWidth || 2,
                        draggable: true,
                        rotation: shape.rotation || 0
                    }" @dragend="onShapeDragEnd(i, $event)" @click="onShapeClick(i, $event)" />
                    <!-- Círculo -->
                    <v-circle v-else-if="shape.type === 'circle'" :key="'circle-' + i" :ref="el => shapeRefs[i] = el"
                        :config="{
                            x: shape.x,
                            y: shape.y,
                            radius: shape.radius,
                            stroke: shape.stroke || 'gray',
                            strokeWidth: shape.strokeWidth || 2,
                            draggable: true,
                            rotation: shape.rotation || 0
                        }" @dragend="onShapeDragEnd(i, $event)" @click="onShapeClick(i, $event)" />
                    <!-- Texto editable -->
                    <v-text v-else-if="shape.type === 'text'" :key="'text-' + i" :ref="el => shapeRefs[i] = el" :config="{
                        x: shape.x,
                        y: shape.y,
                        text: shape.label,
                        fontSize: shape.fontSize || 18,
                        draggable: true,
                        rotation: shape.rotation || 0
                    }" @dragend="onShapeDragEnd(i, $event)" @dblclick="onShapeTextEdit(i)" @click="onShapeClick(i, $event)" />
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
                    @update:seats="onSeatsLayerUpdate"  />

                <LabelsLayer :seats="seatItems" :defaultRadius="defaultRadius" />

                <!-- 6) Transformer de grupo de asientos -->
                <v-rect v-if="transformerNodes.length" :x="bbox.x" :y="bbox.y" :width="bbox.width" :height="bbox.height"
                    fill="#ffffff" :opacity="0.001" :draggable="true" :listening="true" :strokeWidth="0"
                    @mouseover="onGroupMouseOver" @mouseout="onGroupMouseOut" @dragstart="handleGroupDragStart"
                    @dragmove="onGroupDragMove" @dragend="handleGroupDragEnd" />
                <v-transformer v-if="transformerNodes.length" ref="transformerRef" :nodes="transformerNodes"
                    :config="{ enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'] }" />
            </v-layer>
        </v-stage>
    </div>
</template>


<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useCanvasInteractions } from '@/composables/useCanvasInteractions'
import BackgroundImage from './BackgroundImage.vue'
import SelectionBox from './SelectionBox.vue'
import SeatsLayer from './SeatsLayer.vue'
import LabelsLayer from './LabelsLayer.vue'
import SeatControls from '../SeatControls.vue'

// Para manejar la seleccion con tecla shift
//Esto detecta si se está presionando Shift para seleccionar múltiples asientos
const isShiftPressed = ref(false)
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Shift') isShiftPressed.value = true
    })
    window.addEventListener('keyup', (e) => {
        if (e.key === 'Shift') isShiftPressed.value = false
    })
}
// — Props y emits —
const props = defineProps({
    width: { type: Number, required: true },
    height: { type: Number, required: true },
    bgImage: { type: Object, default: null },
    seats: { type: Array, required: true },  // mezcla seats + shapes
    panMode: { type: Boolean, default: false },
})
const emit = defineEmits(['update:seats', 'update:mapJSON', 'update:selection'])

// — Refs a Stage/Layer/Transformers y SeatsLayer —
const stageRef = ref(null)
const layerRef = ref(null)
const transformerRef = ref(null)
const seatsLayerRef = ref(null)
const shapeTransformerRef = ref(null)
const shapeRefs = ref([])  // refs para cada shape

// — Panning y selección de asientos —
const {
    selection,
    defaultRadius,
    didRectSelect,
    onMouseDown,
    onMouseMove,
    onMouseUp
} = useCanvasInteractions({ props, emit })

// — Permitir al padre acceder a getStage() —
defineExpose({ getStage: () => stageRef.value?.getStage() })

// — Separar asientos (“seat”) de shapes (“rect”|“circle”|“text”) —
const seatItems = computed(() =>
    props.seats.filter(s => s && (!s.type || s.type === 'seat'))
)
const shapes = computed(() =>
    props.seats.filter(s => s && (s.type === 'rect' || s.type === 'circle' || s.type === 'text'))
)

// — Cuando SeatsLayer emite update, preservamos shapes intactos —
function onSeatsLayerUpdate(newSeats) {
    emit('update:seats', [...newSeats, ...shapes.value])
}

// — Click en shape para togglear selección —
function onShapeClick(idx, e) {
    e.cancelBubble = true

    let updated

    if (isShiftPressed.value) {
        // Selección múltiple con Shift
        updated = shapes.value.map((sh, i) =>
            i === idx ? { ...sh, selected: !sh.selected } : sh
        )
    } else {
        // Selección simple, deselecciona todos los demás
        updated = shapes.value.map((sh, i) =>
            i === idx ? { ...sh, selected: true } : { ...sh, selected: false }
        )
        // Además, deselecciona todos los asientos
        emit('update:seats', [
            ...seatItems.value.map(s => ({ ...s, selected: false })),
            ...updated
        ])
        return
    }

    emit('update:seats', [...seatItems.value, ...updated])
}



// — Drag de shapes: actualizar x,y —
function onShapeDragEnd(idx, e) {
    const { x, y } = e.target.getAttrs()
    const updated = shapes.value.map((sh, i) =>
        i === idx ? { ...sh, x, y } : sh
    )
    emit('update:seats', [...seatItems.value, ...updated])
}

// — Doble‑clic en texto para editar label —
function onShapeTextEdit(idx) {
    const txt = window.prompt('Editar texto:', shapes.value[idx].label)
    if (txt !== null) {
        const updated = shapes.value.map((sh, i) =>
            i === idx ? { ...sh, label: txt } : sh
        )
        emit('update:seats', [...seatItems.value, ...updated])
    }
}

// — Transformer para shapes: nodos seleccionados —
const shapeTransformerNodes = computed(() =>
    shapeRefs.value
        .map((c, i) => (c && shapes.value[i] && shapes.value[i].selected ? c.getNode() : null))
        .filter(Boolean)
)

// — Enganchar transformer de shapes al cambiar selección —
watch(shapeTransformerNodes, async nodes => {
    if (!nodes.length) return
    await nextTick()
    const tr = shapeTransformerRef.value.getNode()
    tr.nodes(nodes)
    tr.moveToTop()
    layerRef.value.getNode().batchDraw()
}, { immediate: true, flush: 'post' })

// — Al terminar transform de shape, guardo nuevos dims —
function onShapeTransformEnd() {
    const tr = shapeTransformerRef.value.getNode()
    const nodes = tr.nodes()
    if (nodes.length !== 1) return
    const node = nodes[0]
    // encontrar índice
    const idx = shapeRefs.value.findIndex(c => c.getNode() === node)
    const shape = { ...shapes.value[idx] }

    if (shape.type === 'rect') {
        shape.width = node.width() * node.scaleX()
        shape.height = node.height() * node.scaleY()
    } else if (shape.type === 'circle') {
        shape.radius = node.radius() * node.scaleX()
    } else if (shape.type === 'text') {
        shape.fontSize = shape.fontSize * node.scaleX()
    }

    // 🔥 Agregar siempre la rotación para todos los tipos
    shape.rotation = node.rotation()

    // reset del scale para no acumular
    node.scale({ x: 1, y: 1 })

    const updated = shapes.value.map((sh, i) => i === idx ? shape : sh)
    emit('update:seats', [...seatItems.value, ...updated])
}


// — Lógica Transformer + drag grupal para seats (igual que antes) —
const transformerNodes = computed(
    () => seatsLayerRef.value?.selectedCircleRefs || []
)

const bbox = computed(() => {
    const sel = seatItems.value.filter(s => s && s.selected)
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
    groupStartSeats = seatItems.value.map(s => s ? { x: s.x, y: s.y, selected: s.selected } : { x: 0, y: 0, selected: false })
    onGroupMouseOver()
}
function onGroupDragMove(e) {
    const { x, y } = e.target.getAttrs()
    const dx = x - groupStartPos.x
    const dy = y - groupStartPos.y
    const moved = seatItems.value.map((s, i) =>
        s && s.selected
            ? { ...s, x: groupStartSeats[i].x + dx, y: groupStartSeats[i].y + dy, selected: true }
            : s
    )
    emit('update:seats', [...moved, ...shapes.value])
}
function handleGroupDragEnd() {
    groupStartPos = null
    groupStartSeats = []
    onGroupMouseOut()
}

function onGroupMouseOver() {
    const st = stageRef.value?.getStage()
    if (st) st.container().style.cursor = 'move'
}
function onGroupMouseOut() {
    const st = stageRef.value?.getStage()
    if (st) st.container().style.cursor = 'default'
}

// — Click fuera: deseleccionar todo (seats y shapes) —
function onStageClick(e) {
    // Si acabamos de hacer selección por recuadro, lo limpiamos y salimos:
    if (didRectSelect.value) {
        didRectSelect.value = false
        return
    }

    // Sólo deseleccionar si el click fue en el Stage o en la Layer (fondo),
    // no en ningún Circle, Rect, Text, etc.
    const cls = e.target.getClassName?.()
    console.log('Click detectado en:', cls) // 👈🏼 LOG acá

    if (cls === 'Stage' || cls === 'Layer' || cls === 'Image') {
        console.log('Deseleccionando todos')
        emit('update:seats', props.seats.map(s => s ? { ...s, selected: false } : s))
    }

}

</script>

<style scoped>
/* Cursores o estilos extra si los necesitas */
</style>
