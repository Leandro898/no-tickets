// resources/js/composables/useSeatMap.js
import { ref, onMounted, onBeforeUnmount, toRaw, watch, nextTick } from 'vue'
import { useTickets } from '@/composables/useTickets'
import { BASE_CANVAS_WIDTH, BASE_CANVAS_HEIGHT } from '@/constants/seatMap'

export function useSeatMap(eventoSlug, initialBgImageUrl, containerRef) {
    // ─── 1) STATE ─────────────────────────────────────────────────────────────
    const canvasRef = ref(null)
    const seats = ref([])
    const shapes = ref([])          // ← aquí guardaremos los shapes

    // 🟢 1) Estado responsive
    const canvasW = ref(0)
    const canvasH = ref(0)

    const tools = [
        { name: 'rect', label: 'Agregar cuadrado', icon: '⬛' },
        { name: 'circle', label: 'Agregar círculo', icon: '⚪' },
        { name: 'text', label: 'Agregar texto', icon: '🔤' },
    ]

    const bgUploading = ref(false)
    // ─── DIMENSIONES DEL CANVAS ────────────────────────────────────────────────
    const bgImage = ref(null)
    const bgImageUrl = ref(initialBgImageUrl || '')
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
    const { tickets, totalTickets } = useTickets(eventoSlug)

    // ─── MEDIR CONTENEDOR ───────────────────────────────────
    // 🟢 2) Función que mide el contenedor
    function updateSize() {
        if (containerRef?.value) {
            canvasW.value = containerRef.value.clientWidth
            canvasH.value = containerRef.value.clientHeight
        }
    }
    onMounted(() => {
        updateSize()
        containerRef.value.clientWidth,
            containerRef.value.clientHeight

        window.addEventListener('resize', updateSize)
    })

    watch([canvasW, canvasH], ([w, h]) => {

    })

    onBeforeUnmount(() => {
        window.removeEventListener('resize', updateSize)
    })

    // ─── 2) onMounted: CARGAR MAPA (seats + shapes + bg + JSON) ───────────────
    onMounted(async () => {
        // 🔍 2.1) Fetch al backend y manejo de errores
        try {
            const res = await fetch(`/api/eventos/${eventoSlug}/map`)
            if (!res.ok) {
                console.error(`❌ Error al cargar el mapa: ${res.status}`)
                return
            }

            // 📥 Leemos UNA VEZ el body como texto
            //aca quiere decir que recibe un JSON??? osea es como se recibe en el navegador? datos crudos?
            const text = await res.text()

            // 🔄 Intentamos convertir ese texto a JSON
            let data
            try {
                data = JSON.parse(text)
                console.log(data)
            } catch (err) {
                console.error('⚠️ Respuesta inválida (no es JSON):', text)
                return
            }

            // 🟢 Si todo bien, extraemos el payload
            const { seats: rawSeats, shapes: rawShapes, bgUrl, map } = data

            // 2.2) Fondo inicial (prop)
            if (initialBgImageUrl) {
                const img0 = new Image()
                img0.src = initialBgImageUrl
                await new Promise(r => (img0.onload = r))
                bgImage.value = img0
                bgImageUrl.value = initialBgImageUrl
            }

            // 2.3) Validar eventoSlug
            if (!eventoSlug) {
                console.error('useSeatMap: falta el eventoSlug')
                return
            }

            // 2.4) Si viene bgUrl, lo normalizamos y cargamos
            if (bgUrl) {
                //console.log('[useSeatMap] raw bgUrl:', bgUrl)

                // Creamos un objeto URL para extraer solo el pathname:
                const urlObj = new URL(bgUrl, window.location.origin)
                // pathname = "/storage/seat_maps/…png"
                const src = window.location.origin + urlObj.pathname

                //console.log('[useSeatMap] cargando imagen de fondo desde:', src)
                const img1 = new Image()
                img1.onerror = () => console.error('Error al cargar fondo:', src)
                img1.src = src
                await new Promise(r => (img1.onload = r))

                bgImage.value = img1
                bgImageUrl.value = urlObj.pathname  // guardas "/storage/seat_maps/…png"
            }

            // 2.5) Guardar JSON crudo del canvas
            mapJSON.value = map

            await nextTick();

            // 🟢 ACA el canvasW y canvasH ya están actualizados al tamaño visible del canvas
            const actualW = canvasW.value || BASE_CANVAS_WIDTH
            const actualH = canvasH.value || BASE_CANVAS_HEIGHT

            // 2.6) Mapear los asientos y asignarles entrada_id por defecto si hiciera falta - Antes
            // 🔥🔥 ACA es donde multiplicamos por el ancho/alto ACTUAL, no el BASE
            seats.value = rawSeats.map(s => ({
                ...s,
                x: s.x * actualW,
                y: s.y * actualH,
                width: s.width ? s.width * actualW : null,
                height: s.height ? s.height * actualH : null,
                radius: s.radius ? s.radius * actualW : null,
                fontSize: s.fontSize ? s.fontSize * actualW : null,
                selected: false,
            }));
            console.log('Se pintan asientos desde useSeatMap:', seats.value)
            shapes.value = rawShapes.map(s => ({
                ...s,
                x: s.x * actualW,
                y: s.y * actualH,
                width: s.width ? s.width * actualW : null,
                height: s.height ? s.height * actualH : null,
                radius: s.radius ? s.radius * actualW : null,
                fontSize: s.fontSize ? s.fontSize * actualW : null,
                selected: false,
            }));

        } catch (networkErr) {
            console.error('🌐 Error de red al pedir el mapa:', networkErr)
        }
    })

    // Justo después de cargar seats y shapes en onMounted
    watch([canvasW, canvasH], ([w, h]) => {
        const baseW = w || 1;
        const baseH = h || 1;
        seats.value = seats.value.map(s => ({
            ...s,
            x: s.x <= 1 ? s.x * baseW : s.x, // solo recalcula si está en relativo (menor o igual a 1)
            y: s.y <= 1 ? s.y * baseH : s.y,
            width: s.width && s.width <= 1 ? s.width * baseW : s.width,
            height: s.height && s.height <= 1 ? s.height * baseH : s.height,
            radius: s.radius && s.radius <= 1 ? s.radius * baseW : s.radius,
            fontSize: s.fontSize && s.fontSize <= 1 ? s.fontSize * baseW : s.fontSize,
        }));
        shapes.value = shapes.value.map(s => ({
            ...s,
            x: s.x <= 1 ? s.x * baseW : s.x,
            y: s.y <= 1 ? s.y * baseH : s.y,
            width: s.width && s.width <= 1 ? s.width * baseW : s.width,
            height: s.height && s.height <= 1 ? s.height * baseH : s.height,
            radius: s.radius && s.radius <= 1 ? s.radius * baseW : s.radius,
            fontSize: s.fontSize && s.fontSize <= 1 ? s.fontSize * baseW : s.fontSize,
        }));
    });

    // ─── 3) PAN con barra espaciadora ──────────────────────────────────────────
    function onKeyDown(e) {
        // solo si no estamos escribiendo en un input/textarea
        const tag = e.target.tagName
        if (e.code === 'Space' && tag !== 'INPUT' && tag !== 'TEXTAREA') {
            e.preventDefault()        // evita el scroll de la página
            spacePressed.value = true
        }
    }

    function onKeyUp(e) {
        if (e.code === 'Space') {
            e.preventDefault()
            spacePressed.value = false
        }
    }

    onMounted(() => {
        window.addEventListener('keydown', onKeyDown)
        window.addEventListener('keyup', onKeyUp)
    })

    onBeforeUnmount(() => {
        window.removeEventListener('keydown', onKeyDown)
        window.removeEventListener('keyup', onKeyUp)
    })

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

        })
    }

    // ─── 5) DRAG & SELECT UPDATE ────────────────────────────────────────────────
    function onSeatsUpdate(newSeats) {
        // REEMPLAZAR el array COMPLETO
        seats.value = newSeats.map(s => ({ ...s }))
    }

    // ─── 6) RENAME asientos ──────────────────────────────────────────────────────────────
    function onRename({ type, label, letter, start }) {
        const sel = seats.value.filter(s => s.selected)
        if (!sel.length) return
        if (type === 'single') {
            if (!label || label.trim() === '') {
                toast.value = { visible: true, message: 'El label no puede estar vacío.', type: 'error' }
                return
            }
            sel[0].label = label
        } else {
            if (!letter || letter.trim() === '') {
                toast.value = { visible: true, message: 'El prefijo no puede estar vacío.', type: 'error' }
                return
            }
            sel
                .sort((a, b) => a.x - b.x)
                .forEach((s, i) => s.label = `${letter}${start + i}`)
        }
    }


    // ─── **NUEVO** handler para shapes
    function onShapesUpdate(newShapes) {
        shapes.value = (newShapes || []).map(s => ({
            type: s.type,
            x: s.x,
            y: s.y,
            width: s.width ?? null,
            height: s.height ?? null,
            rotation: s.rotation ?? 0,
            label: s.label ?? '',
            fontSize: s.fontSize ?? 18,
            selected: !!s.selected
        }))
    }

    // ─── 7) UNDO / REDO ─────────────────────────────────────────────────────────
    function deleteSelected() {
        // elimina tanto asientos como shapes que estén marcados selected
        seats.value = seats.value.filter(s => !s.selected)
        shapes.value = shapes.value.filter(sh => !sh.selected)
    }
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
        // unimos ambos arrays para saber si están todos seleccionados
        const elementos = [...seats.value, ...shapes.value];
        const todos = elementos.every(el => el.selected);

        // invertimos selected en cada array
        seats.value = seats.value.map(s => ({ ...s, selected: !todos }));
        shapes.value = shapes.value.map(sh => ({ ...sh, selected: !todos }));
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
        const nextNum = seats.value.length + 1
        const newLabel = `S${nextNum}`

        seats.value.push({
            type: 'seat',
            x: canvasW.value / 2 - 20,
            y: canvasH.value / 2 - 20,
            entrada_id: defaultEntradaId,
            label: newLabel,      // <--- Acá, SIEMPRE asignale un nombre por defecto
            row: '',
            number: nextNum,
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

        // 🚨 Validar que todos tengan label (nombre)
        const faltanLabel = onlySeats.some(s => !s.label || s.label.trim() === '')
        if (faltanLabel) {
            // 🔥🔥 Agregá este log para depuración:
            const asientosSinLabel = onlySeats.filter(s => !s.label || s.label.trim() === '')
            console.warn('Hay asientos sin label:', asientosSinLabel)

            toast.value = { visible: true, message: 'Hay asientos sin nombre (label)', type: 'error' }
            return
        }


        isLoading.value = true
        try {
            if (!mapJSON.value && canvasRef.value) {
                mapJSON.value = canvasRef.value.getStage().toJSON()
            }

            // 10.2) Construir array unificado: primero shapes, luego seats
            const baseW = canvasW.value || 1; // Siempre evitá dividir por 0
            const baseH = canvasH.value || 1;

            const elements = [
                ...shapes.value.map(s => ({
                    ...s,
                    x: s.x / baseW,
                    y: s.y / baseH,
                    width: s.width ? s.width / baseW : null,
                    height: s.height ? s.height / baseH : null,
                    radius: s.radius ? s.radius / baseW : null,
                    fontSize: s.fontSize ? s.fontSize / baseW : null,
                })),
                ...onlySeats.map(s => ({
                    ...s,
                    x: s.x / baseW,
                    y: s.y / baseH,
                    width: s.width ? s.width / baseW : null,
                    height: s.height ? s.height / baseH : null,
                    radius: s.radius ? s.radius / baseW : null,
                    fontSize: s.fontSize ? s.fontSize / baseW : null,
                }))
            ];


            // Log para depuración
            // console.log('GUARDANDO:', {
            //     seats: elements, bgUrl: bgImageUrl.value, map: mapJSON.value
            // })

            const payload = {
                seats: toRaw(elements),
                bgUrl: bgImageUrl.value,
                map: mapJSON.value,
            }

            console.log('[GUARDAR] bgImageUrl:', bgImageUrl.value)

            const res = await fetch(`/api/eventos/${eventoSlug}/mapa`, {
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
                rotation: 0,
                selected: false
            })
        }
    }

    // ─── 12) BG UPLOADER ────────────────────────────────────────────────────────

    /**
     * Recibe una imagen ya subida y la carga en el canvas.
     * @param {HTMLImageElement} img 
     */
    function onBgLoaded(img) {
        bgImage.value = img;
    }

    /**
     * Dada una URL de imagen, hace preview y actualiza los refs.
     * @param {string} url 
     */
    async function onBgUploadRequest(url) {
        // 🔄 Normalizar URL: si no es absoluta, le anteponemos origen + /storage/
        let fullUrl = url;
        if (!/^https?:\/\//.test(url)) {
            // quitamos cualquier slash inicial y construimos ruta válida
            fullUrl = window.location.origin + '/storage/' + url.replace(/^\/+/, '');
        }

        // 🖼️ Previsualización con la URL limpia
        const img = new Image();
        img.src = fullUrl;
        await new Promise(r => (img.onload = r));

        // 🎯 Guardamos la URL completa
        bgImageUrl.value = fullUrl;
        bgImage.value = img;
    }


    /**
 * Sube el archivo al servidor y, si todo ok, dispara onBgUploadRequest(url).
 * @param {File} file 
 */
    async function onFileSelected(file) {
        bgUploading.value = true;
        const fd = new FormData();
        fd.append('image', file);

        const res = await fetch(`/api/eventos/${eventoSlug}/upload-bg`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd,
        });
        if (!res.ok) { /* manejo de error */ }

        let { url } = await res.json();  // p.ej. "seat_maps/xxx.png"
        console.log('[upload-bg] respuesta raw:', url);

        // 🔄 Normalizar aquí también
        if (!/^https?:\/\//.test(url)) {
            url = window.location.origin + '/storage/' + url.replace(/^\/+/, '');
        }
        console.log('🟢 [upload-bg] normalized URL:', url);

        // 📸 Preview y guardado
        await onBgUploadRequest(url);
        bgImageUrl.value = url;

        toast.value = { visible: true, message: 'Imagen cargada ✅', type: 'success' };
        bgUploading.value = false;
    }




    /**
     * Quita la imagen de fondo del canvas.
     */
    function removeBg() {
        bgImage.value = null;
        bgImageUrl.value = '';
    }

    /**
 * Elimina la imagen de fondo tanto del servidor como del canvas/local.
 */
    async function eliminarBg() {
        if (!bgImageUrl.value) return

        // Llama a la API para borrar imagen del storage y de la base
        const res = await fetch(`/api/eventos/${eventoSlug}/delete-bg`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ url: bgImageUrl.value })
        });

        if (!res.ok) {
            const text = await res.text();
            toast.value = { visible: true, message: 'No se pudo eliminar la imagen', type: 'error' };
            console.error('Error eliminando fondo:', text);
            return;
        }

        // Limpiá refs locales
        bgImage.value = null;
        bgImageUrl.value = '';
        toast.value = { visible: true, message: 'Imagen eliminada ✅', type: 'success' };
        setTimeout(() => (toast.value.visible = false), 2000);
    }
    // ─── RETURN ────────────────────────────────────────────────────────────────
    return {
        canvasRef, seats, shapes, canvasW, canvasH, bgImage,
        mapJSON, currentTool, spacePressed, isLoading, toast,
        onToolSelect, onBgLoaded, onFileSelected, removeBg, eliminarBg,
        addSeat, guardarTodo, onSeatsUpdate,
        history, future, toggleSelectAll, undo, redo,
        zoomIn, zoomOut, resetView, deleteSelected, showHelp,
        showAddRow, openAddRowModal, sectors, onRowAdd,
        onRename, tools, onShapesUpdate, bgImageUrl, onBgUploadRequest, bgUploading,
    }
}
