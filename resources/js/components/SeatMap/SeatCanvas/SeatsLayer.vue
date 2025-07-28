<!-- resources/js/components/SeatMap/SeatCanvas/SeatsLayer.vue -->
<template>
    <v-circle v-for="({ seat, originalIndex }) in validSeats" :key="originalIndex" :config="{
        x: seat.x,
        y: seat.y,
        radius: seat.radius ?? defaultRadius,
        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
        stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
        strokeWidth: seat.selected ? 4 : 2,
        draggable: true,            // ← aquí
        dragDistance: 5               // ← y aquí
    }" @click="onToggleSeat(originalIndex, $event)" @dragend="onSeatDragEnd(originalIndex, $event)"
        @transformend.native="onCircleTransformEnd(originalIndex, $event)" :ref="el => circleEls[originalIndex] = el" />
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue'

// Props & emits
const props = defineProps({
    seats: { type: Array, required: true },
    defaultRadius: { type: Number, default: 22 }
})
const emit = defineEmits(['update:seats'])

// Filtrar asientos válidos y conservar índice
const validSeats = computed(() =>
    props.seats
        .map((s, idx) => ({ seat: s, originalIndex: idx }))
        .filter(({ seat }) =>
            seat && typeof seat.x === 'number' && typeof seat.y === 'number'
        )
)

// Refs para Konva y Transformer
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

// Seleccionar con click
function onToggleSeat(i, event) {
    // parar propagación para que no llegue al stage
    const evt = event.evt || {}
    evt.stopPropagation?.()

    const updated = props.seats.map((s, idx) => ({
        ...s,
        selected: idx === i
    }))
    emit('update:seats', updated)
}

// Al soltar drag actualizar coords y mantener selección
function onSeatDragEnd(i, e) {
    const { x, y } = e.target.position()
    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, x, y, selected: true }
            : s
    )
    emit('update:seats', updated)
}

// Al terminar transform actualizar radius y mantener selección
function onCircleTransformEnd(i, evt) {
    const shape = evt.target
    const scaleX = shape.scaleX()
    const newR = (shape.radius() || props.defaultRadius) * scaleX

    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, radius: newR, selected: true }
            : s
    )
    emit('update:seats', updated)

    // resetear escala
    shape.scaleX(1)
    shape.scaleY(1)
}
</script>
