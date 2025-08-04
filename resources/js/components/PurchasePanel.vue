<!-- resources/js/components/PurchasePanel.vue -->
<template>
    <div v-if="visible" class="purchase-drawer">
        <!-- Cabecera -->
        <header class="drawer-header">
            <h3 class="drawer-title">🛒 Resumen de tu compra</h3>
            <button class="close-btn" @click="emit('close')">✕</button>
        </header>

        <!-- Lista de asientos -->
        <section class="drawer-body">
            <!-- <pre style="padding:1rem; background:#f9f9f9; font-size:0.8rem;">
            {{ seats }}
            </pre> -->

            <ul class="seat-list">
                <li v-for="s in seats" :key="s.id" class="seat-item">
                    <span>🎫 Asiento {{ s.label || s.id }}</span>
                    <span class="seat-price">${{ s.price || 0 }}</span>
                    <!-- 🔴 Aquí sí emitimos correctamente 'remove' con el ID -->
                    <button class="remove-btn" @click="emit('remove', s.id)" aria-label="Quitar asiento">✕</button>
                </li>
            </ul>

            <!-- Total -->
            <div class="total-row">
                <span>Total:</span>
                <strong>${{ totalPrice }}</strong>
            </div>

            <!-- Mensaje de error -->
            <div v-if="error" class="text-red-600 mb-2">
                {{ error }}
            </div>

            <!-- Formulario comprador -->
            <form class="drawer-form" @submit.prevent="submitPurchase">
                <div class="form-group">
                    <label for="buyer-name">Nombre completo *</label>
                    <input id="buyer-name" v-model="buyer.name" type="text" required />
                </div>
                <div class="form-group">
                    <label for="buyer-email">Email *</label>
                    <input id="buyer-email" v-model="buyer.email" type="email" required />
                </div>
                <button type="submit" class="submit-btn">💳 Proceder al pago</button>
            </form>
        </section>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    seats: { type: Array, required: true },
    visible: { type: Boolean, required: true },
})
const emit = defineEmits(['close', 'remove', 'confirm'])

// Datos del comprador
const buyer = ref({ name: '', email: '' })
// Estado de la petición
const loading = ref(false)
// Ahora sí definimos el error
const error = ref(null)

const totalPrice = computed(() =>
    props.seats.reduce((sum, s) => sum + (s.price || 0), 0)
)

function submitPurchase() {
    error.value = null
    if (!buyer.value.name || !buyer.value.email) {
        error.value = 'Debes completar nombre y correo.'
        return
    }
    loading.value = true
    // Emitimos los datos al padre y lo dejamos a él manejar la reserva/compra
    emit('confirm', {
        seats: props.seats.map(s => s.id),
        buyer: { ...buyer.value }
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
