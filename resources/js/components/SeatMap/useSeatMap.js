import { ref, onMounted, toRaw, watch } from 'vue'

export function useSeatMap(props) {
    // Toolbar tools
    const tools = [
        { name: 'rect', label: 'Agregar cuadrado', icon: '…' },
        { name: 'circle', label: 'Agregar círculo', icon: '…' },
        { name: 'text', label: 'Agregar texto', icon: '…' },
    ];

    // Props desestructuradas
    const eventoId = props.eventoId;
    const initialBgImageUrl = props.initialBgImageUrl;

    // Estado
    const showAddRow = ref(false);
    const sectors = ref([]);
    const canvasRef = ref(null);
    const tickets = ref([]);
    // Importante: aseguramos números
    const canvasW = Number(1000);
    const canvasH = Number(800);
    const bgImage = ref(null);
    const selectedFile = ref(null);
    const bgImageUrl = ref('');
    const removedBg = ref(false);
    const seats = ref([]);
    const mapJSON = ref(null);
    const currentTool = ref('rect');
    // const currentTool = ref('select');
    const spacePressed = ref(false);
    const isLoading = ref(false);
    const toast = ref({ visible: false, message: '', type: 'success' });

    // Selección de tool en sidebar
    function onToolSelect(tool) {
        currentTool.value = tool;
        if (tool === 'rect') addRectangle();
        if (tool === 'circle') addCircle();
        if (tool === 'text') addText();
    }

    // Montaje inicial
    onMounted(async () => {
        // (1) cargar imagen inicial
        if (initialBgImageUrl) {
            const img = new window.Image();
            img.src = initialBgImageUrl;
            await new Promise(r => (img.onload = r));
            bgImage.value = img;
            bgImageUrl.value = initialBgImageUrl;
        }

        // (2) traer tickets
        try {
            const res = await fetch(`/api/eventos/${eventoId}/entradas`);
            tickets.value = await res.json();
        } catch (e) {
            console.error('Error cargando tickets:', e);
        }

        // (3) traer mapa completo (asientos + shapes + bg + map JSON)
        try {
            const resMap = await fetch(`/api/eventos/${eventoId}/map`);
            const { seats: rawSeats, bgUrl, map } = await resMap.json();

            // actualizar fondo si viene
            if (bgUrl) {
                const img = new window.Image();
                img.src = bgUrl;
                await new Promise(r => (img.onload = r));
                bgImage.value = img;
                bgImageUrl.value = bgUrl;
            }

            // guardar JSON del canvas
            mapJSON.value = map;

            // normalizar y asignar a seats.value
            seats.value = rawSeats
                .filter(s => typeof s.x === 'number' && !isNaN(s.x) && typeof s.y === 'number' && !isNaN(s.y))
                .map(s => ({
                    ...s,
                    selected: false,
                    radius: s.radius ?? 22,
                    label: s.label !== null && s.label !== undefined ? s.label : '',
                    fontSize: s.font_size ?? s.fontSize ?? 18,
                    type: s.type ?? 'seat',
                    width: s.width ?? null,
                    height: s.height ?? null,
                    draggable: s.draggable ?? true,
                    rotation: s.rotation ?? 0, // 👈 ESTA LÍNEA
                }));



        } catch (e) {
            console.error('Error cargando mapa:', e);
        }

        // (4) SPACE para pan
        window.addEventListener('keydown', e => {
            if (e.code === 'Space') spacePressed.value = true;
        });
        window.addEventListener('keyup', e => {
            if (e.code === 'Space') spacePressed.value = false;
        });
    });



    // Modal fila de butacas
    function openModal() {
        showAddRow.value = true;
    }

    function onRowAdd({ sectorId, prefix, start, count }) {
        showAddRow.value = false;
        const baseX = 100, baseY = 700;
        for (let i = 0; i < count; i++) {
            const num = start + i;
            seats.value.push({
                x: baseX + i * 50,
                y: baseY,
                selected: false,
                entrada_id: sectorId,
                row: prefix,
                number: num,
                label: `${prefix}${num}`,
            });
        }
    }

    // Fondo
    function onBgLoaded(img) {
        bgImage.value = img;
    }
    function onFileSelected(file) {
        selectedFile.value = file;
        removedBg.value = false;
    }
    function removeBg() {
        bgImage.value = null;
        selectedFile.value = null;
        if (bgImageUrl.value) removedBg.value = true;
    }

    // Agregar asiento
    function addSeat() {
        // Verificar tipo y valor de canvasW y canvasH
        console.log('canvasW:', typeof canvasW, canvasW);
        console.log('canvasH:', typeof canvasH, canvasH);

        const defaultEntradaId = tickets.value.length ? tickets.value[0].id : null;

        // Forzar valores numéricos siempre
        let x = Number(canvasW) / 2 - 20;
        let y = Number(canvasH) / 2 - 20;

        // Si por alguna razón sigue siendo NaN, usar valores por defecto seguros
        if (isNaN(x)) x = 100;
        if (isNaN(y)) y = 100;

        // Sugerir fila y número automáticamente (opcional)
        let nextRow = 'A';
        let nextNumber = 1;
        if (seats.value.length > 0) {
            // Buscar la última fila y número usados
            const lastSeat = seats.value[seats.value.length - 1];
            nextRow = lastSeat.row || 'A';
            nextNumber = (Number(lastSeat.number) || 0) + 1;
        }

        // Debug (puedes quitarlo si todo funciona)
        console.log("Agregando asiento en:", { x, y, nextRow, nextNumber });

        // Crear el asiento, asegurando los tipos correctos
        const newSeat = {
            x: Number(x),
            y: Number(y),
            selected: false,
            entrada_id: defaultEntradaId,
            row: String(nextRow),          // Puede ser letra o número, pero como texto
            number: Number(nextNumber),    // Siempre numérico
            label: `${nextRow}${nextNumber}`,
        };

        // Validar tipos antes de pushear
        if (!isNaN(newSeat.x) && !isNaN(newSeat.y) && !isNaN(newSeat.number)) {
            seats.value.push(newSeat);
        } else {
            console.warn('Intento de agregar asiento inválido:', newSeat);
        }

        // Debug all
        seats.value.forEach((s, i) => {
            console.log(
                `Asiento ${i}: x=${s.x} (${typeof s.x}), y=${s.y} (${typeof s.y}), row=${s.row}, number=${s.number}, label=${s.label}`
            );
        });
    }




    // Guardar todo
    async function guardarTodo() {
        isLoading.value = true;
        try {
            // (1) borrar fondo viejo
            if (removedBg.value && bgImageUrl.value) {
                const del = await fetch(`/api/eventos/${eventoId}/delete-bg`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: bgImageUrl.value }),
                });
                if (!del.ok) throw new Error(`delete-bg ${del.status}`);
                bgImageUrl.value = '';
                removedBg.value = false;
            }

            // (2) subir nuevo
            if (selectedFile.value) {
                const fd = new window.FormData();
                fd.append('image', selectedFile.value);
                const up = await fetch(`/api/eventos/${eventoId}/upload-bg`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: fd,
                });
                if (!up.ok) throw new Error(`upload-bg ${up.status}`);
                const j = await up.json();
                bgImageUrl.value = j.url;
            }

            // (3) JSON mapa
            if (!mapJSON.value && canvasRef.value && canvasRef.value.getStage) {
                mapJSON.value = canvasRef.value.getStage().toJSON();
            }

            // (4) definir defaultEntradaId antes de usarlo
            const defaultEntradaId = tickets.value.length
                ? tickets.value[0].id
                : null;

            // (5) sanitizar asientos y shapes
            const sanitizedSeats = toRaw(seats.value).map(s => ({
                ...s,
                type: s.type ?? 'seat',
                entrada_id: s.entrada_id ?? defaultEntradaId,
                label: s.label ?? `${s.row}${s.number}`,
                radius: s.radius ?? 22,
                rotation: s.rotation ?? 0, // 👈 ESTA LÍNEA
            }));

            // 👇 LOG para ver que rotation está OK
            //console.log('Sanitized seats antes de guardar:', sanitizedSeats);



            // (6) payload y envío
            const payload = {
                seats: sanitizedSeats,
                bgUrl: bgImageUrl.value,
                map: mapJSON.value,
            };
            //console.log('Guardando estos datos:', JSON.stringify(payload, null, 2)); // ← AQUÍ

            const res = await fetch(`/api/eventos/${eventoId}/mapa`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (!res.ok) throw new Error(`save-map ${res.status}`);
            const data = await res.json();
            toast.value = {
                visible: true,
                message: data.status === 'ok'
                    ? 'Guardado correctamente'
                    : 'Error al guardar',
                type: data.status === 'ok' ? 'success' : 'error',
            };

        } catch (err) {
            console.error(err);
            toast.value = {
                visible: true,
                message: 'Error de red, revisá consola',
                type: 'error',
            };
        } finally {
            isLoading.value = false;
            setTimeout(() => (toast.value.visible = false), 2500);
        }
    }


    // Renombrar labels (SeatControls)
    function onRename({ type, label, letter, start }) {
        const sel = seats.value.filter(s => s.selected);
        if (type === 'single') {
            sel[0].label = label;
        } else {
            sel
                .sort((a, b) => a.x - b.x)
                .forEach((s, i) => {
                    s.label = `${letter}${start + i}`;
                });
        }
    }

    // Actualizar asientos (SeatCanvas)
    // function sanitizeSeats(arr) {
    //     return arr.filter(
    //         s =>
    //             s && // existe el objeto
    //             typeof s.x === 'number' && !isNaN(s.x) &&
    //             typeof s.y === 'number' && !isNaN(s.y) &&
    //             Object.keys(s).length > 0
    //     );
    // }

    // 1) Cuando el canvas emite una actualización manual de seats/shapes:
    function onSeatsUpdate(newSeats) {
        seats.value = (newSeats || [])
            .filter(s => s && typeof s.x === 'number' && typeof s.y === 'number')
            .map(s => ({
                ...s,
                selected: Boolean(s.selected),
                radius: s.radius ?? 22,
                label: s.label ?? '',
                fontSize: s.fontSize ?? 18,
            }));
    }

    // Metodos para Toolbar
    // 1) Historial para undo/redo
    const history = ref([])
    const future = ref([])

    // Cada vez que seats cambian, empujamos snapshot al history
    watch(seats, s => {
        history.value.push(JSON.parse(JSON.stringify(s)))
        if (history.value.length > 50) history.value.shift()
        future.value = []
    }, { deep: true, immediate: true })

    // 2) Undo / Redo
    function undo() {
        if (history.value.length < 2) return
        const last = history.value.pop()
        future.value.unshift(last)
        seats.value = JSON.parse(JSON.stringify(history.value[history.value.length - 1]))
    }
    function redo() {
        if (!future.value.length) return
        const next = future.value.shift()
        seats.value = JSON.parse(JSON.stringify(next))
        history.value.push(next)
    }

    // 3) Toggle Select All
    function toggleSelectAll() {
        const all = seats.value.every(s => s.selected)
        seats.value = seats.value.map(s => ({ ...s, selected: !all }))
    }

    // 4) Delete Selected
    // 2) Al borrar los seleccionados desde tu toolbar:
    function deleteSelected() {
        seats.value = seats.value
            .filter(s => s && !s.selected)
            .map(s => ({ ...s, selected: false }));
    }

    // 5) Zoom & Reset View
    function zoom(factor) {
        const stage = canvasRef.value?.getStage()
        if (!stage) return
        const old = stage.scaleX()
        const next = old * factor
        stage.scale({ x: next, y: next })
        stage.batchDraw()
    }
    const zoomIn = () => zoom(1.2)
    const zoomOut = () => zoom(1 / 1.2)
    function resetView() {
        const stage = canvasRef.value?.getStage()
        if (!stage) return
        stage.scale({ x: 1, y: 1 })
        stage.position({ x: 0, y: 0 })
        stage.batchDraw()
    }

    // 6) Ayuda
    function showHelp() {
        // puede ser un modal más bonito
        alert(`Usa rueda de ratón para zoom\nMantén espaciadora para pan…`)
    }

    // ELEMENTOS DE DIBUJO
    // 2) Funciones de dibujo
    function addRectangle() {
        seats.value.push({
            type: 'rect',
            x: canvasW / 2 - 25,
            y: canvasH / 2 - 15,
            width: 50,
            height: 30,
            stroke: 'gray',
            strokeWidth: 2,
            selected: false,
        });
    }

    // CREA UN CÍRCULO “libre”
    function addCircle() {
        seats.value.push({
            type: 'circle',
            x: canvasW / 2,
            y: canvasH / 2,
            radius: 30,
            stroke: 'gray',
            strokeWidth: 2,
            selected: false,
        });
    }

    // CREA UN TEXTO PEDIDO POR PROMPT
    function addText() {
        const txt = window.prompt('Ingresa el texto:', 'Nuevo texto')
        if (!txt) return;
        seats.value.push({
            type: 'text',
            x: canvasW / 2,
            y: canvasH / 2,
            label: txt,
            fontSize: 18,
            draggable: true,
            selected: false,
        });
    }

    function onToolSelect(name) {
        currentTool.value = name;
        if (name === 'rect') addRectangle();
        if (name === 'circle') addCircle();
        if (name === 'text') addText();
    }

    return {
        tools,
        eventoId,
        showAddRow,
        sectors,
        canvasRef,
        tickets,
        canvasW,
        canvasH,
        bgImage,
        selectedFile,
        bgImageUrl,
        removedBg,
        seats,
        mapJSON,
        currentTool,
        spacePressed,
        isLoading,
        toast,
        onToolSelect,
        openModal,
        onRowAdd,
        onBgLoaded,
        onFileSelected,
        removeBg,
        addSeat,
        guardarTodo,
        onRename,
        onSeatsUpdate,
        history,
        future,
        undo,
        redo,
        toggleSelectAll,
        deleteSelected,
        zoomIn,
        zoomOut,
        resetView,
        showHelp
    }
}
