<template>
    <v-circle v-for="(seat, idx) in seats" :key="seat.id" :ref="el => setCircleEl(el, idx)" :config="{
        id: 'seat-' + seat.id,
        x: seat.x,
        y: seat.y,
        radius: seat.radius ?? defaultRadius,
        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
        stroke: (!seat.label || seat.label.trim() === '') ? 'red' : (seat.selected ? '#7c3aed' : '#a1a1aa'),
        strokeWidth: 2,
        draggable: true
    }" @mousedown="onToggleSeat(idx, $event)" @dragmove="onSeatDragMove(idx, $event)"
        @dragend="onSeatDragEnd(idx, $event)" v-on="!isBackend ? {
        mouseover: (e) => onCircleHover(idx, e),
        mouseout: onCircleOut
        } : {}" />


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

const props = defineProps({
    seats: { type: Array, required: true },
    defaultRadius: { type: Number, default: 22 },
    isBackend: { type: Boolean, default: false }
    // ...acá sumás cualquier otra prop que uses en el componente
})

const popupSeat = ref(null)
const popupPosition = ref({ x: 0, y: 0 })




const emit = defineEmits(['update:seats', 'edit-label', 'update:selection', 'show-popup'])


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
    // 1) Si es parte de una selección múltiple y clickeas un seleccionado sin Shift, dejamos que Konva arrastre.
    const selectedCount = props.seats.filter(s => s.selected).length
    const alreadySelected = props.seats[i].selected
    if (!event.shiftKey && alreadySelected && selectedCount > 1) {
        return
    }

    // 2) Cancelamos burbujeo para que no dispare el mousedown del fondo
    event.evt.cancelBubble = true

    // 3) Calculamos nuevo estado de selección
    let updated
    if (event.shiftKey) {
        updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, selected: !s.selected } : s
        )
    } else {
        updated = props.seats.map((s, idx) =>
            idx === i
                ? { ...s, selected: true }
                : { ...s, selected: false }
        )
    }

    // 4) Notificamos al padre
    emit('update:seats', updated)

    // 5) Disparamos popup **solo** si NO estamos en el backend
    const seat = props.seats[i]
    if ((!seat.type || seat.type === 'seat') && !props.isBackend) {
        const stage = event.target.getStage()
        const pointerPos = stage.getPointerPosition()
        emit('show-popup', {
            seat,
            position: { x: pointerPos.x, y: pointerPos.y }
        })
        // si usas este evento para editar labels…
        emit('edit-label', { seat, index: i })
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
    if (props.isBackend) return
    // mostramos el popup con los datos de ese asiento
    popupSeat.value = props.seats[i]   // Usá props.seats (no seats.value) en <script setup>
    // posicionamos justo donde está el mouse
    const { clientX: x, clientY: y } = e.evt
    popupPosition.value = { x, y }
}

function onCircleOut() {
    if (props.isBackend) return
    // ocultamos tan pronto sale el mouse
    popupSeat.value = null
}


watch(() => props.seats, (nuevo) => {
    //console.log('SEATS recibidos:', nuevo)
}, { immediate: true })



</script>
