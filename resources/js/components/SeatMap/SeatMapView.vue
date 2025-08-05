<!-- C:\xampp\htdocs\no-tickets\resources\js\components\SeatMap\SeatMapView.vue -->
<template>
    <div v-bind="$attrs" style="width: 100%; height: 100%; position: relative;">
        <v-stage ref="canvasRef" :config="{ width, height, draggable: panMode }" @mousedown="onStageMouseDown"
            @mousemove="onStageMouseMove" @mouseup="onStageMouseUp" @wheel="onWheel">
            <v-layer ref="layerRef">
                <!-- 1) Fondo -->
                <v-image v-if="bgImage" :config="{ image: bgImage, width, height, listening: false }" />

                <!-- 2) SelectionBox para drag‑select -->
                <SelectionBox v-model="selectionBox" />

                <!-- 3) Shapes -->
                <template v-for="(s, i) in shapes" :key="i">
                    <component :is="shapeTag(s)" :config="shapeConfig(s, i)" :id="'shape-' + i"
                        :ref="el => shapeRefs[i] = el" @mousedown="onShapeMouseDown(i, $event)"
                        @dragend="onShapeDragEnd(i, $event)" @transformend.native="onShapeTransformEnd(i, $event)" />
                </template>

                <!-- 4) Capa de asientos - Esta data viene del archivos SeatsLayer-->

                <SeatsLayer ref="seatsLayerRef" :seats="seats" :defaultRadius="22" :isBackend="true"
                    @update:seats="onSeatsUpdate" @update:selection="onSeatSelection" />
                <!-- 5) Transformer único -->
                <v-transformer ref="transformerRef" @transformend="onTransformerTransformEnd" />

            </v-layer>
        </v-stage>

        <!-- Justo debajo del canvas -->
        <div v-if="popupSeat" :style="{
        position: 'fixed',
        left: popupPosition.x + 40 + 'px',
        top: popupPosition.y - 40 + 'px',
        zIndex: 9999,
        background: 'white',
        boxShadow: '0 4px 24px rgba(0,0,0,0.14)',
        borderRadius: '14px',
        padding: '22px 24px',
        minWidth: '260px',
        border: '1px solid #d1d5db',
        pointerEvents: 'auto',
        transition: 'all 0.16s cubic-bezier(.4,2,.8,1)'
    }" class="seat-popup" @mousedown.stop>
            <div style="font-weight: bold; font-size: 1.1rem; color: #6366f1; margin-bottom: 8px;">
                Asiento {{ popupSeat.label || popupSeat.id }}
            </div>
            <div>
                <b>Sector:</b> {{ popupSeat.sector || '—' }}<br>
                <b>Fila:</b> {{ popupSeat.row || '—' }}<br>
                <b>Número:</b> {{ popupSeat.number || '—' }}<br>
                <b>Precio:</b> ${{ popupSeat.price || '--' }}
            </div>
            <div style="margin-top: 18px; text-align: right;">
                <button @click="popupSeat = null"
                    style="padding: 8px 20px; border-radius: 8px; background: #7c3aed; color: white; border: none;">Cerrar</button>
            </div>
        </div>
    </div>
</template>

<script setup>
// valor de radio por defecto si seat.radius no existiera
const defaultRadius = 22

import { ref, nextTick, watch } from 'vue'
defineOptions({ inheritAttrs: false })
import SelectionBox from './SelectionBox.vue'
import SeatsLayer from './SeatsLayer.vue'

const popupSeat = ref(null)
const popupPosition = ref({ x: 0, y: 0 })
const canvasRef = ref(null)

function handleShowPopup({ seat, position }) {
    console.log('Recibiendo show-popup:', seat, position) // DEBUG
    popupSeat.value = seat
    popupPosition.value = position
}

// Opcional: cerrar el popup al hacer click fuera
import { onMounted, onBeforeUnmount } from 'vue'
onMounted(() => {
    document.addEventListener('mousedown', onClosePopup)
})
onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClosePopup)
})
function onClosePopup(e) {
    if (popupSeat.value && !e.target.closest('.seat-popup')) {
        popupSeat.value = null
    }
}

const props = defineProps({
    width: Number,
    height: Number,
    bgImage: Object,
    seats: Array,
    shapes: Array,
    panMode: Boolean,
    
})
const emit = defineEmits(['update:seats', 'update:shapes'])

// Exponé el método getStage
defineExpose({
    getStage: () => canvasRef.value?.getStage?.() || null
})

// refs a Stage/Layer/Transformer y SeatsLayer
const layerRef = ref(null)
const transformerRef = ref(null)
const seatsLayerRef = ref(null)

// refs para cada shape
const shapeRefs = ref([])

// selección por drag
const selectionBox = ref({ visible: false, x: 0, y: 0, width: 0, height: 0 })
let startPos = { x: 0, y: 0 }
const isDragging = ref(false)

// ——————————————
// 1) Handlers 
// ——————————————
function onStageMouseDown(e) {
    const stage = canvasRef.value.getNode()
    // si clic en el fondo...
    if (e.target === stage) {
        isDragging.value = true
        const pos = stage.getPointerPosition()
        startPos = { ...pos }
        // inicializo el SelectionBox
        selectionBox.value = {
            visible: true,
            x: pos.x,
            y: pos.y,
            width: 0,
            height: 0
        }
    }
}

function onStageMouseMove(e) {
    if (!isDragging.value) return
    const pos = e.target.getStage().getPointerPosition()
    selectionBox.value.width = pos.x - startPos.x
    selectionBox.value.height = pos.y - startPos.y
}

function onStageMouseUp(e) {
    if (!isDragging.value) return
    isDragging.value = false
    // oculto el rect
    selectionBox.value.visible = false
    // calculo la selección
    selectInBox(selectionBox.value)
}

function selectInBox({ x, y, width, height }) {
    const minX = Math.min(x, x + width)
    const maxX = Math.max(x, x + width)
    const minY = Math.min(y, y + height)
    const maxY = Math.max(y, y + height)

    // seats
    const updatedSeats = props.seats.map(seat => ({
        ...seat,
        selected:
            seat.x >= minX && seat.x <= maxX &&
            seat.y >= minY && seat.y <= maxY
    }))
    emit('update:seats', updatedSeats)

    // shapes (idéntica lógica)
    const updatedShapes = props.shapes.map(sh => {
        let sel = false
        if (sh.type === 'rect') {
            sel = sh.x + sh.width >= minX && sh.x <= maxX &&
                sh.y + sh.height >= minY && sh.y <= maxY
        } else {
            sel = sh.x >= minX && sh.x <= maxX &&
                sh.y >= minY && sh.y <= maxY
        }
        return { ...sh, selected: sel }
    })
    emit('update:shapes', updatedShapes)
}

/**
 * Zoom con rueda del mouse.
 */
function onWheel(e) {
    e.evt.preventDefault();             // evita el scroll de la página
    const stage = canvasRef.value.getStage();
    const oldScale = stage.scaleX();
    const pointer = stage.getPointerPosition(); // posición del mouse en coords del Stage

    // ajusta este factor a tu gusto (1.05 = 5% por notch)
    const scaleBy = 1.05;
    const newScale = e.evt.deltaY > 0
        ? oldScale / scaleBy
        : oldScale * scaleBy;

    stage.scale({ x: newScale, y: newScale });

    // Para que zoom se centre donde está el cursor:
    const mousePointTo = {
        x: (pointer.x - stage.x()) / oldScale,
        y: (pointer.y - stage.y()) / oldScale
    };
    stage.position({
        x: pointer.x - mousePointTo.x * newScale,
        y: pointer.y - mousePointTo.y * newScale
    });

    layerRef.value.getNode().batchDraw();
}


function clearTransformer() {
    const tr = transformerRef.value.getNode()
    tr.nodes([])
    layerRef.value.getNode().batchDraw()
}

// ——————————————
// 2) Configuración de tags y props de shapes
// ——————————————
function shapeTag(s) {
    return s.type === 'rect' ? 'v-rect'
        : s.type === 'circle' ? 'v-circle'
            : 'v-text'
}
function shapeConfig(s, i) {
    const sel = !!s.selected
    return {
        x: s.x, y: s.y,
        ...(s.type === 'rect'
            ? { width: s.width, height: s.height }
            : s.type === 'circle'
                ? { radius: s.radius ?? (s.width / 2) }
                : { text: s.label, fontSize: s.fontSize }
        ),
        stroke: sel ? 'blue' : 'gray',
        strokeWidth: 2,
        draggable: true
    }
}

// ——————————————
// 3) Handlers de shapes
// ——————————————
async function onShapeMouseDown(i, e) {
    e.cancelBubble = true
    // toggle o selección simple
    const updated = props.shapes.map((sh, idx) => ({
        ...sh,
        selected: e.shiftKey
            ? (idx === i ? !sh.selected : sh.selected)
            : (idx === i)
    }))
    // limpio seats
    const clearedSeats = props.seats.map(s => ({ ...s, selected: false }))
    emit('update:seats', clearedSeats)
    emit('update:shapes', updated)

    // engancho transformer sólo a este nodo
    await nextTick()
    const node = shapeRefs.value[i]?.getNode?.()
    if (node) {
        const tr = transformerRef.value.getNode()
        tr.nodes([node])
        tr.moveToTop()
        layerRef.value.getNode().batchDraw()
    }
}
function onShapeDragEnd(i, e) {
    const { x, y } = e.target.position()
    const updated = props.shapes.map((sh, idx) =>
        idx === i ? { ...sh, x, y, selected: sh.selected } : sh
    )
    emit('update:shapes', updated)
}
function onShapeTransformEnd(i, evt) {
    const node = evt.target
    const orig = props.shapes[i]
    const copy = { ...orig }
    if (orig.type === 'rect') {
        copy.width = node.width() * node.scaleX()
        copy.height = node.height() * node.scaleY()
    }
    if (orig.type === 'circle') {
        const newR = node.radius() * node.scaleX()
        copy.radius = newR
        copy.width = newR * 2    // ← ancho = diámetro
        copy.height = newR * 2    // ← alto  = diámetro
    }
    if (orig.type === 'text') {
        copy.fontSize = orig.fontSize * node.scaleX()
    }
    copy.rotation = node.rotation()
    node.scale({ x: 1, y: 1 })

    const updated = props.shapes.map((sh, idx) =>
        idx === i
            ? { ...copy, selected: true }
            : { ...sh, selected: false }
    )
    props.seats.forEach(s => s.selected = false)
    emit('update:seats', props.seats)
    emit('update:shapes', updated)
}

// ——————————————
// 4) Handlers de SeatsLayer
// ——————————————
function onSeatsUpdate(ns) {
    //console.log('🏷️ SeatMapView recibió update:seats →', ns)
    emit('update:seats', ns);

    // FORZÁ REDIBUJADO (solo si layerRef existe)
    if (layerRef.value && layerRef.value.getNode) {
        layerRef.value.getNode().batchDraw();
    }
}
async function onSeatSelection() {
    await nextTick();
    if (
        !layerRef.value || !layerRef.value.getNode ||
        !transformerRef.value || !transformerRef.value.getNode ||
        !seatsLayerRef.value
    ) return;

    const layer = layerRef.value.getNode();
    const tr = transformerRef.value.getNode();

    const shapeNodes = props.shapes
        .map((sh, i) => sh.selected ? layer.findOne('#shape-' + i) : null)
        .filter(Boolean);

    const seatNodes = seatsLayerRef.value.selectedCircleRefs || [];

    tr.nodes([...shapeNodes, ...seatNodes]);
    tr.moveToTop();
    layer.batchDraw();
}



// ——————————————
// 5) Watch global para shapes.selected O seats.selected
// ——————————————
watch(
    [
        () => props.shapes.map(s => s.selected),
        () => props.seats.map(s => s.selected)
    ],
    async () => {
        await nextTick();
        //console.log('Watcher disparado, seats:', props.seats.map(s => s.selected), 'shapes:', props.shapes.map(s => s.selected))
        if (
            !layerRef.value || !layerRef.value.getNode ||
            !transformerRef.value || !transformerRef.value.getNode ||
            !seatsLayerRef.value
        ) return;

        const layer = layerRef.value.getNode();
        const tr = transformerRef.value.getNode();

        const shapeNodes = props.shapes
            .map((_, i) =>
                props.shapes[i].selected
                    ? layer.findOne('#shape-' + i)
                    : null)
            .filter(Boolean);

        // Seat nodes: **sin**.value
        const seatNodes = seatsLayerRef.value.selectedCircleRefs || [];
        const nodes = [...shapeNodes, ...seatNodes];

        // 🚩🚩🚩
        // 💡 SI NO HAY NADA SELECCIONADO, forzá a limpiar SÍ o SÍ el transformer,
        // y luego un batchDraw para que Konva actualice el render.
        if (!nodes.length) {
            tr.nodes([]);
            tr.moveToTop();
            layer.batchDraw();
            return;
        }

        tr.nodes(nodes);
        tr.moveToTop();
        layer.batchDraw();
    },
    { immediate: true, flush: 'post' }
);





// —————————————— Hace el resize de los asientos y shapes
async function onTransformerTransformEnd() {
    const tr = transformerRef.value.getNode()
    // nodos que estamos transformando (Shapes y Circles)
    const nodes = tr.nodes()
    // Factor de escala (asumimos uniforme)
    const scaleX = tr.scaleX()
    const scaleY = tr.scaleY()
    // Copiamos el array actual de seats para actualizarlo
    const updatedSeats = props.seats.map(s => ({ ...s }))

    // Para cada nodo transformado, buscamos su asiento por id
    nodes.forEach(node => {
        const id = node.id();           // "seat-3", donde "3" es el PK de la tabla
        if (id?.startsWith('seat-')) {
            const pk = parseInt(id.split('-')[1], 10);
            // Buscamos el índice real en el array de props.seats:
            const seatIndex = props.seats.findIndex(s => s.id === pk);
            if (seatIndex === -1) return;       // si no lo encontramos, salimos
            const seat = updatedSeats[seatIndex];
            // Actualizamos radio y posición:
            seat.radius = (seat.radius ?? defaultRadius) * scaleX;
            seat.x = node.x();
            seat.y = node.y();
        }
    });


    // Resetear la escala del transformer
    tr.scale({ x: 1, y: 1 })
    // Re-enganchar los mismos nodos
    tr.nodes(nodes)
    // Redibujar la capa
    layerRef.value.getNode().batchDraw()

    // Emitir la actualización al padre
    emit('update:seats', updatedSeats)
}

</script>
