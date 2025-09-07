<!-- resources/js/components/PurchasePanel.vue -->
<template>
    <div v-if="visible" class="purchase-drawer">
        <!-- Temporizador de expiración -->
        <div class="p-4 bg-yellow-100 text-yellow-800 font-semibold">
            <template v-if="remainingMs > 0">
                ⏳ Te quedan {{ minutes }}:{{ seconds }} para completar tu compra
            </template>
            <template v-else>
                ⚠️ Tu reserva ha expirado
            </template>
        </div>

        <!-- Cabecera -->
        <header class="drawer-header">
            <h3 class="drawer-title">🛒 Resumen de tu compra</h3>
            <button class="close-btn" @click="emit('close')">✕</button>
        </header>

        <!-- Lista de asientos y formulario -->
        <section class="drawer-body">
            <ul class="seat-list">
                <li v-for="s in seats" :key="s.id" class="seat-item">
                    <span>🎫 Asiento {{ s.label || s.id }}</span>
                    <span class="seat-price">${{ s.price || 0 }}</span>
                    <button class="remove-btn" @click="emit('remove', s.id)">✕</button>
                </li>
            </ul>

            <div class="total-row">
                <span>Total:</span>
                <strong>${{ totalPrice.toFixed(2) }}</strong>

            </div>

            <div v-if="error" class="text-red-600 mb-2">{{ error }}</div>

            <form class="drawer-form" @submit.prevent="submitPurchase">
                <div class="form-group">
                    <label for="buyer-name">Nombre completo *</label>
                    <input id="buyer-name" v-model="buyer.name" type="text" required />
                </div>
                <div class="form-group">
                    <label for="buyer-email">Email *</label>
                    <input id="buyer-email" v-model="buyer.email" type="email" required />
                </div>
                <button type="submit" class="submit-btn" :disabled="loading || remainingMs <= 0">
                    💳 Proceder al pago
                </button>
            </form>
        </section>
    </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    seats: { type: Array, required: true },
    visible: { type: Boolean, required: true },
    reservedUntil: { type: Date, default: null },
})
const emit = defineEmits(['close', 'remove', 'confirm'])

// Temporizador
const now = ref(Date.now())
let timerInterval = null

const remainingMs = computed(() =>
    props.reservedUntil
        ? new Date(props.reservedUntil).getTime() - now.value
        : 0
)
const remainingSec = computed(() => Math.max(0, Math.ceil(remainingMs.value / 1000)))
const minutes = computed(() => String(Math.floor(remainingSec.value / 60)).padStart(2, '0'))
const seconds = computed(() => String(remainingSec.value % 60).padStart(2, '0'))

watch(() => props.visible, visible => {
    if (visible && props.reservedUntil) {
        timerInterval = setInterval(() => (now.value = Date.now()), 250)
    } else clearInterval(timerInterval)
})
onBeforeUnmount(() => clearInterval(timerInterval))

// Comprador y estado
const buyer = ref({ name: '', email: '' })
const loading = ref(false)
const error = ref(null)
const totalPrice = computed(() =>
    props.seats.reduce(
        (sum, s) => sum + (parseFloat(s.price) || 0),
        0
    )
)

function submitPurchase() {
    error.value = null
    if (!buyer.value.name || !buyer.value.email) {
        error.value = 'Debes completar nombre y correo.'
        return
    }
    loading.value = true
    emit('confirm', {
        seats: props.seats.map(s => s.id),
        buyer: { ...buyer.value },
    })
    loading.value = false
}
</script>

<style scoped>
.purchase-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 360px;
    background: #fff;
    box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    z-index: 10000;
    overflow: hidden;
    transition: transform 0.3s ease;
}

/* Bottom-sheet en móviles */
@media (max-width: 640px) {
    .purchase-drawer {
        top: auto;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        max-height: 80vh;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
    }
}

.drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9f9fb;
}

.drawer-title {
    margin: 0;
    font-size: 1.125rem;
}

.close-btn {
    background: transparent;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
}

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
}

.seat-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.seat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding-bottom: 0.25rem;
    border-bottom: 1px solid #eee;
}

.seat-price {
    margin-left: auto;
    margin-right: 0.5rem;
}

.remove-btn {
    background: transparent;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    color: #f87171;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-top: 1px solid #e5e7eb;
    font-size: 1.0625rem;
}

.drawer-form {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
}

.submit-btn {
    margin-top: 1rem;
    padding: 0.75rem;
    background: #7c3aed;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}
</style>
