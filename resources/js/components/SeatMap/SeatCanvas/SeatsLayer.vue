<template>
    <v-circle v-for="({ seat, originalIndex }) in validSeats" :key="originalIndex" :config="{
        x: seat.x,
        y: seat.y,
        radius: seat.radius ?? defaultRadius,
        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
        stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
        strokeWidth: seat.selected ? 4 : 2,
        draggable: true,
        dragDistance: 5
    }" @click="onToggleSeat(originalIndex, $event)" @dragend="onSeatDragEnd(originalIndex, $event)"
        @transformend.native="onCircleTransformEnd(originalIndex, $event)" :ref="el => circleEls[originalIndex] = el" />
</template>

<script setup>
import { ref, watch, nextTick, computed, onMounted, onBeforeUnmount } from 'vue'
const emit = defineEmits(['update:seats', 'edit-label']);
const isShiftPressed = ref(false);

onMounted(() => {
    window.addEventListener('keydown', handleShiftDown);
    window.addEventListener('keyup', handleShiftUp);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleShiftDown);
    window.removeEventListener('keyup', handleShiftUp);
});
function handleShiftDown(e) {
    if (e.key === 'Shift') isShiftPressed.value = true;
}
function handleShiftUp(e) {
    if (e.key === 'Shift') isShiftPressed.value = false;
}

const props = defineProps({
    seats: { type: Array, required: true },
    defaultRadius: { type: Number, default: 22 }
})


const validSeats = computed(() =>
    props.seats
        .map((s, idx) => ({ seat: s, originalIndex: idx }))
        .filter(({ seat }) =>
            seat && (
                !seat.type || seat.type === 'seat' // SOLO ASIENTOS REALES
            ) &&
            typeof seat.x === 'number' &&
            typeof seat.y === 'number'
        )
)



const circleEls = []
const selectedCircleRefs = ref([])
defineExpose({ selectedCircleRefs })

watch(
    () => props.seats.map(s => s.selected),
    async () => {
        await nextTick()
        selectedCircleRefs.value = props.seats
            .map((s, idx) =>
                s.selected && circleEls[idx]?.getNode
                    ? circleEls[idx].getNode()
                    : null
            )
            .filter(Boolean)
    },
    { immediate: true }
)

// PARA QUE LA EDICION DEL LABEL SOLAMENTE APAREZCA EN LOS ASIENTOS
function isAsiento(seat) {
    // type vacío o 'seat'
    return !seat.type || seat.type === 'seat';
}

function onToggleSeat(i, event) {
    event.evt?.stopPropagation();
    const seat = props.seats[i];
    console.log("Click en", seat);
    let updated;

    if (isShiftPressed.value) {
        // Selección múltiple con Shift
        updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, selected: !s.selected } : s
        );
        emit('update:seats', updated);
    } else {
        // Selección simple
        updated = props.seats.map((s, idx) =>
            idx === i ? { ...s, selected: true } : { ...s, selected: false }
        );
        emit(
            'update:seats',
            updated.concat(
                props.seats
                    .filter(s => s.type && s.type !== 'seat')
                    .map(s => ({ ...s, selected: false }))
            )
        );
    }

    // 👉 SOLO SI ES ASIENTO, emitimos para abrir editor de label
    if (isAsiento(seat)) {
        emit('edit-label', { seat, index: i });
    }

    // Restablece el draggable
    const node = circleEls[i]?.getNode();
    if (node) {
        node.draggable(false);
        nextTick(() => node.draggable(true));
    }
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
