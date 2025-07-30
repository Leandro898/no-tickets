<!-- C:\xampp\htdocs\no-tickets\resources\js\components\SeatMap\Page.vue -->
<template>
    <div class="flex h-full">
        <!-- 1) Sidebar de herramientas -->
        <SidebarToolbar :tools="tools" :active="currentTool" @select="onToolSelect" class="shrink-0 border-r" />

        <!-- 2) Área principal -->
        <div class="flex-1 p-4 bg-gray-50 overflow-auto">
            <h2 class="text-2xl font-bold mb-4">Configurar Mapa de Asientos</h2>
            <p class="mb-6 text-sm text-gray-600">Entradas disponibles: {{ totalTickets }}</p>

            <Toast :visible="toast.visible" :message="toast.message" :type="toast.type"
                @close="toast.visible = false" />

            <!-- Uploader + Quitar fondo -->
            <div class="flex items-center gap-2 mb-4">
                <ImageUploader :eventoId="eventoId" @imageLoaded="onBgLoaded" @fileSelected="onFileSelected" />
                <button v-if="bgImage" @click="removeBg"
                    class="px-4 py-2 bg-gray-100 border rounded hover:bg-red-100 hover:text-red-700 transition">
                    Quitar imagen
                </button>
            </div>

            <!-- Toolbar de acciones -->
            <Toolbar class="mb-4" :seats="seats" :shapes="shapes" :history="history" :future="future"
                @toggle-select-all="toggleSelectAll" @undo="undo" @redo="redo" @zoom-in="zoomIn" @zoom-out="zoomOut"
                @reset-view="resetView" @delete-selected="deleteSelected" @show-help="showHelp" />

            <!-- Canvas + controles -->
            <div class="relative flex border rounded overflow-hidden bg-white">
                <div class="flex-1">
                    <SeatMapView ref="canvasRef" :width="canvasW" :height="canvasH" :bg-image="bgImage" :shapes="shapes"
                        :seats="seats" :pan-mode="spacePressed" @update:seats="onSeatsUpdate"
                        @update:shapes="onShapesUpdate" @update:mapJSON="mapJSON = $event" class="w-full h-full" />
                    <!-- <button @click="probarRef" class="bg-red-600 text-white px-4 py-2 rounded mt-2">
                        ¡Probar REF AHORA!
                    </button> -->
                </div>

                <SeatControls v-show="seats.some(s => s.selected)" :selected="seats.filter(s => s.selected)"
                    @rename="onRename" class="absolute top-0 right-0 h-full w-64 bg-white shadow-lg z-20" />
            </div>

            <!-- Botones inferiores -->
            <div class="mt-4 flex gap-2">
                <button @click="openAddRowModal"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Agregar fila de butacas
                </button>
                <button @click="openGenerateModal"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Generar entradas
                </button>
                <button @click="guardarTodo"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center"
                    :disabled="isLoading">
                    <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z" />
                    </svg>
                    {{ isLoading ? 'Guardando…' : 'Guardar todo' }}
                </button>
            </div>
        </div>

        <!-- Modales -->
        <AddRowModal v-if="showAddRow" :sectors="sectors" @add="onRowAdd" @cancel="showAddRow = false" />
        <GenerateSeatsModal v-if="showGenerateModal" :tickets="tickets" :seats="seats" v-model:count="generateCount"
            :selectedTicket="selectedTicket?.id" :remaining="remaining" @selectTicket="selectTicket"
            @generate="generateSeats" @cancel="showGenerateModal = false" />
    </div>
</template>

<script setup>

import { useSeatMap } from '@/composables/useSeatMap'
import { useTickets } from '@/composables/useTickets'
import { useGenerateSeats } from '@/composables/useGenerateSeats'

import SidebarToolbar from './SidebarToolbar.vue'
import Toolbar from './Toolbar.vue'
import SeatMapView from './SeatMapView.vue'
import SeatControls from './SeatControls.vue'
import AddRowModal from './AddRowModal.vue'
import GenerateSeatsModal from './GenerateSeatsModal.vue'
import ImageUploader from '../ui/ImageUploader.vue'
import Toast from '../ui/Toast.vue'

import { nextTick } from 'vue'

import { watch } from 'vue'

const props = defineProps({
    eventoId: { type: [Number, String], required: true },
    initialBgImageUrl: { type: String, default: '' },
})

// Desestructuramos TODO desde el hook, incluyendo shapes y tools
const {
    tools,
    shapes,
    seats,
    canvasRef,
    canvasW,
    canvasH,
    bgImage,
    mapJSON,
    currentTool,
    spacePressed,
    isLoading,
    toast,
    onToolSelect,
    onBgLoaded,
    onFileSelected,
    removeBg,
    guardarTodo,
    onSeatsUpdate,
    history,
    future,
    toggleSelectAll,
    undo,
    redo,
    zoomIn,
    zoomOut,
    resetView,
    deleteSelected,
    showHelp,
    showAddRow,
    openAddRowModal,
    sectors,
    onRowAdd,
    onRename,
    onShapesUpdate  // ← manejador de shapes
} = useSeatMap(props.eventoId, props.initialBgImageUrl)

const { tickets, totalTickets } = useTickets(props.eventoId)

const {
    showGenerateModal,
    generateCount,
    selectedTicket,
    openGenerateModal,
    selectTicket,
    generateSeats,
    remaining
} = useGenerateSeats(seats, tickets, canvasW, canvasH)




function probarRef() {
    nextTick(() => {
        const seatsLayerRef = canvasRef.value?.seatsLayerRef
        const selectedCircleRefs = seatsLayerRef?.selectedCircleRefs?.value
        console.log('selectedCircleRefs:', selectedCircleRefs)
    })
}

watch(
    () => canvasRef.value?.seatsLayerRef?.selectedCircleRefs?.value,
    (nuevoValor) => {
        console.log('El padre detecta el cambio de selectedCircleRefs:', nuevoValor)
    }
)



</script>
