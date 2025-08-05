<!-- resources/js/components/SeatSelector.vue -->
<template>
    <div class="seat-selector-wrapper">

        <!-- Loader mientras baja la data -->
        <div v-if="loading" class="loader-container">
            <div class="loader">Cargando mapa de asientos…</div>
        </div>

        <!-- Canvas + pop-up -->
        <div v-else ref="containerRef" class="stage-container" @mousemove="hidePopupOnMove">
            <div class="relative">

                <!-- Controles Zoom/Pan/Reset -->
                <div class="absolute top-2 left-2 z-10 flex gap-2 bg-white bg-opacity-80 p-2 rounded">
                    <button @click="zoomIn" title="Zoom In">＋</button>
                    <button @click="zoomOut" title="Zoom Out">－</button>
                    <button @click="resetZoom" title="Reset">⟳</button>
                </div>

                <!-- Konva Stage -->
                <v-stage ref="stageRef" :config="{
                    width: BASE_CANVAS_WIDTH,
                    height: BASE_CANVAS_HEIGHT,
                    draggable: true,
                    scaleX: scale,
                    scaleY: scale
                }" @wheel="onWheel" @mousedown="e => { if (!isMobile) startMarquee(e) }"
                    @mousemove="e => { if (!isMobile) drawMarquee(e) }" @mouseup="e => { if (!isMobile) endMarquee(e) }"
                    @touchstart="e => { if (!isMobile) startMarquee(e) }"
                    @touchmove="e => { if (!isMobile) drawMarquee(e) }"
                    @touchend="e => { if (!isMobile) endMarquee(e) }">
                    <v-layer>

                        <!-- Fondo -->
                        <v-image v-if="bgImage" :config="{
                            image: bgImage,
                            width: BASE_CANVAS_WIDTH,
                            height: BASE_CANVAS_HEIGHT
                        }" />

                        <!-- Shapes (rect, circle, text) -->
                        <template v-for="(shape, idx) in shapes" :key="'shape-'+idx">
                            <v-rect v-if="shape.type === 'rect'" :config="{
                                x: shape.x, y: shape.y,
                                width: shape.width, height: shape.height,
                                rotation: shape.rotation || 0,
                                fill: '#e0e7ff', stroke: '#818cf8', strokeWidth: 2
                            }" />
                            <v-circle v-else-if="shape.type === 'circle'" :config="{
                                x: shape.x, y: shape.y,
                                radius: (shape.width || 30) / 2,
                                fill: '#e0e7ff', stroke: '#818cf8', strokeWidth: 2
                            }" />
                            <v-text v-else-if="shape.type === 'text'" :config="{
                                x: shape.x, y: shape.y,
                                text: shape.label,
                                fontSize: shape.fontSize || 18,
                                fill: '#6366f1', fontStyle: 'bold',
                                rotation: shape.rotation || 0
                            }" />
                        </template>

                        <!-- Asientos -->
                        <v-circle v-for="(seat, idx) in seats" :key="seat.id" :id="'seat-' + seat.id" :x="seat.x"
                            :y="seat.y" :radius="seat.radius" :fill="seat.status === 'vendido' ? '#f87171' :
                                seat.status === 'reservado' ? '#facc15' :
                                    seat.selected ? '#a78bfa' :
                                        '#e5e7eb'
                                " :stroke="seat.status === 'vendido' ? '#dc2626' :
                                    seat.status === 'reservado' ? '#d97706' :
                                        seat.selected ? '#7c3aed' :
                                            '#a1a1aa'
                                    " :strokeWidth="2" :listening="seat.status === 'disponible'" @mouseover="onCircleEnter(idx, $event)"
                            @mouseout="onCircleLeave" @click="toggle(idx, $event)" @tap="toggle(idx, $event)" />

                        <!-- Selección rectangular (marquee) -->
                        <v-rect v-if="marquee.visible" :config="marqueeRectConfig" />

                    </v-layer>
                </v-stage>

                <!-- Pop-up info asiento -->
                <div v-if="popupSeat" :style="popupStyle" class="seat-popup">
                    <div class="popup-title">
                        Asiento {{ popupSeat.label || popupSeat.id }}
                    </div>
                    <div>
                        <b>Sector:</b> {{ popupSeat.sector || '—' }}<br>
                        <b>Fila:</b> {{ popupSeat.row || '—' }}<br>
                        <b>Número:</b> {{ popupSeat.number || '—' }}<br>
                        <b>Precio:</b> ${{ popupSeat.price || '--' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>
import { defineProps, defineEmits, ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { BASE_CANVAS_WIDTH, BASE_CANVAS_HEIGHT } from '@/constants/seatMap'

// Props & emits
const props = defineProps({
    eventoSlug: { type: String, required: true }
})
const emit = defineEmits(['selection-change'])

// Estado
const loading = ref(true)
const isMobile = window.innerWidth <= 640

const containerRef = ref(null)
const stageRef = ref(null)
const scale = ref(1)
const seats = ref([])
const shapes = ref([])
const bgImage = ref(null)

// Pop-up
const popupSeat = ref(null)
const popupPosition = ref({ x: 0, y: 0 })

// hover/marquee
let hoverTimeout = null
let reserveCleanupInterval = null
const marquee = { visible: false, startX: 0, startY: 0, x: 0, y: 0, width: 0, height: 0 }
const marqueeRectConfig = computed(() => ({
    x: marquee.x, y: marquee.y,
    width: marquee.width, height: marquee.height,
    fill: 'rgba(0,0,255,0.1)', stroke: 'blue', dash: [4, 4]
}))

// Pop-up style clamped al viewport
const POPUP_MIN_WIDTH = 260
const EDGE_MARGIN = 8
const popupStyle = computed(() => {
    let x = popupPosition.value.x
    const vw = window.innerWidth
    const half = POPUP_MIN_WIDTH / 2
    const minC = half + EDGE_MARGIN
    const maxC = vw - half - EDGE_MARGIN
    if (x < minC) x = minC
    if (x > maxC) x = maxC

    return {
        position: 'fixed',
        left: `${x}px`,
        top: `${popupPosition.value.y}px`,
        transform: 'translate(-50%, -100%)',
        marginBottom: '8px',
        zIndex: 9999,
        background: 'white',
        boxShadow: '0 4px 24px rgba(0,0,0,0.14)',
        borderRadius: '14px',
        padding: '22px 24px',
        minWidth: `${POPUP_MIN_WIDTH}px`,
        border: '1px solid #d1d5db',
        pointerEvents: 'auto',
    }
})

// Carga inicial
onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/eventos/${props.eventoSlug}/map`)
        seats.value = data.seats.map(s => ({
            id: s.id,
            x: s.x <= 1 ? s.x * BASE_CANVAS_WIDTH : s.x,
            y: s.y <= 1 ? s.y * BASE_CANVAS_HEIGHT : s.y,
            radius: s.radius || 22,
            label: s.label,
            price: s.price,
            status: s.status,
            reservedUntil: s.reserved_until ? new Date(s.reserved_until) : null,
            selected: false
        }))
        shapes.value = data.shapes.map(s => ({ ...s, fontSize: s.font_size || 18 }))
        if (data.bgUrl) {
            const img = new Image(); img.src = data.bgUrl
            await new Promise(r => img.onload = r)
            bgImage.value = img
        }

        // Init zoom/pan & listeners
        updateScale()
        window.addEventListener('resize', updateScale)
        document.addEventListener('mousedown', onClosePopup)

        // Limpiar reservas expiradas
        reserveCleanupInterval = setInterval(() => {
            const now = Date.now()
            seats.value.forEach(s => {
                if (s.status === 'reservado' && s.reservedUntil?.getTime() < now) {
                    s.status = 'disponible'
                    s.reservedUntil = null
                }
            })
        }, 1000)

    } finally {
        loading.value = false
    }
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateScale)
    document.removeEventListener('mousedown', onClosePopup)
    clearInterval(reserveCleanupInterval)
})

// — Funciones auxiliares —

function updateScale() {
    const c = containerRef.value
    const st = stageRef.value?.getStage()
    if (!c || !st) return
    const cw = c.offsetWidth, ch = c.offsetHeight
    let rs = Math.min(cw / BASE_CANVAS_WIDTH, ch / BASE_CANVAS_HEIGHT, 1)
    if (isMobile) rs *= 0.5
    scale.value = rs
    st.scale({ x: rs, y: rs })
    const offX = isMobile ? 20 : 0
    const offY = isMobile ? -190 : -20
    const x = (cw - BASE_CANVAS_WIDTH * rs) / 2 + offX
    const y = (ch - BASE_CANVAS_HEIGHT * rs) / 2 + offY
    st.position({ x, y })
    st.batchDraw()
}

function toggle(idx, e) {
    const s = seats.value[idx]
    if (s.status !== 'disponible') return
    s.selected = !s.selected
    emit('selection-change', seats.value.filter(x => x.selected).map(x => ({
        id: x.id, label: x.label, price: x.price
    })))
    popupSeat.value = s.selected ? s : null
    if (s.selected) {
        const px = e?.evt?.clientX || s.x
        const py = e?.evt?.clientY || s.y
        popupPosition.value = { x: px, y: py }
    }
}

function onCircleEnter(idx, e) {
    clearTimeout(hoverTimeout)
    hoverTimeout = setTimeout(() => {
        popupSeat.value = seats.value[idx]
        const { clientX: x, clientY: y } = e.evt
        popupPosition.value = { x: x + 8, y: y + 8 }
    }, 400)
}
function onCircleLeave() {
    clearTimeout(hoverTimeout)
    popupSeat.value = null
}
function onClosePopup(e) {
    if (popupSeat.value && !e.target.closest('.seat-popup'))
        popupSeat.value = null
}

function onWheel(e) {
    e.evt.preventDefault()
    const st = stageRef.value.getStage()
    const oldS = st.scaleX(), p = st.getPointerPosition()
    const newS = e.evt.deltaY > 0 ? oldS * 0.9 : oldS * 1.1
    st.scale({ x: newS, y: newS })
    const mp = { x: (p.x - st.x()) / oldS, y: (p.y - st.y()) / oldS }
    st.position({ x: p.x - mp.x * newS, y: p.y - mp.y * newS })
    scale.value = newS
}
function zoomIn() { scale.value *= 1.2; stageRef.value.getStage().scale({ x: scale.value, y: scale.value }) }
function zoomOut() { scale.value /= 1.2; stageRef.value.getStage().scale({ x: scale.value, y: scale.value }) }
function resetZoom() { scale.value = 1; const s = stageRef.value.getStage(); s.scale({ x: 1, y: 1 }); s.position({ x: 0, y: 0 }) }

function startMarquee({ evt }) {
    const st = stageRef.value.getStage(), ptr = st.getPointerPosition()
    marquee.startX = ptr.x; marquee.startY = ptr.y; marquee.visible = true
}
function drawMarquee() {
    if (!marquee.visible) return
    const ptr = stageRef.value.getStage().getPointerPosition()
    marquee.x = Math.min(marquee.startX, ptr.x)
    marquee.y = Math.min(marquee.startY, ptr.y)
    marquee.width = Math.abs(ptr.x - marquee.startX)
    marquee.height = Math.abs(ptr.y - marquee.startY)
}
function endMarquee() {
    marquee.visible = false
    const st = stageRef.value.getStage()
    seats.value.forEach(s => {
        const c = st.findOne('#seat-' + s.id)
        if (c && c.intersects({
            x: marquee.x, y: marquee.y,
            width: marquee.width, height: marquee.height
        })) s.selected = true
    })
    emit('selection-change',
        seats.value.filter(s => s.selected).map(s => ({
            id: s.id, label: s.label, price: s.price
        }))
    )
}

function hidePopupOnMove() {
    if (popupSeat.value) popupSeat.value = null
}
</script>


<style scoped>
/* Loader */
.loader-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #faf5ff;
}

.loader {
    font-size: 1.2rem;
    color: #6366f1;
}

/* Canvas */
.stage-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #faf5ff;
    touch-action: pinch-zoom;
}

.seat-selector-wrapper {
    position: relative;
}

/* Botones Zoom/Pan */
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

/* Pop-up: la clase queda vacía porque todo va en popupStyle */
.seat-popup {}
</style>
