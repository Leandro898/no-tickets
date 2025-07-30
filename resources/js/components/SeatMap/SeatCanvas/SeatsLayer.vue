<template>
    <v-circle v-for="({ seat, originalIndex }) in validSeats" :key="originalIndex" :config="{
        x: seat.x,
        y: seat.y,
        radius: seat.radius ?? defaultRadius,
        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
        stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
        strokeWidth: seat.selected ? 4 : 2,
        dragDistance: 5,
        draggable: true
    }" :ref="el => setCircleEl(el, originalIndex)" @mousedown="onToggleSeat(originalIndex, $event)"
        @dragend="onSeatDragEnd(originalIndex, $event)"
        @transformend.native="onCircleTransformEnd(originalIndex, $event)" />
</template>

<script setup>
import { ref, watch, nextTick, computed, defineExpose } from 'vue'

const emit = defineEmits(['update:seats', 'edit-label', 'update:selection'])
const props = defineProps({
    seats: { type: Array, required: true },
    defaultRadius: { type: Number, default: 22 }
})

// --- Array de refs a los círculos
const circleEls = ref([])
// --- Array de nodos Circle seleccionados
const selectedCircleRefs = ref([]) // array de NODOS, no de objetos ni proxies
defineExpose({ circleEls, selectedCircleRefs });

function setCircleEl(el, idx) {
    // Por si el array no tiene la longitud correcta (poco probable, pero seguro)
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

// Este watch SIEMPRE pone selectedCircleRefs.value como ARRAY REAL de nodos
watch(
    () => props.seats.map(s => s.selected),
    async () => {
        await nextTick();
        // Esto DEBE generar siempre un array, aunque sea vacío
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
        //console.log('selectedCircleRefs.value (en hijo):', selectedCircleRefs.value)
    },
    { immediate: true }
)

//console.log('selectedCircleRefs en SeatsLayer.vue:', selectedCircleRefs.value)

function isAsiento(seat) {
    return !seat.type || seat.type === 'seat'
}

function onToggleSeat(i, event) {
    event.evt?.stopPropagation();
    const seat = props.seats[i];

    // Siempre que no sea shift, solo seleccionar este asiento
    let updated;
    if (event.shiftKey) {
        updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, selected: !s.selected } : s
        );
    } else {
        // 🔥 Esto hace que SOLO se seleccione el asiento clickeado
        updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, selected: true } : { ...s, selected: false }
        );
        // 🔥 Limpia la selección de SHAPES en el padre
        // Lo más simple: emití un evento (o usá un callback) para que el padre limpie los shapes seleccionados
        // emit('clear-shapes'); // (deberías implementarlo si hace falta)
    }
    emit('update:seats', updated);
    
    //setTimeout(() => console.log('Seats después de update:', updated), 10)

    if (isAsiento(seat)) emit('edit-label', { seat, index: i });
}


function onSeatDragEnd(i, e) {
    const { x, y } = e.target.position()
    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, x, y, selected: true }
            : s
    )
    emit('update:seats', updated)
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


</script>
