<template>
    <v-circle v-for="(seat, idx) in seats" :key="seat.id" :config="{
        id: 'seat-' + seat.id,
        x: seat.x,
        y: seat.y,
        radius: seat.radius,
        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
        stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
        strokeWidth: 2
    }" @click="toggle(idx, $event)" @mouseover="onCircleHover(idx, $event)" @mouseout="onCircleOut" />

    <v-text v-for="seat in seats" :key="'label-' + seat.id" :config="{
        x: seat.x,
        y: seat.y + (seat.radius ?? 22) + 12,
        text: seat.label || '',     // <-- SIEMPRE ESTO!
        fontSize: 15,
        fill: '#6366f1',
        align: 'center',
        fontStyle: 'bold',
    }" />

</template>

<script setup>
import { ref, watch, nextTick, computed, defineExpose } from 'vue'



const emit = defineEmits(['update:seats', 'edit-label', 'update:selection'])
const props = defineProps({
    seats: { type: Array, required: true },
    defaultRadius: { type: Number, default: 22 }
})

const circleEls = ref([])
const selectedCircleRefs = ref([])
defineExpose({ circleEls, selectedCircleRefs })

function setCircleEl(el, idx) {
    if (!Array.isArray(circleEls.value)) circleEls.value = []
    circleEls.value[idx] = el
}

const validSeats = computed(() =>
    props.seats
        .map((s, idx) => ({ seat: s, originalIndex: idx }))
        .filter(({ seat }) =>
            seat &&
            (!seat.type || seat.type === 'seat') &&
            typeof seat.x === 'number' &&
            typeof seat.y === 'number'
        )
)

watch(
    () => props.seats.map(s => s.selected),
    async () => {
        await nextTick()
        selectedCircleRefs.value = props.seats
            .map((s, idx) =>
                s.selected && circleEls.value[idx]?.getNode
                    ? circleEls.value[idx].getNode()
                    : null
            )
            .filter(Boolean)
        emit(
            'update:selection',
            props.seats.map((s, idx) => s.selected ? { type: 'seat', idx } : null).filter(Boolean)
        )
    },
    { immediate: true }
)

// --------- ARRANCA DRAG GRUPAL ---------
let groupDragStart = null

function onSeatDragMove(i, e) {
    const { x, y } = e.target.position()
    const selectedCount = props.seats.filter(s => s.selected).length

    if (selectedCount <= 1) {
        // arrastre individual en vivo
        const updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, x, y, selected: true } : s
        )
        emit('update:seats', updated)
        return
    }

    // arrastre grupal
    if (!groupDragStart) {
        groupDragStart = {
            startPositions: props.seats.map(s => ({ x: s.x, y: s.y }))
        }
    }
    const prev = groupDragStart.startPositions[i]
    const dx = x - prev.x
    const dy = y - prev.y

    const updated = props.seats.map((s, idx) =>
        s.selected
            ? {
                ...s,
                x: groupDragStart.startPositions[idx].x + dx,
                y: groupDragStart.startPositions[idx].y + dy
            }
            : s
    )
    emit('update:seats', updated)
}



function onSeatDragEnd(i, e) {
    const selectedCount = props.seats.filter(s => s.selected).length
    if (selectedCount > 1 && groupDragStart) {
        // ya fijamos todo en dragmove
        groupDragStart = null
    } else {
        // arrastre individual: nada más que resetear groupDragStart
        groupDragStart = null
    }
}




function onToggleSeat(i, event) {
    console.log('CLICK FRONT', i)
    const selectedCount = props.seats.filter(s => s.selected).length
    const alreadySelected = props.seats[i].selected

    // Si estoy clickeando un asiento ya seleccionado y formo parte
    // de una selección múltiple, que pase el evento al drag:
    if (!event.shiftKey && alreadySelected && selectedCount > 1) {
        // no cancelBubble, no emit de selección
        return
    }
    // en cualquier otro caso, quiero interceptar el clic para cambiar selección:
    event.evt.cancelBubble = true

    // Lógica normal de selección:
    const seat = props.seats[i]
    let updated

    if (event.shiftKey) {
        // Shift+click: alterna
        updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, selected: !s.selected } : s
        )
    } else {
        // Click normal: solo este
        updated = props.seats.map((s, idx) =>
            idx === i
                ? { ...s, selected: true }
                : { ...s, selected: false }
        )
    }

    emit('update:seats', updated)

    // --- POPUP tipo Ticketmaster ---
    // Solo mostrar popup si es un asiento (no sección u otra cosa)
    if (!seat.type || seat.type === 'seat') {
        // Obtener la posición del mouse en el canvas de Konva
        const stage = event.target.getStage()
        const pointerPos = stage.getPointerPosition()
        // Emitir evento para que el padre muestre el popup
        console.log('[SeatsLayer] Emitiendo show-popup', seat, pointerPos)
        emit('show-popup', { seat, position: { x: pointerPos.x, y: pointerPos.y } })

        emit('edit-label', { seat, index: i }) // (si lo usás para edición)
    }
}





function onCircleTransformEnd(i, evt) {
    const shape = evt.target
    const newR = (shape.radius() || props.defaultRadius) * shape.scaleX()
    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, radius: newR, selected: true }
            : s
    )
    emit('update:seats', updated)
    shape.scaleX(1)
    shape.scaleY(1)
}

function onCircleHover(i, e) {
    // mostramos el popup con los datos de ese asiento
    popupSeat.value = seats.value[i]
    // posicionamos justo donde está el mouse
    const { clientX: x, clientY: y } = e.evt
    popupPosition.value = { x, y }
}

function onCircleOut() {
    // ocultamos tan pronto sale el mouse
    popupSeat.value = null
}



</script>
