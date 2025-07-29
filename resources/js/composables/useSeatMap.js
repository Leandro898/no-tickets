// resources/js/composables/useSeatMap.js
import { ref, onMounted, toRaw, watch } from 'vue'
import { useTickets } from '@/composables/useTickets'

export function useSeatMap(eventoId, initialBgImageUrl) {
    // ─── 1) STATE ─────────────────────────────────────────────────────────────
    const canvasRef = ref(null)
    const seats = ref([])
    const shapes = ref([])          // ← aquí guardaremos los shapes

    const tools = [
        { name: 'rect', label: 'Agregar cuadrado', icon: '⬛' },
        { name: 'circle', label: 'Agregar círculo', icon: '⚪' },
        { name: 'text', label: 'Agregar texto', icon: '🔤' },
    ]

    const canvasW = ref(1000)
    const canvasH = ref(800)
    const bgImage = ref(null)
    const bgImageUrl = ref('')
    const mapJSON = ref(null)
    const currentTool = ref('rect')
    const spacePressed = ref(false)
    const isLoading = ref(false)
    const toast = ref({ visible: false, message: '', type: 'success' })

    // Histórico undo/redo
    const history = ref([])
    const future = ref([])

    // Modal “Agregar fila”
    const showAddRow = ref(false)
    const sectors = ref([])

    // Para asignar entrada_id por defecto
    const { tickets, totalTickets } = useTickets(eventoId)

    // ─── 2) onMounted: CARGAR MAPA (seats + shapes + bg + JSON) ───────────────
    onMounted(async () => {
        // 2.1) Background inicial
        if (initialBgImageUrl) {
            const img = new Image()
            img.src = initialBgImageUrl
            await new Promise(r => (img.onload = r))
            bgImage.value = img
            bgImageUrl.value = initialBgImageUrl
        }

        // 2.2) Validar que tengamos eventoId
        if (!eventoId) {
            console.error('useSeatMap: falta el eventoId')
            return
        }

        // 2.3) Petición al backend
        const res = await fetch(`/api/eventos/${eventoId}/map`)
        if (!res.ok) {
            console.error(`Error al cargar el mapa: ${res.status}`)
            return
        }
        // Desestructuramos seats y shapes
        const { seats: rawSeats, shapes: rawShapes, bgUrl, map } = await res.json()

        // 2.4) Si viene bgUrl distinto, lo cargamos
        if (bgUrl) {
            const img2 = new Image()
            img2.src = bgUrl
            await new Promise(r => (img2.onload = r))
            bgImage.value = img2
            bgImageUrl.value = bgUrl
        }

        // 2.5) Guardar JSON crudo del canvas
        mapJSON.value = map

        // 2.6) Mapear los asientos y asignarles entrada_id por defecto si hiciera falta
        const defaultEntradaId = tickets.value[0]?.id
        seats.value = rawSeats.map(s => ({
            ...s,
            entrada_id: s.entrada_id ?? defaultEntradaId,
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

        // 2.7) Mapear los shapes
        shapes.value = rawShapes.map(s => ({
            type: s.type,
            x: s.x,
            y: s.y,
            width: s.width ?? null,
            height: s.height ?? null,
            rotation: s.rotation ?? 0,
            label: s.label ?? '',
            fontSize: s.font_size ?? 18,
            draggable: true,
        }))
    })

    // ─── 3) PAN con barra espaciadora ──────────────────────────────────────────
    window.addEventListener('keydown', e => { if (e.code === 'Space') spacePressed.value = true })
    window.addEventListener('keyup', e => { if (e.code === 'Space') spacePressed.value = false })

    // ─── 4) TOOLBAR ACTIONS (añadir rect/circle/text) ─────────────────────────
    function onToolSelect(name) {
        currentTool.value = name
        if (name === 'rect') addRectangle()
        if (name === 'circle') addCircle()
        if (name === 'text') addText()
    }
    function addRectangle() {
        shapes.value.push({
            type: 'rect',
            x: canvasW.value / 2 - 25,
            y: canvasH.value / 2 - 15,
            width: 50,
            height: 30,
            stroke: 'gray',
            strokeWidth: 2,
            label: '',
            rotation: 0,
            draggable: true
        })
    }
    function addCircle() {
        shapes.value.push({
            type: 'circle',
            x: canvasW.value / 2,
            y: canvasH.value / 2,
            width: 60,
            height: 60,
            stroke: 'gray',
            strokeWidth: 2,
            label: '',
            rotation: 0,
            draggable: true
        })
    }
    function addText() {
        const t = prompt('Ingresa texto:')
        if (!t) return
        shapes.value.push({
            type: 'text',
            x: canvasW.value / 2,
            y: canvasH.value / 2,
            label: t,
            fontSize: 18,
            rotation: 0,
            draggable: true
        })
    }

    // ─── 5) DRAG & SELECT UPDATE ────────────────────────────────────────────────
    function onSeatsUpdate(newSeats) {
        seats.value = (newSeats || []).map(s => ({
            ...s,
            selected: !!s.selected,
            radius: s.radius ?? 22,
            label: s.label || '',
            fontSize: s.fontSize || 18,
        }))
    }

    // ─── 6) RENAME ──────────────────────────────────────────────────────────────
    function onRename({ type, label, letter, start }) {
        const sel = seats.value.filter(s => s.selected)
        if (!sel.length) return
        if (type === 'single') sel[0].label = label
        else sel
            .sort((a, b) => a.x - b.x)
            .forEach((s, i) => s.label = `${letter}${start + i}`)
    }

    // ─── 7) UNDO / REDO ─────────────────────────────────────────────────────────
    watch(seats, s => {
        history.value.push(JSON.parse(JSON.stringify(s)))
        if (history.value.length > 50) history.value.shift()
        future.value = []
    }, { deep: true, immediate: true })
    function undo() {
        if (history.value.length < 2) return
        future.value.unshift(history.value.pop())
        seats.value = JSON.parse(JSON.stringify(history.value.at(-1)))
    }
    function redo() {
        if (!future.value.length) return
        const next = future.value.shift()
        seats.value.push(next)
        history.value.push(next)
    }
    function toggleSelectAll() {
        const all = seats.value.every(s => s.selected)
        seats.value = seats.value.map(s => ({ ...s, selected: !all }))
    }
    function deleteSelected() {
        seats.value = seats.value.filter(s => !s.selected)
    }

    // ─── 8) ZOOM & RESET ────────────────────────────────────────────────────────
    function zoomIn() { const st = canvasRef.value.getStage(); st.scale({ x: st.scaleX() * 1.2, y: st.scaleY() * 1.2 }); st.batchDraw() }
    function zoomOut() { const st = canvasRef.value.getStage(); st.scale({ x: st.scaleX() / 1.2, y: st.scaleY() / 1.2 }); st.batchDraw() }
    function resetView() { const st = canvasRef.value.getStage(); st.scale({ x: 1, y: 1 }); st.position({ x: 0, y: 0 }); st.batchDraw() }
    function showHelp() { alert('Usa la rueda para zoom y la barra espaciadora para pan') }

    // ─── 9) AGREGAR ASIENTO “FREE-HAND” ─────────────────────────────────────────
    function addSeat() {
        if (!tickets.value.length) {
            toast.value = { visible: true, message: 'No hay tipos de ticket configurados.', type: 'error' }
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
            selected: false
        })
    }

    // ─── 10) GUARDAR TODO (shapes+seats) ────────────────────────────────────────
    async function guardarTodo() {
        // 10.1) Filtrar solo los asientos reales para validación
        const onlySeats = seats.value.filter(s => s.type === 'seat')
        const faltan = onlySeats.some(s => !s.entrada_id)
        if (faltan) {
            toast.value = { visible: true, message: 'Hay asientos sin tipo de entrada.', type: 'error' }
            return
        }

        isLoading.value = true
        try {
            if (!mapJSON.value && canvasRef.value) {
                mapJSON.value = canvasRef.value.getStage().toJSON()
            }

            // 10.2) Construir array unificado: primero shapes, luego seats
            const elements = [
                ...shapes.value.map(s => ({
                    type: s.type,
                    x: s.x,
                    y: s.y,
                    width: s.width,
                    height: s.height,
                    rotation: s.rotation,
                    label: s.label,
                    fontSize: s.fontSize,
                })),
                ...onlySeats.map(s => ({
                    type: s.type,
                    x: s.x,
                    y: s.y,
                    row: s.row,
                    prefix: s.prefix,
                    number: s.number,
                    entrada_id: s.entrada_id,
                    width: s.width,
                    height: s.height,
                    radius: s.radius,
                    label: s.label,
                    fontSize: s.fontSize,
                    rotation: s.rotation,
                }))
            ]

            const payload = {
                seats: toRaw(elements),
                bgUrl: bgImageUrl.value,
                map: mapJSON.value,
            }

            const res = await fetch(`/api/eventos/${eventoId}/mapa`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })
            if (!res.ok) throw new Error(`Error guardando: ${res.status}`)

            toast.value = { visible: true, message: 'Mapa guardado ✅', type: 'success' }
        }
        catch (err) {
            console.error(err)
            toast.value = { visible: true, message: 'Error al guardar 😢', type: 'error' }
        }
        finally {
            isLoading.value = false
            setTimeout(() => toast.value.visible = false, 2000)
        }
    }

    // ─── 11) ADD‑ROW MODAL ─────────────────────────────────────────────────────
    function openAddRowModal() { showAddRow.value = true }
    function onRowAdd({ sectorId, prefix, start, count }) {
        showAddRow.value = false
        for (let i = 0; i < count; i++) {
            seats.value.push({
                type: 'seat',
                x: 100 + i * 50,
                y: 700,
                entrada_id: sectorId,
                row: prefix,
                number: start + i,
                label: `${prefix}${start + i}`,
                draggable: true,
                rotation: 0,
                selected: false
            })
        }
    }

    // ─── 12) BG UPLOADER ────────────────────────────────────────────────────────
    function onBgLoaded(img) { bgImage.value = img }
    function onFileSelected(f) { /* … lógica de subir fondo … */ }
    function removeBg() { bgImage.value = null; selectedFile.value = null; removedBg.value = true }

    // ─── RETURN ────────────────────────────────────────────────────────────────
    return {
        canvasRef, seats, shapes, canvasW, canvasH, bgImage,
        mapJSON, currentTool, spacePressed, isLoading, toast,
        onToolSelect, onBgLoaded, onFileSelected, removeBg,
        addSeat, guardarTodo, onSeatsUpdate,
        history, future, toggleSelectAll, undo, redo,
        zoomIn, zoomOut, resetView, deleteSelected, showHelp,
        showAddRow, openAddRowModal, sectors, onRowAdd,
        onRename, tools, 
    }
}
