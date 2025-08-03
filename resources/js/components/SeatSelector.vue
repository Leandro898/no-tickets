<!-- C:\xampp\htdocs\no-tickets\resources\js\components\SeatSelector.vue -->
<template>
    <div class="seat-selector-wrapper h-full">
        <div ref="containerRef" class="stage-container relative mx-auto w-full h-full" @mousemove="hidePopupOnMove">
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
                }" @wheel="onWheel" @mousedown="startMarquee" @mousemove="drawMarquee" @mouseup="endMarquee">
                    <v-layer>
                        <!-- Fondo -->
                        <v-image v-if="bgImage" :config="{ image: bgImage, width: BASE_CANVAS_WIDTH, height: BASE_CANVAS_HEIGHT }" />


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
                        <v-circle v-for="(seat, idx) in seats" :key="seat.id" :config="{
                            id: 'seat-' + seat.id,
                            x: seat.x,
                            y: seat.y,
                            radius: seat.radius,
                            fill: seat.selected ? '#a78bfa' : '#e5e7eb',
                            stroke: seat.selected ? '#7c3aed' : '#a1a1aa',
                            strokeWidth: 2,
                        }" @mouseover="onCircleEnter(idx, $event)" @mouseout="onCircleLeave"
                            @click="toggle(idx, $event)" />


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

            <!-- ➡️ Llamada al componente PurchasePanel -->
            <!-- Panel de compra -->
            <PurchasePanel :seats="purchaseSeats" :visible="showPurchase" @close="showPurchase = false"
                @remove="removeSeat" />
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, onMounted, computed, onBeforeUnmount, watch } from 'vue'
import axios from 'axios'
import PurchasePanel from './PurchasePanel.vue'
import { BASE_CANVAS_WIDTH, BASE_CANVAS_HEIGHT } from '@/constants/seatMap'

// Esto es para que se relacionen las dimensiones del canvas con el tamaño real del contenedor
const canvasW = ref(BASE_CANVAS_WIDTH)
const canvasH = ref(BASE_CANVAS_HEIGHT)

// Variables para proceso de compra de asiento
const showPurchase = ref(false)

// Lista reactiva de asientos seleccionados
const purchaseSeats = computed(() =>
    seats.value.filter(s => s.selected)
)

// Para popup de asiento
const popupSeat = ref(null)
const popupPosition = ref({ x: 0, y: 0 })
let hoverTimeout = null

// 👋 Oculta el popup tan pronto movés el mouse
function hidePopupOnMove() {
    if (popupSeat.value) {
        popupSeat.value = null
    }
}

const props = defineProps({
    eventoSlug: { type: String, required: true }
})
const emit = defineEmits(['selection-change'])

const seats = ref([])
const shapes = ref([])
const bgImage = ref(null)
const containerRef = ref(null)
const scale = ref(1)
const stageRef = ref(null)



// Marquee
const marquee = {
    visible: false,
    startX: 0, startY: 0,
    x: 0, y: 0, width: 0, height: 0
}

// Cerrar popup al hacer click fuera
function onClosePopup(e) {
    if (popupSeat.value && !e.target.closest('.seat-popup')) {
        popupSeat.value = null
    }
}

// Carga inicial de datos y listeners
onMounted(async () => {
    try {
        // 1) Llamás al endpoint que te devuelve 'id' de seats
        const res = await axios.get(
            `/api/eventos/${props.eventoSlug}/map`
        )

        const rawSeats = res.data.seats || []
        const rawShapes = res.data.shapes || []
        const bgUrl = res.data.bgUrl

        // 2) Mapear usando s.id, no s.entrada_id
        seats.value = rawSeats.map(s => ({
            id: s.id,           // <-- PK único de la tabla 'seats'
            entrada_id: s.entrada_id,   // sigue disponible si lo necesitás
            x: s.x <= 1 ? s.x * BASE_CANVAS_WIDTH : s.x,
            y: s.y <= 1 ? s.y * BASE_CANVAS_HEIGHT : s.y,
            label: s.label,
            price: s.price,
            radius: s.radius ?? 22,
            selected: false
        }))
        console.log('🔍 seats después del map desde SeatSelector:', seats.value)

        // 3) Shapes igual que antes
        shapes.value = rawShapes.map(s => ({
            ...s,
            fontSize: s.font_size || s.fontSize || 18
        }))

        // 4) Cargo imagen de fondo
        if (bgUrl) {
            const img = new window.Image()
            img.src = bgUrl
            await new Promise(r => (img.onload = r))
            bgImage.value = img
        }

    } catch (err) {
        console.error('No pude cargar el mapa:', err)
    }

    // listeners y escalado
    window.addEventListener('resize', updateScale)
    document.addEventListener('mousedown', onClosePopup)
    updateScale()
})


// Limpieza al salir del componente
onBeforeUnmount(() => {
    window.removeEventListener('resize', updateScale)
    // 💡 SACÁS EL ESCUCHADOR PARA EVITAR FILTRACIONES DE MEMORIA
    document.removeEventListener('mousedown', onClosePopup)
})
//console.log('SEATS:', seats.value)
// Alterna selección de un asiento
function toggle(idx, evt = null) {
    // 1) Invertir selección de este asiento - osea pasar de activo a desactivado
    seats.value[idx].selected = !seats.value[idx].selected

    // 2) 2) Emitir lista actualizada de IDs seleccionados
    const seleccionados = seats.value
        .filter(s => s.selected)
        .map(s => s.id)
    emit('selection-change', seleccionados)

    // 3) Popup individual solo si acabás de seleccionar
    if (seats.value[idx].selected) {
        popupSeat.value = seats.value[idx]
        // posición igual que antes…
        const x = evt?.evt?.clientX ?? seats.value[idx].x
        const y = evt?.evt?.clientY ?? seats.value[idx].y
        popupPosition.value = { x, y }
    } else {
        // si des­clickeó y ya no quería ver detalles, ocultalo
        popupSeat.value = null
    }

    // 🔥 Abrir/cerrar drawer en base al computed
    showPurchase.value = purchaseSeats.value.length > 0
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
    const c = containerRef.value;
    if (!c) return;
    const { offsetWidth: cw, offsetHeight: ch } = c;
    // canvasW.value = cw;
    // canvasH.value = ch;

    const scaleX = cw / BASE_CANVAS_WIDTH;
    const scaleY = ch / BASE_CANVAS_HEIGHT;

    const newScale = Math.min(scaleX, scaleY, 1);    // no sobredimensionar
    scale.value = newScale;

    const stage = stageRef.value.getStage();
    stage.scale({ x: newScale, y: newScale });
    stage.batchDraw();
}


// Desmarca un asiento individual y refresca el panel
// ✔️ Única función de “quitar asiento”
function removeSeat(id) {
    console.log('🗑️ Quiero quitar asiento:', id)
    // 1) Desmarco en el mapa
    const s = seats.value.find(x => x.id === id)
    if (s) s.selected = false

    // 2) Emito al padre la nueva lista de IDs
    const nuevos = seats.value
        .filter(x => x.selected)
        .map(x => x.id)
    emit('selection-change', nuevos)

    // 3) Mantengo abierto el drawer solo si queda al menos uno
    showPurchase.value = nuevos.length > 0
}





// 🔥 Abrir o cerrar drawer automáticamente al cambiar selección
watch(purchaseSeats, v => {
    showPurchase.value = v.length > 0
})

function openPurchasePanel() {
    // cerramos el popup
    popupSeat.value = null
    // abrimos el drawer de compra
    showPurchase.value = true
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
    max-width: 100vw;
    height: 80vh;
    /* o el que te guste */
    display: flex;
    align-items: center;
    justify-content: center;
}

.seat-selector-wrapper {
    position: relative;
}




</style>
