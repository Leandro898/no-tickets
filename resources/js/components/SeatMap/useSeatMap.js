import { ref, onMounted, toRaw, watch } from 'vue'

export function useSeatMap(props) {
    // Toolbar tools
    const tools = [
        { name: 'select', label: 'Seleccionar zona', icon: '<svg>…</svg>' },
        { name: 'seat', label: 'Agregar asiento', icon: '<svg>…</svg>' },
        { name: 'text', label: 'Texto', icon: '<svg>…</svg>' }
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
    const currentTool = ref('select');
    const spacePressed = ref(false);
    const isLoading = ref(false);
    const toast = ref({ visible: false, message: '', type: 'success' });

    // Selección de tool en sidebar
    function onToolSelect(name) {
        currentTool.value = name;
    }

    // Montaje inicial
    onMounted(async () => {
        // cargar imagen inicial
        if (initialBgImageUrl) {
            const img = new window.Image();
            img.src = initialBgImageUrl;
            await new Promise(r => (img.onload = r));
            bgImage.value = img;
            bgImageUrl.value = initialBgImageUrl;
        }

        // traer tickets
        try {
            const res = await fetch(`/api/eventos/${eventoId}/entradas`);
            tickets.value = await res.json();
        } catch (e) {
            console.error('Error cargando tickets:', e);
        }

        // traer asientos guardados
        // traer asientos guardados
        try {
            const res2 = await fetch(`/api/eventos/${eventoId}/asientos`);
            const raw = await res2.json();
            seats.value = raw
                .filter(s =>
                    typeof s.x === 'number' && !isNaN(s.x) &&
                    typeof s.y === 'number' && !isNaN(s.y)
                )
                .map(s => ({
                    ...s,
                    selected: false,
                    radius: s.radius ?? 22,
                    label: s.label ?? `${s.row}${s.number}`,
                }));
        } catch (e) {
            console.error('Error cargando asientos:', e);
        }


        // SPACE para pan
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

            // (4) sanitizar asientos
            const defaultEntradaId = tickets.value.length ? tickets.value[0].id : null;
            const sanitizedSeats = toRaw(seats.value).map(s => ({
                ...s,
                entrada_id: s.entrada_id ?? defaultEntradaId,
                radius: s.radius ?? 22,
            }));

            // (5) payload y envío
            const payload = {
                seats: sanitizedSeats,
                bgUrl: bgImageUrl.value,
                map: mapJSON.value,
            };
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

    function onSeatsUpdate(newSeats) {
        seats.value = newSeats
            .filter(s =>
                typeof s.x === 'number' && !isNaN(s.x) &&
                typeof s.y === 'number' && !isNaN(s.y)
            )
            .map(s => ({
                ...s,
                selected: s.selected ?? false,
                radius: s.radius ?? 22,
            }))
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
    function deleteSelected() {
        seats.value = seats.value.filter(s => !s.selected)
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
