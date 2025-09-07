<!-- resources/js/components/ui/Toast.vue -->
<template>
    <transition name="fade">
        <div v-show="visible" :class="[
            'fixed top-4 right-4 z-50 px-4 py-2 rounded shadow-lg',
            type === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'
        ]">
            {{ message }}
            <button @click="$emit('close')" class="ml-2 font-bold">×</button>
        </div>
    </transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    visible: { type: Boolean, required: true },
    message: { type: String, default: '' },
    type: { type: String, default: 'success' },
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
