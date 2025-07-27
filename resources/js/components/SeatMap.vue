<template>
    <div class="flex h-full">
        <!-- Sidebar de herramientas -->
        <SidebarToolbar :tools="[
            { name: 'select', label: 'Seleccionar zona', icon: '<svg>…</svg>' },
            { name: 'seat', label: 'Agregar asiento', icon: '<svg>…</svg>' },
            { name: 'text', label: 'Texto', icon: '<svg>…</svg>' }
        ]" :active="currentTool" @select="t => currentTool = t" class="shrink-0" />

        <!-- Área principal -->
        <div class="flex-1 p-4 bg-gray-50 overflow-auto">
            <!-- Toast -->
            <Toast :visible="toast.visible" :message="toast.message" :type="toast.type"
                @close="toast.visible = false" />

            <!-- Uploader + Quitar fondo -->
            <div class="flex items-center gap-2 mb-4">
                <ImageUploader :eventoId="props.eventoId" @imageLoaded="onBgLoaded" @fileSelected="onFileSelected" />
                <button v-if="bgImage" @click="removeBg"
                    class="px-4 py-2 bg-gray-100 border rounded hover:bg-red-100 hover:text-red-700">
                    Quitar imagen
                </button>
            </div>

            <!-- Canvas + Controls -->
            <div class="relative flex border rounded overflow-hidden bg-white">
                <!-- 1) El lienzo ocupa todo el espacio disponible -->
                <div class="flex-1">
                    <SeatCanvas ref="canvasRef" :width="canvasW" :height="canvasH" :bg-image="bgImage" :seats="seats"
                        @update:seats="onSeatsUpdate" :pan-mode="spacePressed" @update:mapJSON="mapJSON = $event"
                        class="w-full h-full" />
                </div>

                <!-- 2) Panel de labels posicionado por encima a la derecha -->
                <SeatControls v-show="seats.some(s => s.selected)" :selected="seats.filter(s => s.selected)"
                    @rename="onRename" class="absolute top-0 right-0 h-full w-64 bg-white shadow-lg z-20" />
            </div>



            <!-- Botones -->
            <div class="mt-4 flex gap-2">
                <button class="px-4 py-2 bg-purple-600 text-white rounded" @click="addSeat" :disabled="isLoading">
                    Agregar asiento
                </button>

                <button class="px-4 py-2 bg-green-600 text-white rounded flex items-center" @click="guardarTodo"
                    :disabled="isLoading">
                    <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z" />
                    </svg>
                    {{ isLoading ? 'Guardando…' : 'Guardar todo' }}
                </button>

                <button @click="openModal" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    Agregar fila de butacas
                </button>

                <AddRowModal v-if="showAddRow" :sectors="sectors" @add="onRowAdd" @cancel="showAddRow = false" />
            </div>
        </div>
    </div>
</template>


<script setup>
import { ref, onMounted, toRaw } from 'vue'
import ImageUploader from './ImageUploader.vue'
import SidebarToolbar from './SidebarToolbar.vue'
import SeatCanvas from './SeatCanvas.vue'
import SeatControls from './SeatControls.vue'
import Toast from './Toast.vue'
import AddRowModal from './AddRowModal.vue'

// Props
const props = defineProps({
    eventoId: { type: [Number, String], required: true },
    initialBgImageUrl: { type: String, default: '' }
})

// Refs y estados
const showAddRow = ref(false)
const sectors = ref([])
const canvasRef = ref(null)
const tickets = ref([])
const canvasW = 1000
const canvasH = 800
const bgImage = ref(null)
const selectedFile = ref(null)
const bgImageUrl = ref('')
const removedBg = ref(false)
const seats = ref([])
const mapJSON = ref(null)
const currentTool = ref('select')
const spacePressed = ref(false)
const isLoading = ref(false)
const toast = ref({ visible: false, message: '', type: 'success' })

// Montaje inicial
onMounted(async () => {
    // Fondo previo
    if (props.initialBgImageUrl) {
        const img = new Image()
        img.src = props.initialBgImageUrl
        await new Promise(r => (img.onload = r))
        bgImage.value = img
        bgImageUrl.value = props.initialBgImageUrl
    }

    // Traer tickets
    try {
        const res = await fetch(`/api/eventos/${props.eventoId}/entradas`)
        tickets.value = await res.json()
    } catch (e) {
        console.error('Error cargando tickets:', e)
    }

    // Traer asientos guardados
    try {
        const res2 = await fetch(`/api/eventos/${props.eventoId}/asientos`)
        const raw = await res2.json()
        seats.value = raw.map(s => ({
            ...s,
            selected: false,
            radius: s.radius ?? 22,
            label: s.label ?? `${s.row}${s.number}`
        }))
    } catch (e) {
        console.error('Error cargando asientos:', e)
    }

    // SPACE para pan
    window.addEventListener('keydown', e => {
        if (e.code === 'Space') spacePressed.value = true
    })
    window.addEventListener('keyup', e => {
        if (e.code === 'Space') spacePressed.value = false
    })
})

// — Modal fila de butacas —
function openModal() { showAddRow.value = true }

function onRowAdd({ sectorId, prefix, start, count }) {
    showAddRow.value = false
    const baseX = 100, baseY = 700
    for (let i = 0; i < count; i++) {
        const num = start + i
        seats.value.push({
            x: baseX + i * 50,
            y: baseY,
            selected: false,
            entrada_id: sectorId,
            row: prefix,
            number: num,
            label: `${prefix}${num}`
        })
    }
}

// — Fondo —
function onBgLoaded(img) { bgImage.value = img }
function onFileSelected(file) {
    selectedFile.value = file
    removedBg.value = false
}
function removeBg() {
    bgImage.value = null
    selectedFile.value = null
    if (bgImageUrl.value) removedBg.value = true
}

// — Agregar asiento —
function addSeat() {
    const defaultEntradaId = tickets.value.length
        ? tickets.value[0].id
        : null
    seats.value.push({
        x: canvasW / 2 - 20,
        y: canvasH / 2 - 20,
        selected: false,
        entrada_id: defaultEntradaId,
        row: 0,
        number: 0,
        label: 'A1'
    })
}

// — Guardar todo —
async function guardarTodo() {
    isLoading.value = true
    try {
        // (1) Borrar fondo viejo
        if (removedBg.value && bgImageUrl.value) {
            const del = await fetch(`/api/eventos/${props.eventoId}/delete-bg`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: bgImageUrl.value })
            })
            if (!del.ok) throw new Error(`delete-bg ${del.status}`)
            bgImageUrl.value = ''
            removedBg.value = false
        }

        // (2) Subir nuevo
        if (selectedFile.value) {
            const fd = new FormData()
            fd.append('image', selectedFile.value)
            const up = await fetch(`/api/eventos/${props.eventoId}/upload-bg`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd
            })
            if (!up.ok) throw new Error(`upload-bg ${up.status}`)
            const j = await up.json()
            bgImageUrl.value = j.url
        }

        // (3) JSON mapa
        if (!mapJSON.value && canvasRef.value) {
            mapJSON.value = canvasRef.value.getStage().toJSON()
        }

        // (4) Sanitizar asientos
        const defaultEntradaId = tickets.value.length
            ? tickets.value[0].id
            : null
        const sanitizedSeats = toRaw(seats.value).map(s => ({
            ...s,
            entrada_id: s.entrada_id ?? defaultEntradaId,
            radius: s.radius ?? 22
        }))

        // (5) Payload y envío
        const payload = {
            seats: sanitizedSeats,
            bgUrl: bgImageUrl.value,
            map: mapJSON.value
        }
        const res = await fetch(`/api/eventos/${props.eventoId}/mapa`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        if (!res.ok) throw new Error(`save-map ${res.status}`)
        const data = await res.json()
        toast.value = {
            visible: true,
            message: data.status === 'ok'
                ? 'Guardado correctamente'
                : 'Error al guardar',
            type: data.status === 'ok' ? 'success' : 'error'
        }

    } catch (err) {
        console.error(err)
        toast.value = {
            visible: true,
            message: 'Error de red, revisá consola',
            type: 'error'
        }
    } finally {
        isLoading.value = false
        setTimeout(() => (toast.value.visible = false), 2500)
    }
}

// — Renombrar labels (SeatControls) —
function onRename({ type, label, letter, start }) {
    const sel = seats.value.filter(s => s.selected)
    if (type === 'single') {
        sel[0].label = label
    } else {
        sel
            .sort((a, b) => a.x - b.x)
            .forEach((s, i) => {
                s.label = `${letter}${start + i}`
            })
    }
}

// — Actualizar asientos (SeatCanvas) —
function onSeatsUpdate(newSeats) {
    // console.log('🚀 seats.selected:', newSeats.map(s => s.selected))
    seats.value = newSeats
}
</script>


<style scoped>
/* ajusta tu layout aquí si lo necesitas */
</style>
