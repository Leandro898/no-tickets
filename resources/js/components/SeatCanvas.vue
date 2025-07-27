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
                draggable: true,
                dragDistance: 4    // ya no arrastramos individualmente
            }" @click="onToggleSeat(i)" @dragstart="e => onSeatDragStart(i, e)" @dragend="e => onSeatDragEnd(i, e)" />

            <!-- Asientos (círculos) -->
            <v-text v-for="(seat, i) in seats" :key="'label-' + i" :config="{
                x: seats[i].x,
                // colocamos el texto ligeramente por encima del círculo:
                y: seats[i].y - (seats[i].radius ?? defaultRadius) - 6,
                text: seats[i].label,        // el label que definiste en SeatMap.vue
                fontSize: 14,
                fontFamily: 'Arial',
                align: 'center'
            }" />

            <!-- 🟢 Transformer para resize de múltiples asientos y drag de grupo -->
            <v-transformer v-if="transformerNodes.length" ref="transformerRef" :config="{
                nodes: transformerNodes,
                draggable: false,
                rotateEnabled: false,
                enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right']
            }" @transformend="onTransformEnd" @dragmove="onTransformerDrag" @dragend="onTransformerDragEnd" />

        </v-layer>
    </v-stage>
</template>

<script setup>
import { ref, watch, nextTick, defineExpose } from 'vue'

/** 🚩 Flags para el drag de grupo */
let isGroupDragging = false
let lastGroupPointer = { x: 0, y: 0 }
let isDragging = false;

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

/**  
 * 1) Si arrastras un asiento (incluso no seleccionado), 
 *    selecciona sólo ese asiento y arranca el drag  
 */
function onSeatDragStart(i, e) {
    // selecciona únicamente este asiento
    const updated = props.seats.map((s, idx) => ({
        ...s,
        selected: idx === i
    }))
    emit('update:seats', updated)

    // marcamos que estamos arrastrando
    isDragging = true
}

function onSeatDragEnd(i, e) {
    const { x, y } = e.target.position();
    const updated = props.seats.map((s, idx) =>
        idx === i ? { ...s, x, y } : s
    );
    isDragging = false;
    emit('update:seats', updated);
}
function onToggleSeat(i) {
    if (isDragging) return;
    const updated = props.seats.map((s, idx) =>
        idx === i ? { ...s, selected: !s.selected } : s
    );
    emit('update:seats', updated);
}

// 1) Helper para saber si el click cayó dentro del transformer
function isInsideTransformer(e) {
    if (!transformerRef.value) return false
    const tf = transformerRef.value.getNode()
    const box = tf.getClientRect()
    const pos = e.target.getStage().getPointerPosition()
    return (
        pos.x >= box.x &&
        pos.x <= box.x + box.width &&
        pos.y >= box.y &&
        pos.y <= box.y + box.height
    )
}

// 2) Handler para cuando termines de arrastrar el transformer
function onTransformerDragEnd() {
    isGroupDragging = false
}


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
    const stage = e.target.getStage();
    const pointer = stage.getPointerPosition();

    // ── 1) Si clic dentro del transformer → arranque de drag grupo
    if (transformerRef.value) {
        const tf = transformerRef.value.getNode();
        const box = tf.getClientRect();
        if (
            pointer.x >= box.x &&
            pointer.x <= box.x + box.width &&
            pointer.y >= box.y &&
            pointer.y <= box.y + box.height
        ) {
            isGroupDragging = true;
            lastGroupPointer = pointer;
            return;
        }
    }

    // ── 2) Pan con Space
    if (props.panMode) {
        isPanning = true;
        lastPointer = pointer;
        return;
    }

    // ── 3) Determinar si clic en asiento
    const className = e.target.getClassName();
    const isSeat =
        className === 'Circle' &&
        e.target.id().startsWith('seat-');

    // ☑️ Deseleccionar todo si NO clickeaste un asiento
    if (!isSeat) {
        const cleared = props.seats.map(s => ({
            ...s,
            selected: false
        }));
        emit('update:seats', cleared);
    }

    // ── 4) Si clic en asiento, salgo para permitir click/drag individual
    if (isSeat) {
        return;
    }

    // ── 5) Clic en fondo o capa → iniciar selección de caja
    if (
        e.target === stage ||
        ['Layer', 'Image'].includes(className)
    ) {
        const p = stage.getRelativePointerPosition();
        selectionStart = { x: p.x, y: p.y };
        selection.value = {
            visible: true,
            x: p.x,
            y: p.y,
            width: 0,
            height: 0
        };
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

// — Finalizar selección de caja —
function onMouseUp() {
    // 🚩 Si veníamos arrastrando en grupo, cancelamos ese drag
    if (isGroupDragging) {
        isGroupDragging = false
        return
    }
    // 🚩 Si veníamos haciendo pan con Space, cancelamos el pan
    if (isPanning) {
        isPanning = false
        return
    }
    // 🚩 Si no hay caja activa, nada que hacer
    if (!selection.value.visible) return

    // Procesamos la selección de caja
    const sel = selection.value
    const updated = props.seats.map(s => {
        const inside =
            s.x >= sel.x &&
            s.x <= sel.x + sel.width &&
            s.y >= sel.y &&
            s.y <= sel.y + sel.height
        return inside
            ? { ...s, selected: true }
            : s
    })

    // Emitimos el nuevo array con los selected actualizados
    emit('update:seats', updated)
    // Ocultamos la caja
    selection.value.visible = false
}


// También soportamos drag directo sobre el transformer
function onTransformerDrag(e) {
    const node = e.target
    const p = node.position()
    const dx = p.x - lastGroupPointer.x
    const dy = p.y - lastGroupPointer.y

    const updated = props.seats.map(s =>
        s.selected
            ? { ...s, x: s.x + dx, y: s.y + dy }
            : s
    )
    lastGroupPointer = { x: p.x, y: p.y }
    emit('update:seats', updated)
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
//// Alternar selección de un asiento
// — Alternar selección individual —
function onToggle(i) {
    console.log('click en asiento', i)
    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, selected: !s.selected }
            : s
    )
    emit('update:seats', updated)
}

function onTransformEnd() {
    const tf = transformerRef.value.getNode()
    const updated = props.seats.map((seat, i) => {
        const node = tf.nodes().find(n => Number(n.id().split('-')[1]) === i)
        if (!node) return seat
        const newRadius = (seat.radius ?? defaultRadius) * node.scaleX()
        node.scaleX(1)
        node.scaleY(1)
        return { ...seat, radius: newRadius }
    })

    emit('update:seats', updated)
}

function exportMapJSON() {
    const json = stageRef.value.getStage().toJSON()
    emit('update:mapJSON', json)
}





</script>

<style scoped>
/* No necesita estilos extra */
</style>
