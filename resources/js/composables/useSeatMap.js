// resources/js/composables/useSeatMap.js
import { ref, onMounted, toRaw, watch } from 'vue'
import { useTickets } from '@/composables/useTickets'

export function useSeatMap(eventoId, initialBgImageUrl) {
    // 1) State
    const tools = [
        { name: 'rect', label: 'Agregar cuadrado', icon: '…' },
        { name: 'circle', label: 'Agregar círculo', icon: '…' },
        { name: 'text', label: 'Agregar texto', icon: '…' },
    ]
    const canvasRef = ref(null)
    const seats = ref([])
    const canvasW = ref(1000)
    const canvasH = ref(800)
    const bgImage = ref(null)
    const bgImageUrl = ref('')
    const selectedFile = ref(null)
    const removedBg = ref(false)
    const mapJSON = ref(null)
    const currentTool = ref('rect')
    const spacePressed = ref(false)
    const isLoading = ref(false)
    const toast = ref({ visible: false, message: '', type: 'success' })
    const history = ref([])
    const future = ref([])

    // Add‑row modal
    const showAddRow = ref(false)
    const sectors = ref([])

    // 2) Tickets (para asignar entrada_id por defecto)
    const { tickets, totalTickets } = useTickets(eventoId)

    // 3) Lifecycle: load bg + existing map
    onMounted(async () => {
        // 1️⃣ Background inicial
        if (initialBgImageUrl) {
            const img = new Image()
            img.src = initialBgImageUrl
            await new Promise(r => (img.onload = r))
            bgImage.value = img
            bgImageUrl.value = initialBgImageUrl
        }

        // 2️⃣ Validación de eventoId
        if (!eventoId) {
            console.error('useSeatMap: falta el eventoId')
            return
        }

        // 3️⃣ Carga del mapa existente
        const res = await fetch(`/api/eventos/${eventoId}/map`)
        if (!res.ok) {
            console.error(`Error al cargar el mapa: ${res.status}`)
            return
        }
        const { seats: rawSeats, bgUrl, map } = await res.json()

        // 4️⃣ Si viene bgUrl, lo cargamos
        if (bgUrl) {
            const img2 = new Image()
            img2.src = bgUrl
            await new Promise(r => (img2.onload = r))
            bgImage.value = img2
            bgImageUrl.value = bgUrl
        }

        // 5️⃣ Guardamos el JSON crudo del canvas
        mapJSON.value = map

        // 6️⃣ Asignamos un entrada_id por defecto a cada asiento
        const defaultEntradaId = tickets.value[0]?.id
        seats.value = rawSeats.map(s => ({
            ...s,
            entrada_id: s.entrada_id ?? defaultEntradaId, // ← aquí
            selected: false,
            radius: s.radius ?? 22,
            label: s.label ?? '',
            fontSize: s.font_size ?? 18,
            type: s.type ?? 'seat',
            width: s.width ?? null,
            height: s.height ?? null,
            draggable: true,
            rotation: s.rotation ?? 0,
        }))
    })


    // 4) SPACE pan
    window.addEventListener('keydown', e => {
        if (e.code === 'Space') spacePressed.value = true
    })
    window.addEventListener('keyup', e => {
        if (e.code === 'Space') spacePressed.value = false
    })

    // 5) Toolbar actions
    function onToolSelect(name) {
        currentTool.value = name
        if (name === 'rect') addRectangle()
        if (name === 'circle') addCircle()
        if (name === 'text') addText()
    }
    function addRectangle() {
        seats.value.push({
            type: 'rect',
            x: canvasW.value / 2 - 25,
            y: canvasH.value / 2 - 15,
            width: 50,
            height: 30,
            stroke: 'gray',
            strokeWidth: 2,
            selected: false,
        })
    }
    function addCircle() {
        seats.value.push({
            type: 'circle',
            x: canvasW.value / 2,
            y: canvasH.value / 2,
            radius: 30,
            stroke: 'gray',
            strokeWidth: 2,
            selected: false,
        })
    }
    function addText() {
        const t = prompt('Ingresa texto:')
        if (!t) return
        seats.value.push({
            type: 'text',
            x: canvasW.value / 2,
            y: canvasH.value / 2,
            label: t,
            fontSize: 18,
            draggable: true,
            selected: false,
        })
    }

    // 6) On seats update (drag/selection)
    function onSeatsUpdate(newSeats) {
        seats.value = (newSeats || []).map(s => ({
            ...s,
            selected: !!s.selected,
            radius: s.radius ?? 22,
            label: s.label || '',
            fontSize: s.fontSize || 18,
        }))
    }

    // 7) Rename (SeatControls)
    function onRename({ type, label, letter, start }) {
        const sel = seats.value.filter(s => s.selected)
        if (!sel.length) return
        if (type === 'single') {
            sel[0].label = label
        } else {
            sel.sort((a, b) => a.x - b.x)
                .forEach((s, i) => { s.label = `${letter}${start + i}` })
        }
    }

    // 8) Undo/redo/history
    watch(seats, s => {
        history.value.push(JSON.parse(JSON.stringify(s)))
        if (history.value.length > 50) history.value.shift()
        future.value = []
    }, { deep: true, immediate: true })
    function undo() {
        if (history.value.length < 2) return
        const last = history.value.pop()
        future.value.unshift(last)
        seats.value = JSON.parse(JSON.stringify(history.value.at(-1)))
    }
    function redo() {
        if (!future.value.length) return
        const next = future.value.shift()
        seats.value = JSON.parse(JSON.stringify(next))
        history.value.push(next)
    }
    function toggleSelectAll() {
        const all = seats.value.every(s => s.selected)
        seats.value = seats.value.map(s => ({ ...s, selected: !all }))
    }
    function deleteSelected() {
        seats.value = seats.value.filter(s => !s.selected)
    }

    // 9) Zoom & pan helpers
    function zoomIn() {
        const stage = canvasRef.value.getStage()
        stage.scale({ x: stage.scaleX() * 1.2, y: stage.scaleY() * 1.2 })
        stage.batchDraw()
    }
    function zoomOut() {
        const stage = canvasRef.value.getStage()
        stage.scale({ x: stage.scaleX() / 1.2, y: stage.scaleY() / 1.2 })
        stage.batchDraw()
    }
    function resetView() {
        const stage = canvasRef.value.getStage()
        stage.scale({ x: 1, y: 1 })
        stage.position({ x: 0, y: 0 })
        stage.batchDraw()
    }
    function showHelp() {
        alert('Usa la rueda para zoom y la barra espaciadora para pan')
    }

    // 10) Free‑hand add seat (siempre con entrada_id válido)
    function addSeat() {
        if (!tickets.value.length) {
            toast.value = {
                visible: true,
                message: 'No hay tipos de ticket configurados.',
                type: 'error'
            }
            return
        }
        const defaultEntradaId = tickets.value[0].id
        seats.value.push({
            type: 'seat',
            x: canvasW.value / 2 - 20,
            y: canvasH.value / 2 - 20,
            entrada_id: defaultEntradaId,
            label: '',
            row: '',
            number: 0,
            draggable: true,
            rotation: 0,
            selected: false,
        })
    }

    // 11) Guardar todo (mapa + asientos)
    async function guardarTodo() {
        // 1️⃣ Filtrar solo los asientos reales
        const onlySeats = seats.value.filter(s => s.type === 'seat')

        // 2️⃣ Validación previa: ningún asiento puede venir sin entrada_id
        const faltan = onlySeats.some(s => !s.entrada_id)
        if (faltan) {
            toast.value = {
                visible: true,
                message: 'Hay uno o más asientos sin tipo de entrada.',
                type: 'error'
            }
            return
        }

        isLoading.value = true
        try {
            // Asegura el JSON del canvas si hace falta
            if (!mapJSON.value && canvasRef.value) {
                mapJSON.value = canvasRef.value.getStage().toJSON()
            }

            // 3️⃣ Payload con sólo los seats filtrados
            const payload = {
                seats: toRaw(onlySeats),
                bgUrl: bgImageUrl.value,
                map: mapJSON.value,
            }

            const res = await fetch(`/api/eventos/${eventoId}/mapa`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })

            if (!res.ok) {
                throw new Error(`Error guardando: ${res.status}`)
            }

            toast.value = {
                visible: true,
                message: 'Mapa guardado correctamente ✅',
                type: 'success'
            }
        }
        catch (err) {
            console.error(err)
            toast.value = {
                visible: true,
                message: 'Error al guardar el mapa 😢',
                type: 'error'
            }
        }
        finally {
            isLoading.value = false
            setTimeout(() => {
                toast.value.visible = false
            }, 2000)
        }
    }


    // 12) Add‑row modal
    function openAddRowModal() { showAddRow.value = true }
    function onRowAdd({ sectorId, prefix, start, count }) {
        showAddRow.value = false
        for (let i = 0; i < count; i++) {
            seats.value.push({
                x: 100 + i * 50,
                y: 700,
                entrada_id: sectorId,
                row: prefix,
                number: start + i,
                label: `${prefix}${start + i}`,
                selected: false,
            })
        }
    }

    // 13) BG uploader
    function onBgLoaded(img) { bgImage.value = img }
    function onFileSelected(f) { selectedFile.value = f; removedBg.value = false }
    function removeBg() { bgImage.value = null; selectedFile.value = null; removedBg.value = true }

    return {
        tools,
        canvasRef, seats, canvasW, canvasH, bgImage,
        mapJSON, currentTool, spacePressed, isLoading, toast,
        onToolSelect, onBgLoaded, onFileSelected, removeBg,
        addSeat, guardarTodo, onSeatsUpdate,
        history, future, toggleSelectAll, undo, redo,
        zoomIn, zoomOut, resetView, deleteSelected, showHelp,
        showAddRow, openAddRowModal, sectors, onRowAdd,
        onRename,
    }
}
