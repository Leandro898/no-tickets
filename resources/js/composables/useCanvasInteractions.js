import { ref, watch } from 'vue'
export function useCanvasInteractions({ props, emit }) {
    const stageRef = ref(null)
    const layerRef = ref(null)
    const transformerRef = ref(null)
    const selection = ref({ visible: false, x: 0, y: 0, width: 0, height: 0 })
    const defaultRadius = 22
    // lógica de watchers, handlers, expose getStage()…
    return { stageRef, layerRef, transformerRef, selection, defaultRadius }
}
