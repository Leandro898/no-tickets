// resources/js/composables/useTickets.js
import { ref, onMounted } from 'vue'

export function useTickets(eventoId) {
    const tickets = ref([])

    onMounted(async () => {
        try {
            const res = await fetch(`/api/eventos/${eventoId}/entradas`)
            tickets.value = await res.json()
        } catch (err) {
            console.error('Error cargando tickets:', err)
        }
    })

    return { tickets }
}
