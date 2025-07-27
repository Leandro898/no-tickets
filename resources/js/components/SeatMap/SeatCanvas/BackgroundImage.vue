<template>
    <v-image v-if="bgImage" :config="{ image: bgImage, width, height }" />
</template>
<script setup>
defineProps({ bgImage: Object, width: Number, height: Number })
</script>
