<!-- resources\js\components\SeatMap\Page.vue -->
<template>
  <div class="flex h-full">
    <!-- 1) Sidebar de herramientas -->
    <SidebarToolbar :tools="tools" :active="currentTool" @select="onToolSelect" class="shrink-0 border-r" />

    <!-- 2) Área principal -->
    <div class="flex-1 flex flex-col p-4 bg-gray-50 overflow-hidden">
      <!-- 2.1) Header -->
      <h2 class="text-2xl font-bold mb-2">Configurar Mapa de Asientos</h2>
      <p class="mb-4 text-sm text-gray-600">
        Entradas disponibles: {{ totalTickets }}
      </p>

      <!-- 2.2) Toast -->
      <Toast :visible="toast.visible" :message="toast.message" :type="toast.type" @close="toast.visible = false" />

      <!-- 2.3) Uploader + Quitar fondo -->
      <div class="flex items-center gap-2 mb-4">
        <ImageUploader :eventoSlug="eventoSlug" @image-loaded="onBgLoaded" @file-selected="onFileSelected"
          @imageUploaded="onBgUploaded" />
        <button v-if="bgImage" @click="eliminarBg"
          class="px-4 py-2 bg-gray-100 border rounded hover:bg-red-100 hover:text-red-700 transition">
          Quitar imagen
        </button>
      </div>

      <!-- 2.4) Toolbar de acciones -->
      <Toolbar class="mb-4" :seats="seats" :shapes="shapes" :history="history" :future="future"
        @toggle-select-all="toggleSelectAll" @undo="undo" @redo="redo" @zoom-in="zoomIn" @zoom-out="zoomOut"
        @reset-view="resetView" @delete-selected="deleteSelected" @show-help="showHelp" />

      <!-- 2.5) Contenedor del mapa (flex-1 para que crezca) -->
      <div class="relative min-h-[500px] h-[160vh] border rounded overflow-hidden bg-white">


        <!-- Wrapper mide ancho y alto completos -->
        <div ref="wrapper" class="w-full h-full relative">

          <SeatMapView ref="canvasRef" :width="canvasW" :height="canvasH" :bg-image="bgImage" :shapes="shapes"
            :seats="seats" :pan-mode="spacePressed" @update:seats="handleSeatsFromView" @update:shapes="onShapesUpdate"
            class="absolute inset-0" />


        </div>
        <!-- Controles flotantes -->
        <SeatControls v-show="seats.some(s => s.selected)" :selected="seats.filter(s => s.selected)" @rename="onRename"
          class="absolute top-0 right-0 h-full w-64 bg-white shadow-lg z-20" />
      </div>

      <!-- 2.6) Botones inferiores (siempre visibles) -->
      <div class="mt-4 flex-shrink-0 flex gap-2">
        <button @click="openGenerateModal"
          class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
          Generar entradas
        </button>
        <button @click="guardarTodo"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center"
          :disabled="isLoading || bgUploading">
          <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z" />
          </svg>
          {{ isLoading ? 'Guardando…' : 'Guardar todo' }}
        </button>
      </div>

      <!-- 2.7) Modales -->
      <AddRowModal v-if="showAddRow" :sectors="sectors" @add="onRowAdd" @cancel="showAddRow = false" />
      <GenerateSeatsModal v-if="showGenerateModal" :tickets="tickets" :seats="seats" v-model:count="generateCount"
        :selectedTicket="selectedTicket?.id" :remaining="remaining" @selectTicket="selectTicket"
        @generate="generateSeats" @cancel="showGenerateModal = false" />
    </div>
  </div>
</template>




<script setup>
import { nextTick, watch, ref, onMounted, onBeforeUnmount } from 'vue'

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

const props = defineProps({
  eventoSlug: { type: [Number, String], required: true },
  initialBgImageUrl: { type: String, default: '' },
})

// 1) Creamos el ref para el wrapper:
const wrapper = ref(null)

// 2) Extraemos estado y funciones del composable
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
  eliminarBg,
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
  onShapesUpdate, // Maneja shapes
  onBgUploadRequest,   // lo exponemos desde el composable
  bgUploading,
} = useSeatMap(props.eventoSlug, props.initialBgImageUrl, wrapper)

const { tickets, totalTickets } = useTickets(props.eventoSlug)

const {
  showGenerateModal,
  generateCount,
  selectedTicket,
  openGenerateModal,
  selectTicket,
  generateSeats,
  remaining,
} = useGenerateSeats(seats, tickets, canvasW, canvasH)

// 3) Definimos la función resize **fuera** de onMounted,
//    para poder referenciarla también en onBeforeUnmount.
const resize = () => {
  if (!wrapper.value) return
  canvasW.value = wrapper.value.clientWidth
  canvasH.value = wrapper.value.clientHeight
}

onMounted(() => {
  // Medimos al cargar…
  resize()
  // …y cada vez que cambie tamaño de ventana
  window.addEventListener('resize', resize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', resize)
})








// Nuevo handler:
function onBgUploaded(url) {
  // le pasamos la URL al composable
  onBgUploadRequest(url)
}

// 🎯 Handler intermedio para depurar
function handleSeatsFromView(newSeats) {
  //console.log('El padre recibe:', newSeats)
  onSeatsUpdate(newSeats)
}

// Esto te permite probar el ref desde el padre
function probarRef() {
  nextTick(() => {
    const seatsLayerRef = canvasRef.value?.seatsLayerRef
    console.log('selectedCircleRefs:', seatsLayerRef?.selectedCircleRefs?.value)
  })
}

watch(
  () => canvasRef.value?.seatsLayerRef?.selectedCircleRefs?.value,
  nuevo => console.log('El padre detecta selectedCircleRefs:', nuevo)
)
</script>

<style scoped>

/* 🔴 Dale ancho 100% y alto dinámico con, p.ej., 80vh */
.seat-map-container {
  width: 100%;
  height: 80vh;
  position: relative;
}

</style>
