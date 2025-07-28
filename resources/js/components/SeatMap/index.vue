<template>
    <div class="flex h-full">
        <!-- Sidebar de herramientas -->
        <SidebarToolbar :tools="tools" :active="currentTool" @select="onToolSelect" class="shrink-0" />

        <!-- Área principal -->
        <div class="flex-1 p-4 bg-gray-50 overflow-auto">
            <!-- Toast -->
            <Toast :visible="toast.visible" :message="toast.message" :type="toast.type"
                @close="toast.visible = false" />

            <!-- Uploader + Quitar fondo -->
            <div class="flex items-center gap-2 mb-4">
                <ImageUploader :eventoId="eventoId" @imageLoaded="onBgLoaded" @fileSelected="onFileSelected" />
                <button v-if="bgImage" @click="removeBg"
                    class="px-4 py-2 bg-gray-100 border rounded hover:bg-red-100 hover:text-red-700">
                    Quitar imagen
                </button>
            </div>

            <!-- Toolbar principal -->
            <Toolbar class="mb-4" :seats="seats" :history="history" :future="future"
                @toggle-select-all="toggleSelectAll" @undo="undo" @redo="redo" @zoom-in="zoomIn" @zoom-out="zoomOut"
                @reset-view="resetView" @delete-selected="deleteSelected" @show-help="showHelp" />

            <!-- Canvas + Controls -->
            <div class="relative flex border rounded overflow-hidden bg-white">
                <div class="flex-1">
                    <SeatCanvas ref="canvasRef" :width="canvasW" :height="canvasH" :bg-image="bgImage" :seats="seats"
                        @update:seats="onSeatsUpdate" :pan-mode="spacePressed" @update:mapJSON="mapJSON = $event"
                        class="w-full h-full" />
                </div>

                <SeatControls v-show="seats.some(s => (!s.type || s.type === 'seat') && s.selected)"
                    :selected="seats.filter(s => (!s.type || s.type === 'seat') && s.selected)" @rename="onRename"
                    class="absolute top-0 right-0 h-full w-64 bg-white shadow-lg z-20" />

            </div>

            <!-- Botones de acción -->
            <div class="mt-4 flex gap-2">
                <button class="px-4 py-2 bg-purple-600 text-white rounded" @click="addSeat" :disabled="isLoading">
                    Agregar asiento
                </button>

                <button class="px-4 py-2 bg-green-600 text-white rounded flex items-center" @click="guardarTodo"
                    :disabled="isLoading">
                    <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z" />
                    </svg>
                    {{ isLoading ? 'Guardando…' : 'Guardar todo' }}
                </button>

                <button @click="openModal" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    Agregar fila de butacas
                </button>

                <AddRowModal v-if="showAddRow" :sectors="sectors" @add="onRowAdd" @cancel="showAddRow = false" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import SidebarToolbar from './SidebarToolbar.vue'
import Toolbar from './Toolbar.vue'
import SeatCanvas from './SeatCanvas/index.vue'
import SeatControls from './SeatControls.vue'
import AddRowModal from './AddRowModal.vue'
import ImageUploader from '../ui/ImageUploader.vue'
import Toast from '../ui/Toast.vue'

import { useSeatMap } from './useSeatMap.js'

// Props que recibe el componente padre
const props = defineProps({
    eventoId: { type: [Number, String], required: true },
    initialBgImageUrl: { type: String, default: '' },
})

// 1) Filtrar sólo los asientos “reales”
const seatItems = computed(() =>
    seats.value.filter(s => s && (!s.type || s.type === 'seat'))
);

// 2) Filtrar sólo los shapes (rect, circle, text)
const shapes = computed(() =>
    seats.value.filter(s => s && ['rect', 'circle', 'text'].includes(s.type))
);

// 3) Transformer para los asientos (usa los refs que expone SeatsLayer.vue)
const transformerNodes = computed(() =>
    seatsLayerRef.value?.selectedCircleRefs
        .filter(node => node && node.attrs && node.attrs.selected)
);

// 4) Transformer para los shapes (usa los refs en shapeRefs)
const shapeTransformerNodes = computed(() =>
    shapeRefs.value
        .map((ref, i) => (shapes.value[i] && shapes.value[i].selected) ? ref.getNode() : null)
        .filter(Boolean)
);

// Extraemos TODO de nuestro composable, incluyendo toolbar
const {
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

    // ← Estos son los nuevos que necesitás para Toolbar:
    history,
    future,
    toggleSelectAll,
    undo,
    redo,
    zoomIn,
    zoomOut,
    resetView,
    deleteSelected,
    showHelp
} = useSeatMap(props)
</script>

<style scoped>
/* Ajustes de layout o estilos adicionales */
</style>
