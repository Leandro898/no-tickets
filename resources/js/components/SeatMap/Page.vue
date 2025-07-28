<template>
    <div class="flex h-full">
        <!-- 2) Sidebar de herramientas -->
        <SidebarToolbar :tools="tools" :active="currentTool" @select="onToolSelect" class="shrink-0" />

        <!-- 3) Área principal de canvas y controles -->
        <div class="flex-1 p-4 bg-gray-50 overflow-auto">
            <h2 class="text-2xl font-bold mb-4">Configurar Mapa de Asientos</h2>
            <p class="mb-6 text-sm text-gray-600">Entradas a × : {{ totalTickets }}</p>

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

            <!-- Toolbar principal (undo/redo/zoom/…) -->
            <Toolbar class="mb-4" :seats="seats" :history="history" :future="future"
                @toggle-select-all="toggleSelectAll" @undo="undo" @redo="redo" @zoom-in="zoomIn" @zoom-out="zoomOut"
                @reset-view="resetView" @delete-selected="deleteSelected" @show-help="showHelp" />

            <!-- Canvas + SeatControls -->
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

            <!-- Botones de acción abajo -->
            <div class="mt-4 flex gap-2">
                <button @click="openAddRowModal"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Agregar fila de butacas
                </button>
                <button @click="openGenerateModal"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Generar s
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
        <GenerateSeatsModal v-if="showGenerateModal" :tickets="tickets" v-model:count="generateCount"
            :selectedTicket="selectedTicket" @selectTicket="selectTicket" @generate="generateSeats"
            @cancel="showGenerateModal = false" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useSeatMap } from '@/composables/useSeatMap'
import { useTickets } from '@/composables/useTickets'
import { useGenerateSeats } from '@/composables/useGenerateSeats'

import SidebarToolbar from './SidebarToolbar.vue'
import Toolbar from './Toolbar.vue'
import SeatCanvas from './SeatCanvas/index.vue'
import SeatControls from './SeatControls.vue'
import AddRowModal from './AddRowModal.vue'
import ImageUploader from '../ui/ImageUploader.vue'
import Toast from '../ui/Toast.vue'
import GenerateSeatsModal from './GenerateSeatsModal.vue'

const props = defineProps({
    eventoId: { type: [Number, String], required: true },
    initialBgImageUrl: { type: String, default: '' },
})

// 1) Lógica del canvas, background, undo/redo…  
// Ahora extraemos también tools y onRename
const {
    canvasRef, seats, canvasW, canvasH, bgImage,
    mapJSON, currentTool, spacePressed, isLoading, toast,
    onToolSelect, onBgLoaded, onFileSelected, removeBg,
    addSeat, guardarTodo, onSeatsUpdate,
    history, future, toggleSelectAll, undo, redo,
    zoomIn, zoomOut, resetView, deleteSelected, showHelp,
    showAddRow, openAddRowModal, sectors, onRowAdd,
    tools,       // <–– aquí
    onRename     // <–– y aquí
} = useSeatMap(props.eventoId, props.initialBgImageUrl)

// 2) Tickets (stock y remaining) - Trae los datos de tickets DISPONIBLES del evento
const { tickets, totalTickets } = useTickets(props.eventoId)

// 3) Generar N asientos
const {
    showGenerateModal, generateCount,
    selectedTicket, openGenerateModal,
    selectTicket, generateSeats
} = useGenerateSeats(seats, tickets, canvasW, canvasH)
</script>
