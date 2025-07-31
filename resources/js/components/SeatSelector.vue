<template>
    <div ref="containerRef" class="stage-container relative mx-auto">
        <div class="relative">
            <!-- Controles de Zoom/Pan/Reset -->
            <div class="absolute top-2 left-2 z-10 flex gap-2 bg-white bg-opacity-80 p-2 rounded">
                <button @click="zoomIn" title="Zoom In">＋</button>
                <button @click="zoomOut" title="Zoom Out">－</button>
                <button @click="resetZoom" title="Reset">⟳</button>
            </div>

            <!-- Lienzo Konva -->
            <v-stage ref="stageRef" :config="{
                width: baseWidth,
                height: baseHeight,
                draggable: true,
                scaleX: scale,
                scaleY: scale
            }" @wheel="onWheel" @mousedown="startMarquee" @mousemove="drawMarquee" @mouseup="endMarquee">
                <v-layer>
                    <!-- Fondo -->
                    <v-image v-if="bgImage" :config="{ image: bgImage, width: baseWidth, height: baseHeight }" />


                    <!-- Shapes -->
                    <template v-for="(shape, idx) in shapes" :key="'shape-' + idx">
                        <!-- Rectángulos -->
                        <v-rect v-if="shape.type === 'rect'" :config="{
                            x: shape.x,
                            y: shape.y,
                            width: shape.width,
                            height: shape.height,
                            rotation: shape.rotation || 0,
                            fill: '#e0e7ff',
                            stroke: '#818cf8',
                            strokeWidth: 2,
                        }" />
                        <!-- Círculos -->
                        <v-circle v-else-if="shape.type === 'circle'" :config="{
                            x: shape.x,
                            y: shape.y,
                            width: shape.width,
                            height: shape.height,
                            radius: shape.width ? shape.width / 2 : 30,
                            fill: '#e0e7ff',
                            stroke: '#818cf8',
                            strokeWidth: 2,
                        }" />
                        <!-- Textos -->
                        <v-text v-else-if="shape.type === 'text'" :config="{
                            x: shape.x,
                            y: shape.y,
                            text: shape.label,
                            fontSize: shape.font_size || shape.fontSize || 18,
                            fill: '#6366f1',
                            fontStyle: 'bold',
                            rotation: shape.rotation || 0,
                        }" />
                    </template>

                    <!-- Asientos -->
                    <v-circle v-for="seat in seats" :key="'seat-' + seat.id" :config="{
                        id: 'seat-' + seat.id,
                        x: seat.x,
                        y: seat.y,
                        radius: seat.radius,
                        fill: seat.selected ? '#a78bfa' : '#e5e7eb',
                        stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
                        strokeWidth: 2,
                    }" @click="toggle(seat.id)" @mouseenter="showTooltip(seat, $event)" @mouseleave="hideTooltip" />

                    <!-- Tooltip -->
                    <v-label v-if="tooltip.visible" :config="tooltip.labelConfig">
                        <v-tag :config="tooltip.tagConfig" />
                        <v-text :config="tooltip.textConfig" />
                    </v-label>
                    <!-- Rectángulo de selección (“marquee”) -->
                    <v-rect v-if="marquee.visible" :config="marqueeRectConfig" />
                </v-layer>
            </v-stage>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    eventoSlug: { type: String, required: true }
})
const emit = defineEmits(['selection-change'])

const seats = ref([])
const shapes = ref([])
const bgImage = ref(null)
const containerRef = ref(null)
const scale = ref(1)
const baseWidth = 1000
const baseHeight = 800
const stageRef = ref(null)

// Tooltip
const tooltip = {
    visible: false,
    labelConfig: { x: 0, y: 0 },
    tagConfig: { fill: 'black', cornerRadius: 4 },
    textConfig: { text: '', fontSize: 14, fill: 'white', padding: 6 }
}

// Marquee
const marquee = {
    visible: false,
    startX: 0, startY: 0,
    x: 0, y: 0, width: 0, height: 0
}

// Carga inicial del mapa (fondo + shapes + asientos)
onMounted(async () => {
    try {
        const res = await axios.get(`/api/eventos/${props.eventoSlug}/map`)
        const { seats: rawSeats, shapes: rawShapes, bgUrl } = res.data
        seats.value = (rawSeats || []).map(s => ({
            ...s,
            radius: s.radius ?? 22,
            selected: false
        }))
        shapes.value = (rawShapes || []).map(s => ({
            ...s,
            fontSize: s.font_size || s.fontSize || 18
        }))
        if (bgUrl) {
            const img = new window.Image()
            img.src = bgUrl
            await new Promise(r => (img.onload = r))
            bgImage.value = img
        }
    } catch (err) {
        console.error('No pude cargar el mapa:', err)
    }
    window.addEventListener('resize', updateScale)
    updateScale()
})


// Alterna selección de un asiento
function toggle(id) {
    const seat = seats.value.find(s => s.id === id)
    seat.selected = !seat.selected
    emit('selection-change', seats.value.filter(s => s.selected).map(s => s.id))
}

// Zoom con rueda
function onWheel(e) {
    e.evt.preventDefault()
    const stage = stageRef.value.getStage()
    const oldScale = stage.scaleX()
    const pointer = stage.getPointerPosition()
    const newScale = e.evt.deltaY > 0 ? oldScale * 0.9 : oldScale * 1.1
    stage.scale({ x: newScale, y: newScale })
    // Center zoom under cursor
    const mousePointTo = {
        x: (pointer.x - stage.x()) / oldScale,
        y: (pointer.y - stage.y()) / oldScale
    }
    stage.position({
        x: pointer.x - mousePointTo.x * newScale,
        y: pointer.y - mousePointTo.y * newScale
    })
    scale.value = newScale
}
function zoomIn() { scale.value *= 1.2; stageRef.value.getStage().scale({ x: scale.value, y: scale.value }) }
function zoomOut() { scale.value /= 1.2; stageRef.value.getStage().scale({ x: scale.value, y: scale.value }) }
function resetZoom() {
    scale.value = 1
    const stage = stageRef.value.getStage()
    stage.scale({ x: 1, y: 1 })
    stage.position({ x: 0, y: 0 })
}

function showTooltip(seat, { evt }) {
    const stage = stageRef.value.getStage()
    const pointer = stage.getPointerPosition()
    tooltip.visible = true
    tooltip.labelConfig.x = pointer.x + 10
    tooltip.labelConfig.y = pointer.y + 10

    // Usar label si existe y no es vacío, sino mostrar número, sino ID
    if (seat.label && seat.label !== 'null' && seat.label !== 'undefined' && seat.label.trim() !== '') {
        tooltip.textConfig.text = seat.label
    } else if (typeof seat.number !== 'undefined' && seat.number !== null && seat.number !== 0) {
        tooltip.textConfig.text = `#${seat.number}`
    } else {
        tooltip.textConfig.text = `#${seat.id}`
    }
}


function hideTooltip() {
    tooltip.visible = false
}

// Marquee (selección por rectángulo)
function startMarquee({ evt }) {
    const stage = stageRef.value.getStage()
    const ptr = stage.getPointerPosition()
    marquee.startX = ptr.x
    marquee.startY = ptr.y
    marquee.visible = true
}
function drawMarquee({ evt }) {
    if (!marquee.visible) return
    const ptr = stageRef.value.getStage().getPointerPosition()
    marquee.x = Math.min(marquee.startX, ptr.x)
    marquee.y = Math.min(marquee.startY, ptr.y)
    marquee.width = Math.abs(ptr.x - marquee.startX)
    marquee.height = Math.abs(ptr.y - marquee.startY)
}
function endMarquee() {
    marquee.visible = false
    const stage = stageRef.value.getStage()
    seats.value.forEach(s => {
        const circle = stage.findOne(`#seat-${s.id}`)
        if (circle && circle.intersects({
            x: marquee.x, y: marquee.y,
            width: marquee.width, height: marquee.height
        })) {
            s.selected = true
        }
    })
    emit('selection-change', seats.value.filter(s => s.selected).map(s => s.id))
}

// Configuración reactiva del rectángulo
const marqueeRectConfig = computed(() => ({
    x: marquee.x,
    y: marquee.y,
    width: marquee.width,
    height: marquee.height,
    fill: 'rgba(0,0,255,0.1)',
    stroke: 'blue',
    dash: [4, 4]
}))


function updateScale() {
    if (containerRef.value) {
        // Tomá el ancho real disponible
        const parentWidth = containerRef.value.offsetWidth
        // Calcula el factor de escala (si querés mantener proporción usa Math.min)
        scale.value = Math.min(parentWidth / baseWidth, 1) // nunca escalar más de 1
    }
}


</script>

<style scoped>
.absolute button {
    background: #fff;
    border: 1px solid #888;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
}

.absolute button:hover {
    background: #f0f0f0;
}

.stage-container {
    width: 100%;
    max-width: 1000px;
    height: auto;
    /* Para que nunca supere el tamaño original */
}
</style>
