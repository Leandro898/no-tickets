<template>
    <!-- Recorremos los asientos válidos, guardando también su índice original -->
    <v-circle v-for="({ seat, originalIndex }, idx) in validSeats" :key="originalIndex" :config="{
        x: seat.x,
        y: seat.y,
        radius: seat.radius ?? defaultRadius,
        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
        stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
        strokeWidth: seat.selected ? 4 : 2,
        draggable: true
    }" @click="onToggleSeat(originalIndex, $event)" 
        @dragend="onSeatDragEnd(originalIndex, $event)" 
        @transformend.native="onCircleTransformEnd(originalIndex, $event)" 
        :ref="el => circleEls[originalIndex] = el" 
        />
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue'

// 1️⃣ Props y emits
const props = defineProps({
    seats: { type: Array, required: true },
    defaultRadius: { type: Number, default: 22 }
})
const emit = defineEmits(['update:seats'])

// 2️⃣ Creamos validSeats para filtrar y conservar índice
const validSeats = computed(() =>
    props.seats
        .map((s, idx) => ({ seat: s, originalIndex: idx }))
        .filter(({ seat }) =>
            seat &&
            typeof seat.x === 'number' && !isNaN(seat.x) &&
            typeof seat.y === 'number' && !isNaN(seat.y)
        )
)

// 3️⃣ Refs para manejar nodos Konva y exponerlos al padre (SeatCanvas.vue)
const circleEls = []               // cada <v-circle>
const selectedCircleRefs = ref([]) // nodos Konva seleccionados
defineExpose({ selectedCircleRefs })

// 4️⃣ Cuando cambie el flag `selected`, recalculamos los refs de Konva
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

// 5️⃣ Alternar selección con click (Shift permite multiselección)
function onToggleSeat(i, event) {
    const isShift = event.evt?.shiftKey
    const updated = props.seats.map((s, idx) => {
        if (idx === i) {
            return { ...s, selected: isShift ? !s.selected : !s.selected }
        }
        return { ...s, selected: isShift ? s.selected : false }
    })
    emit('update:seats', updated)
}

// 6️⃣ Al terminar de arrastrar, actualizamos posición y forzamos selected:true
function onSeatDragEnd(i, e) {
    const { x, y } = e.target.position()
    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, x, y, selected: true } // persistimos selección
            : s
    )
    emit('update:seats', updated)
}

// 7️⃣ Al terminar de redimensionar (transformend), calculamos nuevo radio y persistimos selección
function onCircleTransformEnd(i, evt) {
    const shape = evt.target
    const scaleX = shape.scaleX()
    const newR = (shape.radius() || props.defaultRadius) * scaleX

    const updated = props.seats.map((s, idx) =>
        idx === i
            ? { ...s, radius: newR, selected: true } // persistimos selección
            : s
    )
    emit('update:seats', updated)

    // Reseteamos escala para no acumular transformaciones
    shape.scaleX(1)
    shape.scaleY(1)
}
</script>
