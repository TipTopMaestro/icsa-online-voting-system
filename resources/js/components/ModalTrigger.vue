<script setup lang="ts">
const open = defineModel<boolean>({ default: false })
</script>

<template>
  <div @click="open = true">
    <slot />
  </div>
</template>