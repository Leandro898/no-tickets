<!-- resources/js/components/SeatMap/SidebarToolbar.vue -->
<template>
    <nav class="flex flex-col items-center py-8 gap-8 bg-white shadow h-full min-w-[72px] rounded-l-lg">
        <button v-for="tool in tools" :key="tool.name" :title="tool.label"
            class="flex flex-col items-center justify-center w-10 h-10 rounded hover:bg-gray-100 transition"
            :class="active === tool.name ? 'text-purple-600 bg-purple-100' : 'text-gray-700'"
            @click="$emit('select', tool.name)">
            <span v-html="tool.icon" class="w-6 h-6"></span>
        </button>
    </nav>
</template>

<script setup>
defineProps({
    active: String,
    tools: Array,
})
defineEmits(['select'])
</script>

<style scoped>
/* si deseas ajustar estilos específicos, hazlo aquí */
</style>
