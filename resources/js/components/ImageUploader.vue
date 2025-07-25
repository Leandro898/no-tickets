<template>
    <div>
        <input type="file" accept="image/*" @change="handleChange" />
    </div>
</template>

<script setup>
import { defineEmits } from 'vue'
// ⬇️ INICIO AGREGADO: recibir eventoId por props
const props = defineProps({
    eventoId: {
        type: [Number, String],
        required: true
    }
})
const emit = defineEmits(['imageLoaded', 'imageUploaded'])

async function handleChange(e) {
    const file = e.target.files[0]
    if (!file) return

    // Previsualización inmediata en el front
    const reader = new FileReader()
    reader.onload = function (evt) {
        const img = new window.Image()
        img.src = evt.target.result
        img.onload = () => {
            emit('imageLoaded', img)
        }
        emit('fileSelected', file)
    }
    reader.readAsDataURL(file)

    // 1️⃣ Subir al servidor con FormData
    const formData = new FormData()
    formData.append('image', file)
    try {
        // ⬇️ INICIO CORRECCIÓN: usar eventoId en la URL
        const response = await fetch(`/api/eventos/${props.eventoId}/upload-bg`, {
            method: 'POST',
            body: formData,
        })
        const data = await response.json()
        // Te devuelve la URL pública de la imagen
        emit('imageUploaded', data.url)
    } catch (err) {
        alert('Error al subir la imagen')
    }
}
</script>

