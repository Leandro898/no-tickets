<template>
    <div>
        <!-- Toolbar -->
        <div class="flex items-center gap-4 px-6 pt-6 pb-4 bg-white shadow rounded-t-lg"
            style="border-bottom: 1px solid #eee;">
            <ImageUploader @imageLoaded="setBgImage" />
            <button v-if="bgImage" @click="removeBgImage"
                class="px-4 py-2 bg-gray-100 border text-gray-700 rounded hover:bg-red-100 hover:text-red-700"
                type="button">
                Remove image
            </button>
        </div>

        <!-- Lienzo -->
        <div class="bg-gray-100 p-8 flex justify-center items-center rounded-b-lg">
            <v-stage :config="{ width, height }" @wheel="onWheel" @mousedown="onMouseDown" @mousemove="onMouseMove"
                @mouseup="onMouseUp" @mouseleave="onMouseUp"
                style="background: white; border-radius: 8px; box-shadow: 0 2px 12px #0001;">
                <v-layer>
                    <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />
                    <v-rect v-if="selectionBox.visible" :config="{
                        x: selectionBox.x,
                        y: selectionBox.y,
                        width: selectionBox.width,
                        height: selectionBox.height,
                        fill: 'rgba(60, 120, 255, 0.2)',
                        stroke: 'rgba(60, 120, 255, 0.5)',
                        dash: [4, 4]
                    }" />
                    <v-circle v-for="(seat, i) in seats" :key="i" :config="{
                        x: seat.x,
                        y: seat.y,
                        radius: 22,
                        fill: seat.selected ? '#7c3aed' : '#e5e7eb',
                        stroke: '#7c3aed',
                        strokeWidth: 2,
                        draggable: true
                    }" @dragmove="onDragMove(i, $event)" @click="toggleSelect(i)" />
                </v-layer>
            </v-stage>
        </div>

        <!-- Botón agregar asiento -->
        <div class="flex justify-end mt-6">
            <button class="px-6 py-2 bg-purple-600 text-white rounded shadow hover:bg-purple-700 transition"
                @click="addSeat">
                Agregar asiento
            </button>
        </div>
    </div>
</template>


<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import ImageUploader from './ImageUploader.vue'

const width = 800
const height = 400
const seats = ref([
    { x: 100, y: 100, selected: false },
    { x: 200, y: 100, selected: false }
])
const bgImage = ref(null)

function setBgImage(img) {
    bgImage.value = img
}
function removeBgImage() {
    bgImage.value = null
}
function addSeat() {
    seats.value.push({ x: 150, y: 150, selected: false })
}
function onDragMove(i, e) {
    const pos = e.target.position()
    seats.value[i].x = pos.x
    seats.value[i].y = pos.y
}
function toggleSelect(i) {
    seats.value[i].selected = !seats.value[i].selected
}

// ZOOM "TO CURSOR"
function onWheel(e) {
    e.evt.preventDefault()
    const stage = e.target.getStage()
    const oldScale = stage.scaleX()
    const pointer = stage.getPointerPosition()
    const scaleBy = 1.07
    const direction = e.evt.deltaY > 0 ? 1 : -1
    const newScale = direction > 0 ? oldScale / scaleBy : oldScale * scaleBy
    const limitedScale = Math.max(0.3, Math.min(newScale, 3))
    const mousePointTo = {
        x: (pointer.x - stage.x()) / oldScale,
        y: (pointer.y - stage.y()) / oldScale,
    }
    stage.scale({ x: limitedScale, y: limitedScale })
    const newPos = {
        x: pointer.x - mousePointTo.x * limitedScale,
        y: pointer.y - mousePointTo.y * limitedScale,
    }
    stage.position(newPos)
    stage.batchDraw()
}

// ---- PAN CON BARRA ESPACIADORA ----
let isPanning = false
let lastPointer = { x: 0, y: 0 }
const spacePressed = ref(false)
onMounted(() => {
    window.addEventListener('keydown', handleKeyDown)
    window.addEventListener('keyup', handleKeyUp)
})
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown)
    window.removeEventListener('keyup', handleKeyUp)
})
function handleKeyDown(e) {
    if (e.code === 'Space') spacePressed.value = true
}
function handleKeyUp(e) {
    if (e.code === 'Space') spacePressed.value = false
}
// Mejora UX: cambia el cursor cuando está en pan mode
watch(spacePressed, (val) => {
    document.body.style.cursor = val ? 'grab' : ''
})

// CUADRO DE SELECCION
const selectionBox = ref({
    visible: false,
    x: 0, y: 0, width: 0, height: 0
})
let selectionStart = { x: 0, y: 0 }
function onMouseDown(e) {
    if (spacePressed.value) {
        isPanning = true
        const stage = e.target.getStage()
        lastPointer = stage.getPointerPosition()
        return
    }
    if (e.target.getClassName() === "Circle") {
        return
    }
    if (
        e.target === e.target.getStage() ||
        e.target.getClassName() === "Layer" ||
        e.target.getClassName() === "Image"
    ) {
        const pointer = e.target.getStage().getPointerPosition()
        selectionStart = { x: pointer.x, y: pointer.y }
        selectionBox.value = {
            visible: true,
            x: pointer.x,
            y: pointer.y,
            width: 0,
            height: 0
        }
    }
}
function onMouseMove(e) {
    if (isPanning) {
        const stage = e.target.getStage()
        const pointer = stage.getPointerPosition()
        const dx = pointer.x - lastPointer.x
        const dy = pointer.y - lastPointer.y
        stage.x(stage.x() + dx)
        stage.y(stage.y() + dy)
        stage.batchDraw()
        lastPointer = pointer
        return
    }
    if (!selectionBox.value.visible) return
    const pointer = e.target.getStage().getPointerPosition()
    const x = Math.min(pointer.x, selectionStart.x)
    const y = Math.min(pointer.y, selectionStart.y)
    const width = Math.abs(pointer.x - selectionStart.x)
    const height = Math.abs(pointer.y - selectionStart.y)
    selectionBox.value = {
        visible: true,
        x, y, width, height
    }
}
function onMouseUp(e) {
    if (isPanning) {
        isPanning = false
        return
    }
    if (selectionBox.value.visible) {
        seats.value.forEach(seat => {
            seat.selected =
                seat.x >= selectionBox.value.x &&
                seat.x <= selectionBox.value.x + selectionBox.value.width &&
                seat.y >= selectionBox.value.y &&
                seat.y <= selectionBox.value.y + selectionBox.value.height
        })
        selectionBox.value.visible = false
    }
}
</script>
