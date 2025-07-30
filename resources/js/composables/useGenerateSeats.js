// resources/js/composables/useGenerateSeats.js
import { ref } from 'vue'

export function useGenerateSeats(seats, tickets, canvasW, canvasH) {
    const showGenerateModal = ref(false)
    const generateCount = ref(1)
    const selectedTicket = ref(null)

    function openGenerateModal() {
        showGenerateModal.value = true
    }
    function selectTicket(ticket) {
        selectedTicket.value = ticket
    }

    function generateSeats() {
        if (!selectedTicket.value) return

        const id = selectedTicket.value.id
        const count = generateCount.value
        const radius = 22            // o toma el valor que uses
        const gap = 8             // espacio entre círculos
        const diameter = radius * 2 + gap

        // Centrar horizontalmente
        const startX = (canvasW.value - diameter * (count - 1)) / 2
        const y = canvasH.value / 2

        for (let i = 0; i < count; i++) {
            seats.value.push({
                type: 'seat',
                x: startX + i * diameter,
                y,
                entrada_id: id,   // ← siempre definido
                label: '',
                rotation: 0,
            })
        }

        showGenerateModal.value = false
    }

    return {
        showGenerateModal,
        generateCount,
        selectedTicket,
        openGenerateModal,
        selectTicket,
        generateSeats,
    }
}
