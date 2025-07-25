<template>
    <div>
        <input type="file" accept="image/*" @change="handleChange" />
    </div>
</template>

<script setup>
import { defineEmits } from 'vue'
const emit = defineEmits(['imageLoaded'])

function handleChange(e) {
    const file = e.target.files[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = function (evt) {
        const img = new window.Image()
        img.src = evt.target.result
        img.onload = () => {
            emit('imageLoaded', img)
        }
    }
    reader.readAsDataURL(file)
}
</script>
