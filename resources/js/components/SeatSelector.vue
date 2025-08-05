<!-- C:\xampp\htdocs\no-tickets\resources\js\components\SeatSelector.vue -->
<template>
    <div class="seat-selector-wrapper">
        <div ref="containerRef" class="stage-container" @mousemove="hidePopupOnMove">
            <div class="relative">
                <!-- Controles de Zoom/Pan/Reset -->
                <div class="absolute top-2 left-2 z-10 flex gap-2 bg-white bg-opacity-80 p-2 rounded">
                    <button @click="zoomIn" title="Zoom In">＋</button>
                    <button @click="zoomOut" title="Zoom Out">－</button>
                    <button @click="resetZoom" title="Reset">⟳</button>
                </div>

                <!-- Lienzo Konva -->
                <v-stage ref="stageRef" :config="{
                    width: BASE_CANVAS_WIDTH,
                    height: BASE_CANVAS_HEIGHT,
                    draggable: true,
                    scaleX: scale,
                    scaleY: scale
                }" @wheel="onWheel" @mousedown="e => { if (!isMobile) startMarquee(e) }"
                    @mousemove="e => { if (!isMobile) drawMarquee(e) }" @mouseup="e => { if (!isMobile) endMarquee(e) }"
                    @touchstart.prevent="e => { if (!isMobile) startMarquee(e) }"
                    @touchmove.prevent="e => { if (!isMobile) drawMarquee(e) }"
                    @touchend.prevent="e => { if (!isMobile) endMarquee(e) }">
                    <v-layer>
                        <!-- Fondo -->
                        <v-image v-if="bgImage"
                            :config="{ image: bgImage, width: BASE_CANVAS_WIDTH, height: BASE_CANVAS_HEIGHT }" />


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
                        <v-circle v-for="(seat, idx) in seats" :key="seat.id" :id="'seat-' + seat.id" :x="seat.x"
                            :y="seat.y" :radius="seat.radius"  :fill="seat.status === 'vendido'
                                ? '#f87171'
                                : seat.status === 'reservado'
                                    ? '#facc15'
                                    : seat.selected
                                        ? '#a78bfa'
                                        : '#e5e7eb'"  :stroke="seat.status === 'vendido'
                                            ? '#dc2626'
                                            : seat.status === 'reservado'
                                                ? '#d97706'
                                                : seat.selected
                                                    ? '#7c3aed'
                                                    : '#a1a1aa'"  :listening="seat.status === 'disponible'" :strokeWidth="2"
                            @mouseover="onCircleEnter(idx, $event)" @mouseout="onCircleLeave"
                            @click="toggle(idx, $event)" @tap="toggle(idx, $event)" />



                        <!-- Rectángulo de selección (“marquee”) -->
                        <v-rect v-if="marquee.visible" :config="marqueeRectConfig" />
                    </v-layer>
                </v-stage>
                <div v-if="popupSeat" :style="{
                    position: 'fixed',
                    left: popupPosition.x + 20 + 'px',
                    top: popupPosition.y - 20 + 'px',
                    zIndex: 9999,
                    background: 'white',
                    boxShadow: '0 4px 24px rgba(0,0,0,0.14)',
                    borderRadius: '14px',
                    padding: '22px 24px',
                    minWidth: '260px',
                    border: '1px solid #d1d5db',
                    pointerEvents: 'auto'
                }" class="seat-popup">

                    <!-- aquí va tu contenido del asiento… -->
                    <div style="font-weight: bold; font-size: 1.1rem; color: #6366f1; margin-bottom: 8px;">
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
/*–––––––––– Imports y Props ––––––––––*/
import { defineProps, defineEmits, ref, onMounted, computed, onBeforeUnmount, watch } from 'vue'
import axios from 'axios'
import { BASE_CANVAS_WIDTH, BASE_CANVAS_HEIGHT } from '@/constants/seatMap'

// Detectar si es móvil para ajustar el zoom
const isMobile = window.innerWidth <= 640

const props = defineProps({
    eventoSlug: { type: String, required: true }
})
const emit = defineEmits(['selection-change'])

/*–––––––––– Refs y Reactive ––––––––––*/
const containerRef = ref(null)
const stageRef = ref(null)
const scale = ref(1)
const seats = ref([])
const shapes = ref([])
const bgImage = ref(null)
// Para popup de asiento
const popupSeat = ref(null)
const popupPosition = ref({ x: 0, y: 0 })
let hoverTimeout = null
// 🟢 Añadimos un temporizador para liberar reservas expiradas
let reserveCleanupInterval = null


// Lista reactiva de asientos seleccionados
const purchaseSeats = computed(() =>
    seats.value.filter(s => s.selected)
)

// Marquee
const marquee = {
    visible: false,
    startX: 0, startY: 0,
    x: 0, y: 0, width: 0, height: 0
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


/*–––––––––– Lifecycle ––––––––––*/
// Carga inicial de datos y listeners
onMounted(async () => {
    const res = await axios.get(`/api/eventos/${props.eventoSlug}/map`)
    seats.value = res.data.seats.map(s => ({
        id: s.id,
        x: s.x <= 1 ? s.x * BASE_CANVAS_WIDTH : s.x,
        y: s.y <= 1 ? s.y * BASE_CANVAS_HEIGHT : s.y,
        radius: s.radius || 22,
        label: s.label,
        price: s.price,
        status: s.status,                          // 'disponible'|'reservado'|'vendido'
        reservedUntil: s.reserved_until ? new Date(s.reserved_until) : null,
        selected: false
    }))
    shapes.value = res.data.shapes.map(s => ({ ...s, fontSize: s.font_size || 18 }))
    if (res.data.bgUrl) {
        const img = new Image(); img.src = res.data.bgUrl
        await new Promise(r => img.onload = r)
        bgImage.value = img
    }
    window.addEventListener('resize', updateScale)
    document.addEventListener('mousedown', onClosePopup)
    updateScale()

    // 👉 **ADICIÓN**: liberar automáticamente reservas expiradas cada segundo
    reserveCleanupInterval = setInterval(() => {
        const now = Date.now()
        seats.value.forEach(s => {
            if (s.status === 'reservado' && s.reservedUntil && now > s.reservedUntil.getTime()) {
                s.status = 'disponible'
                s.reservedUntil = null
            }
        })
    }, 1000)
})

// Limpieza al salir del componente
onBeforeUnmount(() => {
    window.removeEventListener('resize', updateScale)
    // 💡 SACÁS EL ESCUCHADOR PARA EVITAR FILTRACIONES DE MEMORIA
    document.removeEventListener('mousedown', onClosePopup)

    // 👉 **ADICIÓN**: detener el intervalo de expiraciones
    clearInterval(reserveCleanupInterval)
})

/*–––––––––– Data & Map Load ––––––––––*/


/*–––––––––– Escalado y Centrando ––––––––––*/
function updateScale() {
    const c = containerRef.value
    if (!c || !stageRef.value) return

    const cw = c.offsetWidth
    const ch = c.offsetHeight

    // 1) escala base
    let rawScale = Math.min(cw / BASE_CANVAS_WIDTH, ch / BASE_CANVAS_HEIGHT, 1)

    // 2) distinto factor de zoom para mobile vs desktop
    const isMobile = window.innerWidth <= 640
    if (isMobile) {
        rawScale *= 0.5     // 🔴 aquí reduces más el zoom en móvil
    }

    // 3) aplicamos escala
    scale.value = rawScale
    const stage = stageRef.value.getStage()
    stage.scale({ x: rawScale, y: rawScale })

    // 4) offsets distintos según dispositivo
    const desktopYOffset = -20
    const mobileYOffset = -190
    const offsetY = isMobile ? mobileYOffset : desktopYOffset

    const desktopXOffset = 0      // 👈 sin desplazamiento en desktop
    const mobileXOffset = 20     // 👈 mueves 20px a la derecha en móvil
    const offsetX = isMobile ? mobileXOffset : desktopXOffset

    // 5) calculamos la posición centrada + offsets
    const x = (cw - BASE_CANVAS_WIDTH * rawScale) / 2 + offsetX
    const y = (ch - BASE_CANVAS_HEIGHT * rawScale) / 2 + offsetY

    stage.position({ x, y })
    stage.batchDraw()
}



/*–––––––––– Interacción Asientos ––––––––––*/
// Alterna selección de un asiento
function toggle(idx, evt = null) {
    const s = seats.value[idx];
    // ■ Si NO está disponible, salimos sin hacer nada
    if (s.status !== 'disponible') {
        return;
    }

    // ■ 1) Invertir selección
    s.selected = !s.selected;

    // ■ 2) Emitir lista actualizada
    const seleccionados = seats.value
        .filter(x => x.selected)
        .map(x => ({ id: x.id, label: x.label, price: x.price }));
    emit('selection-change', seleccionados);

    // ■ 3) Popup si es nueva selección
    if (s.selected) {
        popupSeat.value = s;
        const x = evt?.evt?.clientX ?? s.x;
        const y = evt?.evt?.clientY ?? s.y;
        popupPosition.value = { x, y };
    } else {
        popupSeat.value = null;
    }
}




// Mostrar popup al pasar el mouse por encima de un círculo
function onCircleEnter(idx, e) {
    clearTimeout(hoverTimeout)
    hoverTimeout = setTimeout(() => {
        // solo tras 400 ms mostramos el tooltip
        popupSeat.value = seats.value[idx]
        const { clientX: x, clientY: y } = e.evt
        popupPosition.value = { x: x + 8, y: y + 8 }
    }, 400)
}

function onCircleLeave() {
    clearTimeout(hoverTimeout)
    popupSeat.value = null
}

/*–––––––––– Popup externo ––––––––––*/
// Cerrar popup al hacer click fuera
function onClosePopup(e) {
    if (popupSeat.value && !e.target.closest('.seat-popup')) {
        popupSeat.value = null
    }
}

/*–––––––––– Zoom / Pan / Reset ––––––––––*/
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

/*–––––––––– Marquee (selección rectangular) ––––––––––*/
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
    emit('selection-change', seats.value
        .filter(s => s.selected)
        .map(s => ({ id: s.id, label: s.label, price: s.price }))
    )

}

/*–––––––––– Remover asiento desde panel ––––––––––*/
// Desmarca un asiento individual y refresca el panel
// ✔️ Única función de “quitar asiento”






// 👋 Oculta el popup tan pronto movés el mouse
function hidePopupOnMove() {
    if (popupSeat.value) {
        popupSeat.value = null
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
    height: 100%;
    overflow: hidden;
    /* evita scroll interno */
    display: flex;
    align-items: center;
    justify-content: center;
    background: #faf5ff;
}



.seat-selector-wrapper {
    position: relative;
}
</style>
