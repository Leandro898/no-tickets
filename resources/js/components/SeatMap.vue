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

            <!-- Canvas -->
            <div class="relative border rounded overflow-hidden bg-white">
                <SeatCanvas ref="canvasRef" :width="canvasW" :height="canvasH" :bg-image="bgImage" :seats="seats"
                    :pan-mode="spacePressed" @update:seats="seats = $event" @update:mapJSON="mapJSON = $event" />
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

                <!-- //PRUEBA DE BOTONES -->
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
import Toast from './Toast.vue'
import AddRowModal from './AddRowModal.vue'

const showAddRow = ref(false)
const sectors = ref([])    // los obtienes del API /eventos/:id/sectores

// unificamos el ref para el canvas
const canvasRef = ref(null)

// Props que vienen del blade
const props = defineProps({
    eventoId: { type: [Number, String], required: true },
    initialBgImageUrl: { type: String, default: '' }
})

// Estados y refs
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

// Carga inicial
onMounted(async () => {
    // 1) Preload del fondo si ya existía
    if (props.initialBgImageUrl) {
        const img = new Image()
        img.src = props.initialBgImageUrl
        await new Promise(r => (img.onload = r))
        bgImage.value = img
        bgImageUrl.value = props.initialBgImageUrl
    }

    // 2) Fetch de ENTRADAS (tickets)
    try {
        const res = await fetch(`/api/eventos/${props.eventoId}/entradas`)
        tickets.value = await res.json()
    } catch (e) {
        console.error('Error cargando tickets:', e)
    }

    // 3) Fetch de ASIENTOS guardados
    try {
        const res2 = await fetch(`/api/eventos/${props.eventoId}/asientos`)
        seats.value = await res2.json()
    } catch (e) {
        console.error('Error cargando asientos:', e)
    }

    // 4) Listener para usar la barra SPACE como modo pan
    window.addEventListener('keydown', e => {
        if (e.code === 'Space') spacePressed.value = true
    })
    window.addEventListener('keyup', e => {
        if (e.code === 'Space') spacePressed.value = false
    })
})

// — Abrir modal para agregar fila de butacas — //
function openModal() { showAddRow.value = true }

// — Evento de agregar fila de butacas — //
function onRowAdd({ sectorId, prefix, start, count }) {
    showAddRow.value = false

    // calculas posición inicial X/Y (por ejemplo, centrar o alinear abajo)
    const baseX = 100
    const baseY = 700

    for (let i = 0; i < count; i++) {
        const number = start + i
        seats.value.push({
            x: baseX + i * 50,       // separación horizontal 50px
            y: baseY,                // misma Y
            selected: false,
            entrada_id: sectorId,    // asocio asiento al sector/tipo
            row: prefix,
            number,
        })
    }
}

// — Imagen de fondo — //
function onBgLoaded(img) {
    bgImage.value = img
}
function onFileSelected(file) {
    selectedFile.value = file
    removedBg.value = false
}
function removeBg() {
    bgImage.value = null
    selectedFile.value = null
    if (bgImageUrl.value) removedBg.value = true
}

// — Agregar asiento centrado — //
function addSeat() {
    // 🔴 Antes: entrada_id: null
    // 🟢 Ahora asigna siempre un ID válido (p.ej. el primero de tickets)
    const defaultEntradaId = tickets.value.length
        ? tickets.value[0].id
        : null // o 1 si quieres forzar

    seats.value.push({
        x: canvasW / 2 - 20,
        y: canvasH / 2 - 20,
        selected: false,
        entrada_id: defaultEntradaId,  // 🔴 modificado aquí
        row: 0,
        number: 0
    })
}

// — Guardar TODO por AJAX — //
async function guardarTodo() {
    isLoading.value = true

    try {
        // 1) Borrar fondo viejo
        if (removedBg.value && bgImageUrl.value) {
            const del = await fetch(
                `/api/eventos/${props.eventoId}/delete-bg`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: bgImageUrl.value })
                }
            )
            if (!del.ok) throw new Error(`delete-bg ${del.status}`)
            bgImageUrl.value = ''
            removedBg.value = false
        }

        // 2) Subir nueva imagen
        if (selectedFile.value) {
            const fd = new FormData()
            fd.append('image', selectedFile.value)
            const up = await fetch(
                `/api/eventos/${props.eventoId}/upload-bg`,
                {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: fd
                }
            )
            if (!up.ok) throw new Error(`upload-bg ${up.status}`)
            const j = await up.json()
            bgImageUrl.value = j.url
        }

        // 3) Asegurar JSON del mapa
        if (!mapJSON.value && canvasRef.value) {
            mapJSON.value = canvasRef.value.getStage().toJSON()
        }

        // ————————————————
        // 4) CORRECCIÓN: asegurarnos de que cada asiento tenga un entrada_id válido
        // (e.g. tomamos el primer ticket de tickets.value si existe)
        const defaultEntradaId = tickets.value.length
            ? tickets.value[0].id
            : null

        const sanitizedSeats = toRaw(seats.value).map(s => ({
            ...s,
            entrada_id: s.entrada_id ?? defaultEntradaId
        }))
        // ————————————————

        // 5) Preparar payload
        const payload = {
            seats: sanitizedSeats,
            bgUrl: bgImageUrl.value,
            map: mapJSON.value
        }
        console.log('📤 Payload /mapa:', payload)

        // 6) Guardar todo junto
        const res = await fetch(
            `/api/eventos/${props.eventoId}/mapa`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            }
        )
        if (!res.ok) {
            const txt = await res.text()
            console.error('Error save-map:', txt)
            throw new Error(`save-map ${res.status}`)
        }
        const data = await res.json()
        if (data.status === 'ok') {
            toast.value = { visible: true, message: 'Guardado correctamente', type: 'success' }
        } else {
            toast.value = { visible: true, message: 'Error al guardar', type: 'error' }
        }

    } catch (err) {
        console.error(err)
        toast.value = { visible: true, message: 'Error de red, revisá consola', type: 'error' }
    } finally {
        isLoading.value = false
        setTimeout(() => (toast.value.visible = false), 2500)
    }
}

</script>

<style scoped>
/* ajusta tu layout aquí si lo necesitas */
</style>
