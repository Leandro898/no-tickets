<template>
    <div>
        <input type="file" accept="image/*" @change="handleChange" />
    </div>
</template>

<script setup>
const props = defineProps({
    eventoId: { type: [Number, String], required: true }
})
const emit = defineEmits(['imageLoaded', 'fileSelected', 'imageUploaded'])

async function handleChange(e) {
    const file = e.target.files[0]
    if (!file) return

    // Previsualización inmediata en el front
    const reader = new FileReader()
    reader.onload = (evt) => {
        const img = new Image()
        img.src = evt.target.result
        img.onload = () => {
            emit('imageLoaded', img)
        }
        emit('fileSelected', file)
    }
    reader.readAsDataURL(file)

    // Subir al servidor con FormData
    const formData = new FormData()
    formData.append('image', file)
    try {
        const response = await fetch(`/api/eventos/${props.eventoId}/upload-bg`, {
            method: 'POST',
            body: formData,
        })
        if (!response.ok) throw new Error(`upload-bg ${response.status}`)
        const data = await response.json()
        // Devuelve la URL pública de la imagen
        emit('imageUploaded', data.url)
    } catch (err) {
        console.error('Error al subir la imagen:', err)
        alert('Error al subir la imagen')
    }
}
</script>
