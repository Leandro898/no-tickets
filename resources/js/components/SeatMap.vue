<template>
    <!-- TOAST -->
    <transition name="fade">
        <div v-if="toast.visible" class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg">
            {{ toast.message }}
        </div>
    </transition>

    <div>
        <!-- uploader + botón quitar -->
        <div class="flex gap-2 mb-4">
            <ImageUploader :eventoId="props.eventoId" @imageLoaded="setBgImage" @fileSelected="onFileSelected" />
            <button v-if="bgImage" @click="removeBgImage"
                class="px-4 py-2 bg-gray-100 border text-gray-700 rounded hover:bg-red-100 hover:text-red-700"
                type="button">
                Quitar imagen
            </button>
        </div>

        <!-- canvas Konva -->
        <v-stage :config="{ width, height }" @wheel="onWheel" @mousedown="onMouseDown" @mousemove="onMouseMove"
            @mouseup="onMouseUp" @mouseleave="onMouseUp">
            <v-layer>
                <!-- fondo -->
                <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />
                <!-- selección -->
                <v-rect v-if="selectionBox.visible" :config="{
                    x: selectionBox.x,
                    y: selectionBox.y,
                    width: selectionBox.width,
                    height: selectionBox.height,
                    fill: 'rgba(60, 120, 255, 0.2)',
                    stroke: 'rgba(60, 120, 255, 0.5)',
                    dash: [4, 4]
                }" />
                <!-- asientos -->
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

        <!-- controles -->
        <button class="mt-4 px-4 py-2 bg-purple-600 text-white rounded" @click="addSeat" :disabled="isLoading">
            Agregar asiento
        </button>
        <button class="mt-4 px-4 py-2 bg-green-600 text-white rounded flex items-center justify-center"
            @click="guardarTodo" :disabled="isLoading">
            <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z" />
            </svg>
            <span>{{ isLoading ? 'Guardando…' : 'Guardar todo' }}</span>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import ImageUploader from './ImageUploader.vue'

// — Props desde Blade
const props = defineProps({
    eventoId: { type: [Number, String], required: true },
    initialBgImageUrl: { type: String, default: '' }
})

// — Estados & refs
const bgImage = ref(null)
const selectedFile = ref(null)
const bgImageUrl = ref('')
const removedBg = ref(false)

const isLoading = ref(false)
const toast = ref({ visible: false, message: '' })

// — canvas y asientos
const width = 800
const height = 400
const seats = ref([
    { x: 100, y: 100, selected: false },
    { x: 200, y: 100, selected: false }
])

// — Preload de fondo guardado
onMounted(() => {
    if (props.initialBgImageUrl) {
        const img = new window.Image()
        img.src = props.initialBgImageUrl
        img.onload = () => {
            bgImage.value = img
            bgImageUrl.value = props.initialBgImageUrl
        }
    }
    window.addEventListener('keydown', handleKeyDown)
    window.addEventListener('keyup', handleKeyUp)
})
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown)
    window.removeEventListener('keyup', handleKeyUp)
})

// — IMAGEN: set, select, remove
function setBgImage(img) {
    bgImage.value = img
}
function onFileSelected(file) {
    selectedFile.value = file
    removedBg.value = false
}
function removeBgImage() {
    bgImage.value = null
    selectedFile.value = null
    if (bgImageUrl.value) removedBg.value = true
}

// — GUARDAR TODO (1 delete-bg, 2 upload-bg, 3 asientos)
async function guardarTodo() {
    isLoading.value = true;

    try {
        // 1️⃣ delete-bg
        if (removedBg.value && bgImageUrl.value) {
            await fetch(`/api/eventos/${props.eventoId}/delete-bg`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: bgImageUrl.value }),
            });
            bgImageUrl.value = '';
            removedBg.value = false;
        }

        // 2️⃣ upload-bg
        if (selectedFile.value) {
            const fd = new FormData();
            fd.append('image', selectedFile.value);
            const resImg = await fetch(`/api/eventos/${props.eventoId}/upload-bg`, {
                method: 'POST',
                body: fd,
            });
            const imgData = await resImg.json();
            bgImageUrl.value = imgData.url;
        }

        // 3️⃣ asientos
        const resSeats = await fetch(`/api/eventos/${props.eventoId}/asientos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ seats: seats.value }),
        });

        if (!resSeats.ok) {
            throw new Error(`HTTP ${resSeats.status}`);
        }
        const data = await resSeats.json();

        // Mostrar toast según resultado
        if (data.status === 'ok') {
            window.toastr.success('¡Asientos guardados correctamente!', 'Éxito');
        } else {
            window.toastr.error('Error al guardar asientos', 'Error');
        }
    } catch (err) {
        console.error(err);
        window.toastr.error('Error al guardar. Revisá la consola.', 'Error');
    } finally {
        isLoading.value = false;
    }
}


// — ASIENTOS: agregar / arrastrar / seleccionar
function addSeat() {
    seats.value.push({ x: 150, y: 150, selected: false })
}
function onDragMove(i, e) {
    const p = e.target.position()
    seats.value[i].x = p.x
    seats.value[i].y = p.y
}
function toggleSelect(i) {
    seats.value[i].selected = !seats.value[i].selected
}

// — ZOOM al cursor
function onWheel(e) {
    e.evt.preventDefault()
    const stage = e.target.getStage()
    const oldSc = stage.scaleX()
    const ptr = stage.getPointerPosition()
    const scaleB = 1.07
    const dir = e.evt.deltaY > 0 ? 1 : -1
    const newSc = dir > 0 ? oldSc / scaleB : oldSc * scaleB
    const limSc = Math.max(0.3, Math.min(newSc, 3))
    const mp = {
        x: (ptr.x - stage.x()) / oldSc,
        y: (ptr.y - stage.y()) / oldSc
    }
    stage.scale({ x: limSc, y: limSc })
    stage.position({
        x: ptr.x - mp.x * limSc,
        y: ptr.y - mp.y * limSc
    })
    stage.batchDraw()
}

// — PAN con Space
let isPanning = false
let lastPtr = { x: 0, y: 0 }
const spacePressed = ref(false)
function handleKeyDown(e) { if (e.code === 'Space') spacePressed.value = true }
function handleKeyUp(e) { if (e.code === 'Space') spacePressed.value = false }
watch(spacePressed, v => document.body.style.cursor = v ? 'grab' : '')

// — CUADRO de selección
const selectionBox = ref({ visible: false, x: 0, y: 0, width: 0, height: 0 })
let selectionStart = { x: 0, y: 0 }

function onMouseDown(e) {
    if (spacePressed.value) {
        isPanning = true
        lastPtr = e.target.getStage().getPointerPosition()
        return
    }
    if (e.target.getClassName() === 'Circle') return
    if (
        e.target === e.target.getStage() ||
        ['Layer', 'Image'].includes(e.target.getClassName())
    ) {
        const p = e.target.getStage().getPointerPosition()
        selectionStart = { x: p.x, y: p.y }
        selectionBox.value = { visible: true, x: p.x, y: p.y, width: 0, height: 0 }
    }
}

function onMouseMove(e) {
    if (isPanning) {
        const st = e.target.getStage(),
            p = st.getPointerPosition()
        st.x(st.x() + (p.x - lastPtr.x))
        st.y(st.y() + (p.y - lastPtr.y))
        st.batchDraw()
        lastPtr = p
        return
    }
    if (!selectionBox.value.visible) return
    const p = e.target.getStage().getPointerPosition()
    selectionBox.value = {
        visible: true,
        x: Math.min(p.x, selectionStart.x),
        y: Math.min(p.y, selectionStart.y),
        width: Math.abs(p.x - selectionStart.x),
        height: Math.abs(p.y - selectionStart.y)
    }
}

function onMouseUp() {
    if (isPanning) {
        isPanning = false
        return
    }
    if (!selectionBox.value.visible) return
    seats.value.forEach(s => {
        s.selected =
            s.x >= selectionBox.value.x &&
            s.x <= selectionBox.value.x + selectionBox.value.width &&
            s.y >= selectionBox.value.y &&
            s.y <= selectionBox.value.y + selectionBox.value.height
    })
    selectionBox.value.visible = false
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .3s
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0
}
</style>
