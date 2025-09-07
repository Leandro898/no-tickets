// resources/js/composables/useGenerateSeats.js
import { ref, computed } from 'vue'

export function useGenerateSeats(seats, tickets, canvasW, canvasH) {
    const showGenerateModal = ref(false)
    const generateCount = ref(1)
    const selectedTicket = ref(null)

    // 1) Cuántos ya existen de este tipo
    const usedCount = computed(() => {
        if (!selectedTicket.value) return 0
        return seats.value.filter(s => s.entrada_id === selectedTicket.value.id).length
    })

    // 2) Cuál es el total permitido (ajusta aquí según tu estructura de tickets)
    const total = computed(() => {
        if (!selectedTicket.value) return 0
        const t = tickets.value.find(t => t.id === selectedTicket.value.id)
        return t ? t.stock_inicial : 0
    })

    // 3) El “stock restante”
    const remaining = computed(() => {
        const r = total.value - usedCount.value
        return r > 0 ? r : 0
    })

    function openGenerateModal() {
        // Si no hay ninguno seleccionado, marcamos el primero del array
        if (!selectedTicket.value && tickets.value.length > 0) {
            // Puedes usar directamente selectTicket() para aplicar todos los ajustes
            selectTicket(tickets.value[0])
        }
        showGenerateModal.value = true
    }
    function selectTicket(ticketOrId) {
        // si me llega un ID, lo convierto en objeto
        const ticketObj = typeof ticketOrId === 'object' && ticketOrId
            ? ticketOrId
            : tickets.value.find(t => t.id === ticketOrId) || null
        selectedTicket.value = ticketObj
        // reajusta el count si te pasaste del remaining
        if (generateCount.value > remaining.value) {
            generateCount.value = remaining.value || 1
        }
    }

    function generateSeats() {
        if (!selectedTicket.value) return

        // valida de nuevo
        if (generateCount.value > remaining.value) {
            alert(`Sólo quedan ${remaining.value} disponibles.`)
            return
        }

        const id = selectedTicket.value.id
        const radius = 22
        const gap = 8
        const diameter = radius * 2 + gap

        // ejemplo de posición, ajusta a tu lógica
        const startX = (canvasW.value - diameter * (generateCount.value - 1)) / 2
        const y = canvasH.value / 2

        for (let i = 0; i < generateCount.value; i++) {
            seats.value.push({
                type: 'seat',
                x: startX + i * diameter,
                y,
                entrada_id: id,
                label: '',
                rotation: 0,
                selected: false,
            })
        }

        // ④ Opción: **no cerrar** inmediatamente el modal, para ver el stock bajar en caliente.**
        showGenerateModal.value = false
    }

    return {
        showGenerateModal,
        generateCount,
        selectedTicket,
        openGenerateModal,
        selectTicket,
        generateSeats,
        remaining,
    }
}
