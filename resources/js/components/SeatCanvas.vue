<template>
    <v-stage ref="stageRef" :config="{ width, height }" @wheel="onWheel" @mousedown="onMouseDown"
        @mousemove="onMouseMove" @mouseup="onMouseUp" @mouseleave="onMouseUp">
        <v-layer ref="layerRef">
            <!-- Fondo -->
            <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />

            <!-- Caja de selección individual -->
            <v-rect v-if="selection.visible" :config="{
                x: selection.x,
                y: selection.y,
                width: selection.width,
                height: selection.height,
                fill: 'rgba(60,120,255,0.2)',
                stroke: 'rgba(60,120,255,0.5)',
                dash: [4, 4]
            }" />

            <!-- Asientos (círculos) -->
            <v-circle v-for="(seat, i) in seats" :key="i" :id="`seat-${i}`" :config="{
                x: seat.x,
                y: seat.y,
                radius: seat.radius ?? defaultRadius,
                fill: seat.selected ? '#7c3aed' : '#e5e7eb',
                stroke: '#7c3aed',
                strokeWidth: 2,
                draggable: true
            }" @dragmove="e => onDragMove(i, e)" @click="() => onToggle(i)" />

            <!-- 🟢 Transformer para resize de múltiples asientos y drag de grupo -->
            <v-transformer v-if="transformerNodes.length" ref="transformerRef" :config="{
                nodes: transformerNodes,
                draggable: false,
                rotateEnabled: false,
                enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right']
            }" @transformend="onTransformEnd" ></v-transformer>
        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, watch, nextTick, defineExpose } from 'vue'

/** 🚩 Flags para el drag de grupo */
let isGroupDragging = false
let lastGroupPointer = { x: 0, y: 0 }

/** — Props — */
const props = defineProps({
    width: { type: Number, required: true },
    height: { type: Number, required: true },
    bgImage: { type: Object, default: null },
    seats: { type: Array, required: true },   // [{ x, y, selected, radius? }]
    panMode: { type: Boolean, default: false }
})

/** — Emits — */
const emit = defineEmits([
    'update:seats',
    'update:selection',
    'update:mapJSON'
])

/** — Refs internos — */
const stageRef = ref(null)
const layerRef = ref(null)
const transformerRef = ref(null)
const selection = ref({
    visible: false,
    x: 0, y: 0,
    width: 0, height: 0
})

/** 🟢 Nodos para alimentar el transformer */
const transformerNodes = ref([])

/** — Exponer getStage() al padre para exportar JSON completo */
defineExpose({
    getStage: () => stageRef.value.getStage()
})

/** — Variables para pan y selección de caja — */
let isPanning = false
let lastPointer = { x: 0, y: 0 }
let selectionStart = { x: 0, y: 0 }

/** — Estilo por defecto de los asientos — */
const defaultRadius = 22

/** — Watchers — */
// Actualiza los nodos del transformer cuando cambian los seats seleccionados
watch(
    () => props.seats.map(s => s.selected),
    async () => {
        await nextTick()
        const layer = layerRef.value.getNode()
        transformerNodes.value = props.seats
            .map((s, i) => s.selected
                ? layer.findOne('#seat-' + i)
                : null
            )
            .filter(n => n)
    }
)

// Emite selección de caja al padre
watch(selection, val => emit('update:selection', val), { deep: true })


/** — Métodos de interacción — */

function onWheel(e) {
    e.evt.preventDefault()
    const stage = e.target.getStage()
    const oldScale = stage.scaleX()
    const pointer = stage.getPointerPosition()
    const factor = 1.07
    const dir = e.evt.deltaY > 0 ? 1 : -1
    const newScale = dir > 0 ? oldScale / factor : oldScale * factor
    const scale = Math.max(0.3, Math.min(newScale, 3))
    const mp = {
        x: (pointer.x - stage.x()) / oldScale,
        y: (pointer.y - stage.y()) / oldScale
    }
    stage.scale({ x: scale, y: scale })
    stage.position({
        x: pointer.x - mp.x * scale,
        y: pointer.y - mp.y * scale
    })
    stage.batchDraw()
}

function onMouseDown(e) {
    const stage = e.target.getStage()
    const pointer = stage.getPointerPosition()

    // ── 1) Si hay un transformer y el clic cae dentro de su bounding box…
    if (transformerRef.value) {
        const tf = transformerRef.value.getNode()
        const box = tf.getClientRect()              // { x, y, width, height }
        if (
            pointer.x >= box.x &&
            pointer.x <= box.x + box.width &&
            pointer.y >= box.y &&
            pointer.y <= box.y + box.height
        ) {
            isGroupDragging = true
            lastGroupPointer = pointer
            return
        }
    }

    // ── 2) Pan con Space
    if (props.panMode) {
        isPanning = true
        lastPointer = pointer
        return
    }

    // ── 3) Si clic en asiento individual, no iniciar caja
    const className = e.target.getClassName()
    if (className === 'Circle' && e.target.id().startsWith('seat-')) {
        return
    }

    // ── 4) Clic en fondo/capa → iniciar selección de caja
    if (
        e.target === stage ||
        ['Layer', 'Image'].includes(className)
    ) {
        const p = stage.getRelativePointerPosition()
        selectionStart = { x: p.x, y: p.y }
        selection.value = { visible: true, x: p.x, y: p.y, width: 0, height: 0 }
    }
}


function onMouseMove(e) {
    // 🚩 Drag de grupo en curso
    if (isGroupDragging) {
        const st = e.target.getStage()
        const p = st.getPointerPosition()
        const dx = p.x - lastGroupPointer.x
        const dy = p.y - lastGroupPointer.y

        props.seats.forEach(s => {
            if (s.selected) {
                s.x += dx
                s.y += dy
            }
        })

        lastGroupPointer = p
        emit('update:seats', props.seats)
        return
    }

    // Pan con Space
    if (isPanning) {
        const st = e.target.getStage()
        const p = st.getPointerPosition()
        st.x(st.x() + (p.x - lastPointer.x))
        st.y(st.y() + (p.y - lastPointer.y))
        st.batchDraw()
        lastPointer = p
        return
    }

    // Mover la caja de selección
    if (!selection.value.visible) return
    const st = e.target.getStage()
    const p = st.getRelativePointerPosition()
    selection.value = {
        visible: true,
        x: Math.min(p.x, selectionStart.x),
        y: Math.min(p.y, selectionStart.y),
        width: Math.abs(p.x - selectionStart.x),
        height: Math.abs(p.y - selectionStart.y)
    }
}

function onMouseUp() {
    // 🚩 Finalizar drag de grupo
    if (isGroupDragging) {
        isGroupDragging = false
        return
    }

    // Fin de pan
    if (isPanning) {
        isPanning = false
        return
    }

    // Finalizar selección de caja
    if (!selection.value.visible) return
    const sel = selection.value
    props.seats.forEach(s => {
        s.selected =
            s.x >= sel.x &&
            s.x <= sel.x + sel.width &&
            s.y >= sel.y &&
            s.y <= sel.y + sel.height
    })
    emit('update:seats', props.seats)
    selection.value.visible = false
}

function onDragMove(i, e) {
    const node = e.target
    const pos = node.position()
    const dx = pos.x - props.seats[i].x
    const dy = pos.y - props.seats[i].y

    if (!props.seats[i].selected) {
        // Solo ese asiento
        props.seats[i].x = pos.x
        props.seats[i].y = pos.y
    } else {
        // Mover todos los seleccionados
        props.seats.forEach(s => {
            if (s.selected) {
                s.x += dx
                s.y += dy
            }
        })
    }
    emit('update:seats', props.seats)
}

function onToggle(i) {
    props.seats[i].selected = !props.seats[i].selected
    emit('update:seats', props.seats)
}

function onTransformEnd() {
    // 🟢 Tras redimensionar: actualizar radius en el estado
    const tf = transformerRef.value.getNode()
    tf.nodes().forEach(node => {
        const idx = Number(node.id().split('-')[1])
        const scaleX = node.scaleX()
        props.seats[idx].radius = (props.seats[idx].radius ?? defaultRadius) * scaleX
        node.scaleX(1)
        node.scaleY(1)
    })
    emit('update:seats', props.seats)
}



function exportMapJSON() {
    const json = stageRef.value.getStage().toJSON()
    emit('update:mapJSON', json)
}
</script>

<style scoped>
/* No necesita estilos extra */
</style>
