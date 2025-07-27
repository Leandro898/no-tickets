import { ref } from 'vue'

export function useCanvasInteractions({ props, emit }) {
    // Refs a Stage, Layer y Transformer
    const stageRef = ref(null)
    const layerRef = ref(null)
    const transformerRef = ref(null)

    // Estado de la selección por rectángulo
    const selection = ref({ visible: false, x: 0, y: 0, width: 0, height: 0 })
    const defaultRadius = 22

    // Para saber si acabamos de hacer selección por rectángulo
    const didRectSelect = ref(false)

    // Posición inicial del drag
    let startPos = null

    /**
     * onMouseDown: arrancamos la caja de selección si NO clicas sobre un asiento
     */
    function onMouseDown(e) {
        const cls = e.target.getClassName?.()
        if (cls === 'Circle') return

        const pos = e.target.getStage().getPointerPosition()
        startPos = pos
        selection.value = {
            visible: true,
            x: pos.x,
            y: pos.y,
            width: 0,
            height: 0
        }
        // reseteamos el flag
        didRectSelect.value = false
    }

    /**
     * onMouseMove: mientras arrastras, ajusta el tamaño del rectángulo
     */
    function onMouseMove(e) {
        if (!selection.value.visible || !startPos) return
        const pos = e.target.getStage().getPointerPosition()
        selection.value = {
            visible: true,
            x: startPos.x,
            y: startPos.y,
            width: pos.x - startPos.x,
            height: pos.y - startPos.y
        }
    }

    /**
     * onMouseUp: si fue un drag grande (>5px), selecciona todos los asientos
     * dentro del rectángulo; marca didRectSelect. Si no, no toca la selección.
     */
    function onMouseUp(e) {
        if (selection.value.visible) {
            const w = selection.value.width
            const h = selection.value.height
            const absW = Math.abs(w)
            const absH = Math.abs(h)

            if (absW > 5 && absH > 5) {
                // calculamos bounds
                const selX = w < 0 ? selection.value.x + w : selection.value.x
                const selY = h < 0 ? selection.value.y + h : selection.value.y
                const selW = absW
                const selH = absH

                // seleccionamos
                const updated = props.seats.map(seat => ({
                    ...seat,
                    selected:
                        seat.x >= selX &&
                        seat.x <= selX + selW &&
                        seat.y >= selY &&
                        seat.y <= selY + selH
                }))
                emit('update:seats', updated)

                // indicamos que hicimos selección por rectángulo
                didRectSelect.value = true
            }
            // si arrastre fue pequeño, no deseleccionamos aquí
        }

        // ocultar rectángulo y resetear
        selection.value.visible = false
        startPos = null
    }

    return {
        stageRef,
        layerRef,
        transformerRef,
        selection,
        defaultRadius,
        didRectSelect,     // <-- exportamos este flag
        onMouseDown,
        onMouseMove,
        onMouseUp
    }
}
