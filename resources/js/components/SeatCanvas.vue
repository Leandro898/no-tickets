<template>
    <v-stage ref="stageRef" :config="{ width, height }" @wheel="onWheel" @mousedown="onMouseDown"
        @mousemove="onMouseMove" @mouseup="onMouseUp" @mouseleave="onMouseUp">
        <v-layer>
            <!-- Imagen de fondo -->
            <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />

            <!-- Cuadro de selección -->
            <v-rect v-if="selection.visible" :config="{
                x: selection.x,
                y: selection.y,
                width: selection.width,
                height: selection.height,
                fill: 'rgba(60, 120, 255, 0.2)',
                stroke: 'rgba(60, 120, 255, 0.5)',
                dash: [4, 4]
            }" />

            <!-- Asientos -->
            <v-circle v-for="(seat, i) in seats" :key="i" :config="{
                x: seat.x,
                y: seat.y,
                radius: 22,
                fill: seat.selected ? '#7c3aed' : '#e5e7eb',
                stroke: '#7c3aed',
                strokeWidth: 2,
                draggable: true
            }" @dragmove="e => onDrag(i, e)" @click="() => onToggle(i)" />
        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, watch, defineExpose } from 'vue'

// Props que vienen del componente padre
const props = defineProps({
    width: { type: Number, required: true },
    height: { type: Number, required: true },
    bgImage: { type: Object, default: null },
    seats: { type: Array, required: true },
    panMode: { type: Boolean, default: false },
})

// Eventos que emitimos al padre
const emit = defineEmits([
    'update:seats',
    'update:selection',
    'update:mapJSON',
])

// Ref al <v-stage>
const stageRef = ref(null)
// Exponemos getStage() para que el padre pueda invocarlo si lo necesita
defineExpose({
    getStage: () => stageRef.value.getStage()
})

// Estado interno de selección
const selection = ref({
    visible: false,
    x: 0, y: 0,
    width: 0, height: 0,
})

// Variables para pan y selección
let isPanning = false
let lastPointer = { x: 0, y: 0 }
let selectionStart = { x: 0, y: 0 }

// Cuando `selection` cambia, avisamos al padre
watch(selection, val => emit('update:selection', val))

// ————— Métodos de interacción ————— //

function onWheel(e) {
    e.evt.preventDefault()
    const stage = e.target.getStage()
    const oldScale = stage.scaleX()
    const pointer = stage.getPointerPosition()
    const scaleBy = 1.07
    const dir = e.evt.deltaY > 0 ? 1 : -1
    const newScale = dir > 0 ? oldScale / scaleBy : oldScale * scaleBy
    const limited = Math.max(0.3, Math.min(newScale, 3))
    const mp = {
        x: (pointer.x - stage.x()) / oldScale,
        y: (pointer.y - stage.y()) / oldScale
    }
    stage.scale({ x: limited, y: limited })
    stage.position({
        x: pointer.x - mp.x * limited,
        y: pointer.y - mp.y * limited
    })
    stage.batchDraw()
}

function onMouseDown(e) {
    // PAN con Space
    if (props.panMode) {
        isPanning = true
        lastPointer = e.target.getStage().getPointerPosition()
        return
    }
    // Si clic en un asiento, no iniciar selección de zona
    if (e.target.getClassName() === 'Circle') return

    // Clic en capa o fondo: inicia selección de caja
    if (
        e.target === e.target.getStage() ||
        ['Layer', 'Image'].includes(e.target.getClassName())
    ) {
        const p = e.target.getStage().getPointerPosition()
        selectionStart = { x: p.x, y: p.y }
        selection.value = {
            visible: true,
            x: p.x, y: p.y,
            width: 0, height: 0
        }
    }
}

function onMouseMove(e) {
    if (isPanning) {
        const st = e.target.getStage()
        const p = st.getPointerPosition()
        st.x(st.x() + (p.x - lastPointer.x))
        st.y(st.y() + (p.y - lastPointer.y))
        st.batchDraw()
        lastPointer = p
        return
    }
    // Actualizar caja de selección
    if (!selection.value.visible) return
    const p = e.target.getStage().getPointerPosition()
    selection.value = {
        visible: true,
        x: Math.min(p.x, selectionStart.x),
        y: Math.min(p.y, selectionStart.y),
        width: Math.abs(p.x - selectionStart.x),
        height: Math.abs(p.y - selectionStart.y),
    }
}

function onMouseUp() {
    // Terminar pan
    if (isPanning) {
        isPanning = false
        return
    }
    // Terminar selección de zona: marcar asientos dentro
    if (!selection.value.visible) return
    const sel = selection.value
    props.seats.forEach(s => {
        s.selected =
            s.x >= sel.x &&
            s.x <= sel.x + sel.width &&
            s.y >= sel.y &&
            s.y <= sel.y + sel.height
    })
    // Emitimos el array actualizado
    emit('update:seats', props.seats)
    // Ocultar caja
    selection.value.visible = false
}

function onDrag(i, e) {
    // Arrastrar un asiento: actualizar coords y emitir
    const pos = e.target.position()
    props.seats[i].x = pos.x
    props.seats[i].y = pos.y
    emit('update:seats', props.seats)
}

function onToggle(i) {
    // Clic sobre un asiento: togglear selección
    props.seats[i].selected = !props.seats[i].selected
    emit('update:seats', props.seats)
}

// Llamar a este método antes de guardar,
// para emitir el JSON serializado completo del stage al padre:
function exportMapJSON() {
    const json = stageRef.value.getStage().toJSON()
    emit('update:mapJSON', json)
}
</script>
