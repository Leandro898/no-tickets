<template>
    <button @click="$emit('add')">Agregar asiento</button>
    <button @click="$emit('save')">{{ loading ? 'Guardando…' : 'Guardar todo' }}</button>
</template>
<script setup>
defineProps({ loading: Boolean })
defineEmits(['add', 'save'])
</script>
