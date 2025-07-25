<template>
    <div>
        <input type="file" accept="image/*" @change="handleChange" />
    </div>
</template>

<script setup>
import { defineEmits } from 'vue'
const emit = defineEmits(['imageLoaded', 'imageUploaded'])

async function handleChange(e) {
    const file = e.target.files[0]
    if (!file) return

    // Previsualización inmediata en el front (ya la tienes)
    const reader = new FileReader()
    reader.onload = function (evt) {
        const img = new window.Image()
        img.src = evt.target.result
        img.onload = () => {
            emit('imageLoaded', img)
        }
    }
    reader.readAsDataURL(file)

    // 1️⃣ Subir al servidor con FormData
    const formData = new FormData()
    formData.append('image', file)
    try {
        const response = await fetch('/api/seat-map/upload-bg', {
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

